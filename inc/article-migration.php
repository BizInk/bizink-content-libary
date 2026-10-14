<?php

defined('ABSPATH') || exit;

/**
 * One-off migration: converts legacy "post" posts into bcl_article.
 *
 * For every post it:
 *  - pulls the "Download Word Doc" link out of the content into the ACF
 *    download_links repeater,
 *  - strips the legacy preview chrome (Back To Previous link, [page_title],
 *    [lmt-post-modified-info], the preview intro text),
 *  - cleans up leftover classic-editor HTML and converts it to blocks,
 *  - switches the post type to bcl_article,
 *  - assigns a matching Topic for each of the post's Tags (creating the Topic if needed).
 *
 * The original content and post type are kept in post meta so a post can be
 * inspected or restored afterwards. Triggered from Settings > Article Migration.
 */

define('BCL_MIGRATION_BATCH_SIZE', 20);
define('BCL_MIGRATION_DOWNLOAD_LINKS_FIELD', 'field_6ab28a36d69ba');
define('BCL_MIGRATION_SOURCE_STATUSES', array('publish', 'draft', 'pending', 'private', 'future'));

// Phrases removed wherever they appear (matched case-insensitively).
define('BCL_MIGRATION_REMOVE_TEXT', array(
    'Preview the content here and download a copy in Microsoft Word format to edit and brand as you wish.',
    'Article Preview',
));

// Shortcodes removed along with any attributes, e.g. [lmt-post-modified-info format="..."].
define('BCL_MIGRATION_REMOVE_SHORTCODES', array('page_title', 'lmt-post-modified-info'));

// Attributes kept during cleanup; everything else (style, class, data-*, Word cruft) is dropped.
define('BCL_MIGRATION_KEEP_ATTRIBUTES', array('href', 'src', 'alt', 'title', 'target', 'rel', 'id', 'colspan', 'rowspan', 'width', 'height'));

// ---------------------------------------------------------------------------
// Admin page
// ---------------------------------------------------------------------------

add_action('admin_menu', function () {
    add_options_page('Article Migration', 'Article Migration', 'manage_options', 'bcl-article-migration', 'bcl_migration_page');
});

function bcl_migration_remaining_count()
{
    $counts = wp_count_posts('post');
    $total  = 0;
    foreach (BCL_MIGRATION_SOURCE_STATUSES as $status) {
        $total += (int) ($counts->$status ?? 0);
    }
    return $total;
}

function bcl_migration_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }
    $remaining = bcl_migration_remaining_count();
    $nonce     = wp_create_nonce('bcl_article_migration');
?>
    <div class="wrap">
        <h1>Article Migration</h1>
        <p>
            Converts every Post into an <strong>Article</strong> (<code>bcl_article</code>): moves the "Download Word Doc" link into
            the Download Links field, removes the "Back To Previous" link, the <code>[page_title]</code> and
            <code>[lmt-post-modified-info]</code> shortcodes and the preview intro text, cleans up leftover HTML and converts the
            content to blocks.
        </p>
        <p>Each Tag on a post is assigned to the Article as a Topic with the same name (created if it doesn't exist yet). The Topic records its source Tag in the <code>_bcl_migrated_from_tag</code> term meta.</p>
        <p>The original content is stored in the <code>_bcl_migration_original_content</code> post meta and as a revision.</p>
        <p><strong>Posts remaining:</strong> <span id="bcl-migration-remaining"><?php echo esc_html($remaining); ?></span></p>

        <h2>Preview</h2>
        <p>Run the conversion on a single post without saving anything. Leave the ID blank to use the next post in the queue.</p>
        <p>
            <input type="number" id="bcl-migration-preview-id" class="small-text" min="1" placeholder="ID">
            <button type="button" class="button" id="bcl-migration-preview">Preview conversion</button>
        </p>
        <div id="bcl-migration-preview-output" style="display:none;">
            <p><strong>Post:</strong> <span data-field="title"></span> &mdash; <strong>Download Word Doc:</strong> <code data-field="download_url"></code> &mdash; <strong>Topics:</strong> <span data-field="topics"></span></p>
            <textarea readonly rows="20" class="large-text code" data-field="content"></textarea>
        </div>

        <h2>Run migration</h2>
        <p>
            <button type="button" class="button button-primary" id="bcl-migration-run" <?php disabled($remaining, 0); ?>>Migrate all posts</button>
        </p>
        <pre id="bcl-migration-log" style="max-height:400px;overflow:auto;background:#fff;border:1px solid #c3c4c7;padding:10px;display:none;"></pre>
    </div>
    <script>
        (function() {
            const ajaxUrl = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
            const nonce = <?php echo wp_json_encode($nonce); ?>;
            const log = document.getElementById('bcl-migration-log');
            const remaining = document.getElementById('bcl-migration-remaining');
            const runButton = document.getElementById('bcl-migration-run');

            function request(action, data) {
                const body = new URLSearchParams(Object.assign({ action: action, _ajax_nonce: nonce }, data || {}));
                return fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body })
                    .then(function(response) { return response.json(); })
                    .then(function(json) {
                        if (!json.success) {
                            throw new Error(json.data && json.data.message ? json.data.message : 'Request failed');
                        }
                        return json.data;
                    });
            }

            function write(line) {
                log.style.display = 'block';
                log.textContent += line + '\n';
                log.scrollTop = log.scrollHeight;
            }

            document.getElementById('bcl-migration-preview').addEventListener('click', function() {
                const output = document.getElementById('bcl-migration-preview-output');
                request('bcl_article_migration_preview', { post_id: document.getElementById('bcl-migration-preview-id').value })
                    .then(function(data) {
                        output.style.display = 'block';
                        output.querySelector('[data-field="title"]').textContent = '#' + data.post_id + ' ' + data.title;
                        output.querySelector('[data-field="download_url"]').textContent = data.download_url || '(none found)';
                        output.querySelector('[data-field="topics"]').textContent = data.topics.length ? data.topics.join(', ') : '(no tags)';
                        output.querySelector('[data-field="content"]').value = data.content;
                    })
                    .catch(function(error) { alert(error.message); });
            });

            runButton.addEventListener('click', function() {
                if (!confirm('Convert all posts to Articles? This changes their post type and content.')) {
                    return;
                }
                runButton.disabled = true;
                write('Starting migration...');

                (function next() {
                    request('bcl_article_migration_run').then(function(data) {
                        data.results.forEach(function(result) {
                            write((result.error ? 'FAILED ' : 'OK     ') + '#' + result.post_id + ' ' + result.title +
                                (result.error ? ' — ' + result.error : (result.topics.length ? ' [Topics: ' + result.topics.join(', ') + ']' : '') + (result.download_url ? '' : ' (no Download Word Doc link found)')));
                        });
                        remaining.textContent = data.remaining;
                        // Stop if a batch made no progress so a post that keeps failing can't loop forever.
                        if (data.remaining > 0 && data.migrated > 0) {
                            next();
                        } else {
                            write(data.remaining > 0 ? 'Stopped: ' + data.remaining + ' posts could not be migrated.' : 'Done.');
                        }
                    }).catch(function(error) {
                        write('Error: ' + error.message);
                        runButton.disabled = false;
                    });
                })();
            });
        })();
    </script>
<?php
}

add_action('wp_ajax_bcl_article_migration_preview', function () {
    check_ajax_referer('bcl_article_migration');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Not allowed'), 403);
    }

    $post_id = absint($_POST['post_id'] ?? 0);
    if ($post_id) {
        $post = get_post($post_id);
    } else {
        $ids  = bcl_migration_next_post_ids(1);
        $post = $ids ? get_post($ids[0]) : null;
    }
    if (!$post) {
        wp_send_json_error(array('message' => 'No post found'), 404);
    }

    $converted = bcl_migration_convert_content($post->post_content);
    wp_send_json_success(array(
        'post_id'      => $post->ID,
        'title'        => get_the_title($post),
        'download_url' => $converted['download_url'],
        'topics'       => wp_get_post_terms($post->ID, 'post_tag', array('fields' => 'names')),
        'content'      => $converted['content'],
    ));
});

add_action('wp_ajax_bcl_article_migration_run', function () {
    check_ajax_referer('bcl_article_migration');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Not allowed'), 403);
    }

    $results  = array();
    $migrated = 0;
    foreach (bcl_migration_next_post_ids(BCL_MIGRATION_BATCH_SIZE) as $post_id) {
        $result    = bcl_migrate_post($post_id);
        $results[] = $result;
        if (empty($result['error'])) {
            $migrated++;
        }
    }

    wp_send_json_success(array(
        'results'   => $results,
        'migrated'  => $migrated,
        'remaining' => bcl_migration_remaining_count(),
    ));
});

function bcl_migration_next_post_ids($limit)
{
    return get_posts(array(
        'post_type'        => 'post',
        'post_status'      => BCL_MIGRATION_SOURCE_STATUSES,
        'posts_per_page'   => $limit,
        'orderby'          => 'ID',
        'order'            => 'ASC',
        'fields'           => 'ids',
        'suppress_filters' => true,
    ));
}

// ---------------------------------------------------------------------------
// Migration
// ---------------------------------------------------------------------------

function bcl_migrate_post($post_id)
{
    $post   = get_post($post_id);
    $result = array('post_id' => $post_id, 'title' => get_the_title($post), 'download_url' => '', 'topics' => array(), 'error' => '');

    // Read the tags before the post type changes, while post_tag is still registered for it.
    $tags = wp_get_post_terms($post_id, 'post_tag');

    $converted = bcl_migration_convert_content($post->post_content);
    $result['download_url'] = $converted['download_url'];

    update_post_meta($post_id, '_bcl_migration_original_content', wp_slash($post->post_content));
    update_post_meta($post_id, '_bcl_migration_original_post_type', $post->post_type);

    $updated = wp_update_post(wp_slash(array(
        'ID'           => $post_id,
        'post_type'    => 'bcl_article',
        'post_content' => $converted['content'],
    )), true);

    if (is_wp_error($updated)) {
        $result['error'] = $updated->get_error_message();
        return $result;
    }

    if ($converted['download_url']) {
        bcl_migration_add_download_link($post_id, 'Download Word Doc', $converted['download_url']);
    }

    if (!is_wp_error($tags) && $tags) {
        $topic_ids = array();
        foreach ($tags as $tag) {
            $topic_id = bcl_migration_tag_to_topic($tag);
            if ($topic_id) {
                $topic_ids[]        = $topic_id;
                $result['topics'][] = $tag->name;
            }
        }
        wp_set_object_terms($post_id, $topic_ids, 'bcl_topic', true);
    }

    return $result;
}

/**
 * Returns the bcl_topic term ID for a post_tag, reusing a Topic already linked to the tag or
 * with the same slug/name, otherwise creating one. The Topic keeps the source tag ID in term meta.
 */
function bcl_migration_tag_to_topic(WP_Term $tag)
{
    $linked = get_terms(array(
        'taxonomy'   => 'bcl_topic',
        'hide_empty' => false,
        'fields'     => 'ids',
        'number'     => 1,
        'meta_key'   => '_bcl_migrated_from_tag',
        'meta_value' => $tag->term_id,
    ));
    if (!is_wp_error($linked) && $linked) {
        return (int) $linked[0];
    }

    $existing = get_term_by('slug', $tag->slug, 'bcl_topic') ?: get_term_by('name', $tag->name, 'bcl_topic');
    if ($existing) {
        $topic_id = $existing->term_id;
    } else {
        $created = wp_insert_term($tag->name, 'bcl_topic', array('slug' => $tag->slug, 'description' => $tag->description));
        if (is_wp_error($created)) {
            return 0;
        }
        $topic_id = $created['term_id'];
    }

    update_term_meta($topic_id, '_bcl_migrated_from_tag', $tag->term_id);
    return (int) $topic_id;
}

function bcl_migration_add_download_link($post_id, $label, $url)
{
    $rows = get_field(BCL_MIGRATION_DOWNLOAD_LINKS_FIELD, $post_id, false);
    $rows = is_array($rows) ? $rows : array();

    // Unformatted repeater rows are keyed by sub field key; normalise to names for update_field().
    $rows = array_map(function ($row) {
        return array(
            'label' => $row['field_6ab28a60d69bd'] ?? $row['label'] ?? '',
            'url'   => $row['field_6ab28a57d69bc'] ?? $row['url'] ?? '',
        );
    }, $rows);

    foreach ($rows as $row) {
        if ($row['url'] === $url) {
            return;
        }
    }

    $rows[] = array('label' => $label, 'url' => $url);
    update_field(BCL_MIGRATION_DOWNLOAD_LINKS_FIELD, $rows, $post_id);
}

/**
 * Cleans legacy classic-editor content and converts it to block markup.
 * Returns ['content' => string, 'download_url' => string].
 */
function bcl_migration_convert_content($content)
{
    $download_url = '';

    // Content that already contains blocks: only convert its classic (freeform) chunks.
    if (has_blocks($content)) {
        $blocks = parse_blocks($content);
        foreach ($blocks as $i => $block) {
            if ($block['blockName'] !== null) {
                continue;
            }
            $converted = bcl_migration_convert_content($block['innerHTML']);
            $download_url = $download_url ?: $converted['download_url'];
            $blocks[$i] = $converted['content'] === '' ? null : array('blockName' => null, 'attrs' => array(), 'innerBlocks' => array(), 'innerHTML' => $converted['content'], 'innerContent' => array($converted['content']));
        }
        return array('content' => serialize_blocks(array_filter($blocks)), 'download_url' => $download_url);
    }

    $pattern = '/\[(' . implode('|', array_map('preg_quote', BCL_MIGRATION_REMOVE_SHORTCODES)) . ')\b[^\]]*\](?:.*?\[\/\1\])?/is';
    $content = preg_replace($pattern, '', $content);
    $content = wpautop($content);

    $doc  = new DOMDocument();
    $prev = libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8"?><div id="bcl-migration-root">' . $content . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($prev);
    $root = $doc->getElementById('bcl-migration-root');
    if (!$root) {
        return array('content' => $content, 'download_url' => '');
    }
    $xpath = new DOMXPath($doc);

    // Links: capture the Word doc URL, then drop both navigation links.
    foreach (iterator_to_array($xpath->query('.//a', $root)) as $link) {
        $text = bcl_migration_normalise_text($link->textContent);
        if (stripos($text, 'download word doc') !== false) {
            $download_url = $download_url ?: esc_url_raw(trim($link->getAttribute('href')));
            $link->parentNode->removeChild($link);
        } elseif (stripos($text, 'back to previous') !== false) {
            $link->parentNode->removeChild($link);
        }
    }

    // Phrases: remove whole elements that contain only the phrase (catches text split across
    // inline tags), then any remaining occurrences inside text nodes.
    foreach (iterator_to_array($xpath->query('.//*', $root)) as $element) {
        if (!$element->parentNode) {
            continue;
        }
        $text = bcl_migration_normalise_text($element->textContent);
        foreach (BCL_MIGRATION_REMOVE_TEXT as $phrase) {
            if ($text !== '' && strcasecmp($text, $phrase) === 0) {
                $element->parentNode->removeChild($element);
                break;
            }
        }
    }
    foreach (iterator_to_array($xpath->query('.//text()', $root)) as $text_node) {
        $text_node->nodeValue = str_ireplace(BCL_MIGRATION_REMOVE_TEXT, '', $text_node->nodeValue);
    }

    bcl_migration_clean_dom($root, $xpath);

    return array('content' => bcl_migration_nodes_to_blocks($root, $doc), 'download_url' => $download_url);
}

function bcl_migration_normalise_text($text)
{
    return trim(preg_replace('/[\s\x{00A0}]+/u', ' ', $text));
}

// True for text that is only whitespace or separator characters left behind by removed links.
function bcl_migration_is_blank_text($text)
{
    return preg_match('/^[\s\x{00A0}|\-–—•·:>»«]*$/u', $text) === 1;
}

function bcl_migration_clean_dom(DOMElement $root, DOMXPath $xpath)
{
    foreach (iterator_to_array($xpath->query('.//comment()', $root)) as $comment) {
        $comment->parentNode->removeChild($comment);
    }

    // Word / editor cruft with no content value.
    foreach (iterator_to_array($xpath->query('.//*[name()="o:p"] | .//style | .//meta | .//link', $root)) as $node) {
        $node->parentNode->removeChild($node);
    }

    foreach (iterator_to_array($xpath->query('.//*', $root)) as $element) {
        foreach (iterator_to_array($element->attributes) as $attribute) {
            if (!in_array(strtolower($attribute->nodeName), BCL_MIGRATION_KEEP_ATTRIBUTES, true)) {
                $element->removeAttribute($attribute->nodeName);
            }
        }
    }

    // Unwrap presentational wrappers so their contents become top-level blocks / plain inline text.
    foreach (iterator_to_array($xpath->query('.//div | .//section | .//article | .//span | .//font | .//center', $root)) as $element) {
        if ($element->hasAttribute('id') && $element->nodeName === 'span') {
            continue;
        }
        while ($element->firstChild) {
            $element->parentNode->insertBefore($element->firstChild, $element);
        }
        $element->parentNode->removeChild($element);
    }

    // Repeatedly remove elements left empty (or holding only separators / <br>s) by the removals above.
    $removable = array('p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'strong', 'b', 'em', 'i', 'u', 'a', 'li', 'ul', 'ol', 'blockquote', 'small', 'sup', 'sub');
    do {
        $removed = false;
        foreach (iterator_to_array($xpath->query('.//*', $root)) as $element) {
            if (!$element->parentNode || !in_array($element->nodeName, $removable, true)) {
                continue;
            }
            $has_media = $xpath->query('.//img | .//iframe | .//video | .//audio | .//embed | .//object | .//table | .//hr', $element)->length > 0;
            if (!$has_media && bcl_migration_is_blank_text($element->textContent)) {
                $element->parentNode->removeChild($element);
                $removed = true;
            }
        }
    } while ($removed);

    // Strip leading/trailing <br>s and blank text inside paragraphs/headings, and blank top-level text.
    foreach (iterator_to_array($xpath->query('.//p | .//h1 | .//h2 | .//h3 | .//h4 | .//h5 | .//h6 | .//li', $root)) as $element) {
        foreach (array('firstChild', 'lastChild') as $edge) {
            while ($element->$edge && ($element->$edge->nodeName === 'br' || ($element->$edge->nodeType === XML_TEXT_NODE && bcl_migration_is_blank_text($element->$edge->nodeValue)))) {
                $element->removeChild($element->$edge);
            }
        }
    }
    foreach (iterator_to_array($root->childNodes) as $node) {
        if (($node->nodeType === XML_TEXT_NODE && bcl_migration_is_blank_text($node->nodeValue)) || $node->nodeName === 'br') {
            $root->removeChild($node);
        }
    }
}

// ---------------------------------------------------------------------------
// HTML -> block markup
// ---------------------------------------------------------------------------

function bcl_migration_inner_html(DOMNode $node, DOMDocument $doc)
{
    $html = '';
    foreach ($node->childNodes as $child) {
        $html .= $doc->saveHTML($child);
    }
    return trim($html);
}

function bcl_migration_block($name, $html, $attrs = array())
{
    $attrs = $attrs ? ' ' . wp_json_encode($attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';
    return "<!-- wp:{$name}{$attrs} -->\n{$html}\n<!-- /wp:{$name} -->";
}

function bcl_migration_nodes_to_blocks(DOMNode $parent, DOMDocument $doc)
{
    $block_tags = array('p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'blockquote', 'table', 'hr', 'figure', 'pre', 'iframe', 'dl', 'form', 'video', 'audio', 'script');
    $blocks     = array();
    $inline     = array();

    // Loose inline nodes (text, <a>, <strong>…) between block elements are grouped into a paragraph.
    $flush_inline = function () use (&$inline, &$blocks, $doc) {
        $html = trim(implode('', array_map(array($doc, 'saveHTML'), $inline)));
        $html = preg_replace('/^(<br\s*\/?>\s*)+|(\s*<br\s*\/?>)+$/i', '', $html);
        if (!bcl_migration_is_blank_text(wp_strip_all_tags($html)) || stripos($html, '<img') !== false) {
            $blocks[] = bcl_migration_block('paragraph', '<p>' . $html . '</p>');
        }
        $inline = array();
    };

    foreach (iterator_to_array($parent->childNodes) as $node) {
        if ($node->nodeType !== XML_ELEMENT_NODE || !in_array($node->nodeName, $block_tags, true)) {
            if ($node->nodeType === XML_ELEMENT_NODE || $node->nodeType === XML_TEXT_NODE) {
                $inline[] = $node;
            }
            continue;
        }
        if ($inline) {
            $flush_inline();
        }
        $blocks[] = bcl_migration_element_to_block($node, $doc);
    }
    if ($inline) {
        $flush_inline();
    }

    return implode("\n\n", array_filter($blocks));
}

function bcl_migration_element_to_block(DOMElement $node, DOMDocument $doc)
{
    $tag = $node->nodeName;

    switch ($tag) {
        case 'p':
            $image = bcl_migration_image_block($node, $doc);
            if ($image) {
                return $image;
            }
            return bcl_migration_block('paragraph', '<p>' . bcl_migration_inner_html($node, $doc) . '</p>');

        case 'h1':
        case 'h2':
        case 'h3':
        case 'h4':
        case 'h5':
        case 'h6':
            $level = (int) substr($tag, 1);
            return bcl_migration_block(
                'heading',
                "<{$tag} class=\"wp-block-heading\">" . bcl_migration_inner_html($node, $doc) . "</{$tag}>",
                $level === 2 ? array() : array('level' => $level)
            );

        case 'ul':
        case 'ol':
            return bcl_migration_list_block($node, $doc);

        case 'blockquote':
            return bcl_migration_block('quote', '<blockquote class="wp-block-quote">' . bcl_migration_nodes_to_blocks($node, $doc) . '</blockquote>');

        case 'hr':
            return bcl_migration_block('separator', '<hr class="wp-block-separator has-alpha-channel-opacity"/>');

        case 'table':
            // Cells holding block-level markup can't be represented by the table block; keep those as HTML.
            $xpath = new DOMXPath($doc);
            if ($xpath->query('.//td//p | .//th//p | .//td//ul | .//td//ol | .//td//table | .//td//div', $node)->length === 0) {
                return bcl_migration_block('table', '<figure class="wp-block-table">' . $doc->saveHTML($node) . '</figure>', array('hasFixedLayout' => false));
            }
            return bcl_migration_block('html', $doc->saveHTML($node));

        default:
            return bcl_migration_block('html', $doc->saveHTML($node));
    }
}

// A paragraph holding only an image (optionally linked) becomes an image block.
function bcl_migration_image_block(DOMElement $paragraph, DOMDocument $doc)
{
    $elements = array();
    foreach ($paragraph->childNodes as $child) {
        if ($child->nodeType === XML_TEXT_NODE && trim($child->nodeValue) === '') {
            continue;
        }
        $elements[] = $child;
    }
    if (count($elements) !== 1 || $elements[0]->nodeType !== XML_ELEMENT_NODE) {
        return '';
    }

    $link  = $elements[0]->nodeName === 'a' ? $elements[0] : null;
    $image = $link ? $link->getElementsByTagName('img')->item(0) : ($elements[0]->nodeName === 'img' ? $elements[0] : null);
    if (!$image || ($link && trim($link->textContent) !== '')) {
        return '';
    }

    $img = '<img src="' . esc_url($image->getAttribute('src')) . '" alt="' . esc_attr($image->getAttribute('alt')) . '"/>';
    if ($link) {
        $img = '<a href="' . esc_url($link->getAttribute('href')) . '">' . $img . '</a>';
    }
    return bcl_migration_block('image', '<figure class="wp-block-image">' . $img . '</figure>', $link ? array('linkDestination' => 'custom') : array());
}

function bcl_migration_list_block(DOMElement $list, DOMDocument $doc)
{
    $items = array();
    foreach ($list->childNodes as $child) {
        if ($child->nodeType !== XML_ELEMENT_NODE || $child->nodeName !== 'li') {
            continue;
        }
        $html = '';
        foreach ($child->childNodes as $grandchild) {
            $html .= ($grandchild->nodeType === XML_ELEMENT_NODE && in_array($grandchild->nodeName, array('ul', 'ol'), true))
                ? bcl_migration_list_block($grandchild, $doc)
                : $doc->saveHTML($grandchild);
        }
        $items[] = bcl_migration_block('list-item', '<li>' . trim($html) . '</li>');
    }

    $tag = $list->nodeName;
    return bcl_migration_block(
        'list',
        "<{$tag} class=\"wp-block-list\">" . implode("\n\n", $items) . "</{$tag}>",
        $tag === 'ol' ? array('ordered' => true) : array()
    );
}
