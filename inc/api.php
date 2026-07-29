<?php

defined('ABSPATH') || exit;

ini_set('max_execution_time', '300');

function bcl_calculator_code(WP_POST $post, array $fields)
{
	$content = "";
	ob_start();
?>
	<!doctype html>
	<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?php echo $post->post_title; ?></title>
		<script async src="https://portal.bizinkonline.com/resizer.js"></script>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
		<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" />
		<link rel="stylesheet" type="text/css" href="https://smartbizcalcs.com/css/style.css" />
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
		
		<style>
			/** brand.css */
			.bizinkEmbed .TSBCcontainer {
				border: solid 1px var(--brand-tab-border-color);
			}

			:root {
				/* fonts */
				--brand-headline-font: <?php echo $fields['headline-font'] ?? "'Poppins', sans-serif"; ?> !important;
				--brand-headline-font-size: <?php echo $fields['headline-font-size'] ?? '2rem'; ?> !important;
				--brand-headline-font-weight: <?php echo $fields['headline-font-weight'] ?? '500'; ?> !important;
				--brand-body-font: <?php echo $fields['body-font'] ?? "'Poppins', sans-serif"; ?> !important;
				--brand-body-font-size: <?php echo $fields['body-font-size'] ?? '1rem'; ?> !important;
				--brand-body-font-weight: <?php echo $fields['body-font-weight'] ?? '500'; ?> !important;
				--brand-input-font-size: <?php echo $fields['input-font-size'] ?? '1rem'; ?> !important;
				--brand-output-expression-font-size: <?php echo $fields['output-expression-font-size'] ?? '1.5rem'; ?> !important;
				--brand-output-font-size: <?php echo $fields['output-font-size'] ?? '1.2rem'; ?> !important;
				--brand-h1-font-size: <?php echo $fields['h1-font-size'] ?? '20rem'; ?> !important;
				--brand-h2-font-size: <?php echo $fields['h2-font-size'] ?? '1.6rem'; ?> !important;
				--brand-h3-font-size: <?php echo $fields['h3-font-size'] ?? '1.2rem'; ?> !important;
				--brand-h4-font-size: <?php echo $fields['h4-font-size'] ?? '1rem'; ?> !important;
				--brand-h4-font-weight: <?php echo $fields['h4-font-weight'] ?? '600'; ?> !important;
				--brand-intro-font-color: <?php echo $fields['intro-font-color'] ?? '#000'; ?> !important;
				--brand-output-font-weight: <?php echo $fields['output-font-weight'] ?? '500'; ?> !important;
				--brand-btn-font: <?php echo $fields['btn-font'] ?? "'Poppins', sans-serif"; ?> !important;
				--brand-btn-font-size: <?php echo $fields['btn-font-size'] ?? '1.2rem'; ?> !important;
				--brand-btn-font-weight: <?php echo $fields['btn-font-weight'] ?? '600'; ?> !important;
				--brand-button-font-transform: <?php echo $fields['button-font-transform'] ?? 'none'; ?> !important;
				--brand-disclaimer-heading-size: <?php echo $fields['disclaimer-heading-size'] ?? '1.0rem'; ?> !important;
				--brand-disclaimer-heading-weight: <?php echo $fields['disclaimer-heading-weight'] ?? '600'; ?> !important;
				--brand-disclaimer-size: <?php echo $fields['disclaimer-size'] ?? '0.8rem'; ?> !important;
				--brand-largeAnswerText-font-size: <?php echo $fields['largeanswerttext-font-size'] ?? '2.5rem'; ?> !important;
				--brand-largeAnswerText-font-weight: <?php echo $fields['largeanswerttext-font-weight'] ?? '500'; ?> !important;

				/* backgrounds */
				--brand-outer-background: <?php echo $fields['outer-background'] ?? '#fff'; ?> !important;
				--brand-calc-background: <?php echo $fields['calc-background'] ?? '#fefefe'; ?> !important;
				--brand-input-background: <?php echo $fields['input-background'] ?? '#eee'; ?> !important;
				--brand-output-background: <?php echo $fields['output-background'] ?? 'rgba(113, 134, 157, .1)'; ?> !important;
				--brand-output-effect: <?php echo $fields['output-effect-hover'] ?? '0 12px 15px'; ?> <?php echo $fields['output-effect-color'] ?? 'rgba(140, 152, 164, .1)'; ?> !important;
				--brand-tool-shape: <?php echo $fields['tool-shape'] ?? '8px 8px 8px 8px'; ?> !important;
				--brand-tool-effect: <?php echo $fields['tool-effect-color'] ?? 'rgba(0, 0, 0, 0.2)'; ?> <?php echo $fields['tool-effect'] ?? '5px 5px 5px '; ?> !important;
				--brand-subtotal-background: <?php echo $fields['subtotal-background'] ?? '#036'; ?> !important;
				--brand-subtotal-text-color: <?php echo $fields['subtotal-text-color'] ?? '#fff'; ?> !important;
				--brand-results-summary-background: <?php echo $fields['results-summary-background'] ?? 'rgba(0, 0, 0, 0.1)'; ?> !important;
				--brand-results-summary-text-color: <?php echo $fields['results-summary-text-color'] ?? '#036'; ?> !important;
				--brand-results-summary-border-radius: <?php echo $fields['results-summary-border-radius'] ?? '.5rem'; ?> !important;
				--brand-results-summary-border-width: <?php echo $fields['results-summary-border-width'] ?? '1px'; ?> !important;
				--brand-results-summary-border-style: <?php echo $fields['results-summary-border-style'] ?? 'solid'; ?> !important;
				--brand-results-summary-border-color: <?php echo $fields['results-summary-border-color'] ?? 'rgba(0, 0, 0, 0.12)'; ?> !important;

				/* text color */
				--brand-calc-color: <?php echo $fields['calc-color'] ?? '#036'; ?> !important;
				--brand-input-color: <?php echo $fields['input-color'] ?? '#080808'; ?> !important;
				--brand-output-color: <?php echo $fields['output-color'] ?? '#036'; ?> !important;
				--brand-disclaimer-heading-color: <?php echo $fields['disclaimer-heading-color'] ?? '#000'; ?> !important;
				--brand-disclaimer-color: <?php echo $fields['disclaimer-color'] ?? '#000'; ?> !important;
				--brand-call-to-action-color: <?php echo $fields['call-to-action-color'] ?? '#fff'; ?> !important;

				/* buttons*/
				--brand-button-background: <?php echo $fields['button-background'] ?? '#000'; ?> !important;
				--brand-button-grad-background: <?php echo $fields['button-grad-background'] ?? 'linear-gradient(6deg, #575757 0%, #1f1f1f 35%, #000 100%)'; ?> !important;
				--brand-button-background-hover: <?php echo $fields['button-background-hover'] ?? '#1f1f1f'; ?> !important;
				--brand-button-shape: <?php echo $fields['button-shape'] ?? '35px'; ?> !important;
				--brand-button-padding: <?php echo $fields['button-padding'] ?? '.5rem 1rem'; ?> !important;
				--brand-button-width: <?php echo $fields['button-width'] ?? 'auto'; ?> !important;
				--brand-button-text-color: <?php echo $fields['button-text-color'] ?? '#fff'; ?> !important;
				--brand-button-text-hover-color: <?php echo $fields['button-text-hover-color'] ?? '#fff'; ?> !important;
				--brand-button-effect: <?php echo $fields['button-effect-color'] ?? 'rgba(0, 0, 0, 0)'; ?> <?php echo $fields['button-effect'] ?? '0px 5px 15px'; ?> !important;
				--brand-button-hover-effect: <?php echo $fields['button-hover-effect-color'] ?? 'rgba(0, 0, 0, 0)'; ?> <?php echo $fields['button-hover-effect'] ?? '0px 5px 15px'; ?> !important;

				/* CTA buttons */
				--brand-callToActionButton1-background: <?php echo $fields['calltoactionbutton1-background'] ?? '#be1749'; ?> !important;
				--brand-callToActionButton1-background-hover: <?php echo $fields['calltoactionbutton1-background-hover'] ?? '#307FE2'; ?> !important;
				--brand-callToActionButton1-shape: <?php echo $fields['calltoactionbutton1-shape'] ?? '35px'; ?> !important;
				--brand-callToActionButton1-padding: <?php echo $fields['calltoactionbutton1-padding'] ?? '.5rem 1rem'; ?> !important;
				--brand-callToActionButton1-text-color: <?php echo $fields['calltoactionbutton1-text-color'] ?? '#fff'; ?> !important;
				--brand-callToActionButton1-text-hover-color: <?php echo $fields['calltoactionbutton1-text-hover-color'] ?? '#fff'; ?> !important;
				--brand-callToActionButton1-effect: <?php echo $fields['calltoactionbutton1-effect-color'] ?? 'rgba(0, 0, 0, 0.35)'; ?> <?php echo $fields['calltoactionbutton1-effect'] ?? '0px 5px 15px'; ?> !important;
				--brand-callToActionButton1-hover-effect: <?php echo $fields['calltoactionbutton1-hover-effect-color'] ?? 'rgba(0, 0, 0, 0.35)'; ?> <?php echo $fields['calltoactionbutton1-effect-hover'] ?? '0px 5px 15px'; ?> !important;

				--brand-callToActionButton2-background: <?php echo $fields['calltoactionbutton2-background'] ?? '#307FE2'; ?> !important;
				--brand-callToActionButton2-background-hover: <?php echo $fields['calltoactionbutton2-background-hover'] ?? '#001871'; ?> !important;
				--brand-callToActionButton2-shape: <?php echo $fields['calltoactionbutton2-shape'] ?? '35px'; ?> !important;
				--brand-callToActionButton2-padding: <?php echo $fields['calltoactionbutton2-padding'] ?? '.5rem 1rem'; ?> !important;
				--brand-callToActionButton2-text-color: <?php echo $fields['calltoactionbutton2-text-color'] ?? '#fff'; ?> !important;
				--brand-callToActionButton2-text-hover-color: <?php echo $fields['calltoactionbutton2-text-hover-color'] ?? '#fff'; ?> !important;
				--brand-callToActionButton2-effect: <?php echo $fields['calltoactionbutton2-effect-color'] ?? 'rgba(0, 0, 0, 0)'; ?> <?php echo $fields['calltoactionbutton2-effect'] ?? '0px 5px 15px'; ?> !important;
				--brand-callToActionButton2-hover-effect: <?php echo $fields['calltoactionbutton2-hover-effect-color'] ?? 'rgba(0, 0, 0, 0.35)'; ?> <?php echo $fields['calltoactionbutton2-effect-hover'] ?? '0px 5px 15px'; ?> !important;

				/* chart colors */
				--brand-chart-primary: <?php echo $fields['chart-primary'] ?? '#036'; ?> !important;
				/* primary series / savings growth */
				--brand-chart-secondary: <?php echo $fields['chart-secondary'] ?? '#377dff'; ?> !important;
				/* secondary bar (e.g. wants) */
				--brand-chart-tertiary: <?php echo $fields['chart-tertiary'] ?? '#4caf50'; ?> !important;
				/* tertiary bar (e.g. savings) */
				--brand-chart-line: <?php echo $fields['chart-line'] ?? '#001871'; ?> !important;
				/* default line color for projections */
				--brand-chart-danger: <?php echo $fields['chart-danger'] ?? '#377dff'; ?> !important;
				/* warning/loan/debt color */

				/* sliders */
				--brand-slider-track-color: <?php echo $fields['slider-track-color'] ?? '#d0d4e5'; ?> !important;
				--brand-slider-thumb-color: <?php echo $fields['slider-thumb-color'] ?? '#001871'; ?> !important;

				/* tabs*/
				--brand-tab-background: <?php echo $fields['tab-background'] ?? '#eee'; ?> !important;
				--brand-tab-inactive-background: <?php echo $fields['tab-inactive-background'] ?? '#ccc'; ?> !important;
				--brand-tab-active-color: <?php echo $fields['tab-active-color'] ?? '#000'; ?> !important;
				--brand-tab-hover-background: <?php echo $fields['tab-hover-background'] ?? '#bbb'; ?> !important;
				--brand-tab-hover-color: <?php echo $fields['tab-hover-color'] ?? '#000'; ?> !important;
				--brand-tab-border-shape: <?php echo $fields['tab-border-shape'] ?? '8px'; ?> !important;
				--brand-tab-border-color: <?php echo $fields['tab-border-color'] ?? '#b8b7b7'; ?> !important;
				--brand-tab-text-color: <?php echo $fields['tab-text-color'] ?? '#000'; ?> !important;
				--brand-tab-font-size: <?php echo $fields['tab-font-size'] ?? '1.1'; ?>rem !important;
				--brand-tab-font-weight: <?php echo $fields['tab-font-weight'] ?? '600'; ?> !important;
			}
		</style>
        <script>
            // brandContent.js

            const brandContent = {
            // Currency symbol used by calculator displays
            currencyCode: 'USD',
            disclaimerHead: 'Disclaimer',
            disclaimerText:
                'Test Test Figures and results from these calculators are a general guide only and are not financial or professional advice. Consider getting professional advice before making decisions based on these results.',
            // Contact information (applies to all calculators)
            contactAddress: 'Optional email address for enquiries here',
            contactText: 'Add your call to action text here.',
            tab1: 'Calculator',
            tab2: 'About this calculator',
            tab3: 'Help',

            tab4: 'AI Coach',
            defaultTab2Text: '<p>This section explains what this calculator does, what inputs it uses, and how to interpret the results.</p>',
            defaultTab3Text: '<p>This section provides guidance on how to use this calculator and what to consider before acting on the results.</p>',
            defaultTab4Text: '<p>Use the AI coach to ask questions about this calculator, your numbers, and how they relate to your business decisions.</p>',
            // Optional override for AI coach disclaimer (HTML allowed)
            defaultAiCoachDisclaimerText: '',
            // Global toggles for optional sections
            showDisclaimer: true,
            showContactAddress: false,
            showContactText: false,
            mobileIconCalc: 'https://smartbizcalcs.com/bizink/embedau/css/images/icon-calc.svg',
            mobileIconInfo: 'https://smartbizcalcs.com/bizink/embedau/css/images/icon-info.svg',
            mobileIconHelp: 'https://smartbizcalcs.com/bizink/embedau/css/images/icon-help.svg',
            };

            // content.js

            // Content for Break-even plus profit calculator

            const aiCoachDisclaimerText = '<?php 
                if(isset($fields['aicoachdisclaimertext'])){
                    echo str_replace('"','\"',trim(preg_replace('/\s+/', ' ', $fields['aicoachdisclaimertext'] )));
                }
                else{
                    echo "<div class=\"mt-4 pt-3 border-top\"><p class=\"mb-1\"><strong>Disclaimer</strong></p><p class=\"mb-0\">This AI Coach tool helps you create example prompts that you may choose to use with third party artificial intelligence (AI) platforms. It does not provide financial, legal, tax, or other professional advice, and it does not make recommendations about any products or services. Any prompts or examples generated by this tool are for general informational and educational purposes only. They are not tailored to your personal financial situation, objectives, or needs, and they may produce inaccurate, incomplete, biased, or outdated results when used with external AI tools. We do not control, monitor, or verify the content or accuracy of any information generated by third party AI platforms, and we do not endorse or guarantee any results obtained from those platforms. You are solely responsible for how you use any prompts and for all decisions you make based on AI generated content. We are not responsible for the security, privacy, or use of any information you choose to provide to third party AI providers.</p></div>";
                }
            ?>';

const content = [
  {
    groupId: <?php echo $post->ID; ?>,
    CalcName: '<?php echo $post->post_title; ?>',
    showHelpTab: <?php if(isset($fields['showhelptab']) && $fields['showhelptab']): echo "true"; else: echo "false"; endif; ?>,
    showTabs: <?php if(isset($fields['showtabs']) && $fields['showtabs']): echo "true"; else: echo "false"; endif; ?>,
    // Optional AI coach tab
    showCoachTab: <?php if(isset($fields['showcoachtab']) && $fields['showcoachtab']): echo "true"; else: echo "false"; endif; ?>,
    defaultValues: {
      overheads: 510000,
      pricePerUnit: 1000,
      variableCostPerUnit: 350,
      pricePerUnitSlider: 1000,
      variableCostPerUnitSlider: 350,

      availableCash: 100000,
      monthlyExpense: 30000,
      monthlyIncome: 25000,
    },
    Intro: '<?php echo isset($fields['intro']) ? $fields['intro']:'Estimate how many units you need to sell to cover your overheads and achieve a target profit over a set time period.' ?>',
    tab2Text: '',
    tab3Text:'', 
    tab4Text: '',
    resultIntro: '',
    // Optional CTA buttons (below resultIntro)
   //callToActionButton1: { text: 'Apply now', link: '#' },
   // callToActionButton2: { text: 'Learn more', link: '#' },
    singleColumnLayout: <?php if(isset($fields['singlecolumnlayout']) && $fields['singlecolumnlayout']): echo "true"; else: echo "false"; endif; ?>,
    labelsFor: {
      overheads: 'Fixed costs (overheads)',
      pricePerUnit: 'Selling price per unit',
      variableCostPerUnit: 'Variable cost per unit',

      availableCash: 'Available cash',
      monthlyExpense: 'Monthly expense',
      monthlyIncome: 'Monthly income',
    },
    labels: {
      beHeadingTargets: 'Your targets and costs',
      beHeadingSales: 'Sales',
      bePriceExplanation: 'You can type an amount or use the slider to see how changes in your price per unit affect the result.',
      beVariableExplanation: 'Use this to explore how higher or lower unit costs change the number of units you need to sell.',
      calculateBreakEven: 'Calculate break-even',
      beResultHeading: 'Total units to sell',
      beLabelTotalSales: 'Total sales required',

      bdHeadingPosition: 'Your cash position',
      calculateBurnDown: 'Calculate cash burn',
      bdResultHeading: 'Your cash zero date',
    },
    noCashMessage: 'Enter your available cash to estimate when you could run out of money.',
    noBurnMessage: 'Your income is at least equal to your monthly expenses, so this simple cash burn calculation does not show a cash zero date.',
    zeroDateSummary: "The cash zero date is an estimate of when you could run out of money based on today's available cash and your monthly net burn. To move this date, look for ways to increase income, reduce expenses, or increase available cash by collecting receivables, raising capital or securing funding.",
	noBurnLabel: 'No burn',

    resultsText: {
      breakEvenSummaryMessages: {
        missingInputs:
          'Enter your overheads, sales price and costs to see the break-even result.',
        basePrefix:
          'Based on these numbers, you would need to sell about ',
        baseSuffix:
          ' units in total to cover your overheads.',
      },
    },
  },
];
        </script>
		<?php
		echo $fields['head_code'] ?? '';
		?>
	</head>

	<body>
		<script>
			window.iframeResizer = {
				license: "GPLv3"
			}
		</script>
        <?php echo $fields['body_code'] ?? $post->post_content; ?>
        <script src="https://smartbizcalcs.com/js/calculators.js"></script>
	</body>

	</html>
<?php
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}


function bcl_noAcfResponce()
{
    // No ACF
    $response = new WP_REST_Response(array("message" => "Plugin Missing"), 500);
    $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
    return $response;
}

function bcl_register(WP_REST_Request $request)
{
    $data = json_decode($request->get_body(), true);

    if (!is_array($data)) {
        $response = new WP_REST_Response(array("message" => "Invalid or empty request body"), 400);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }

    $first_name  = sanitize_text_field($data['first_name'] ?? '');
    $last_name   = sanitize_text_field($data['last_name'] ?? '');
    $firm_name   = sanitize_text_field($data['firm_name'] ?? '');
    $email       = sanitize_email($data['email'] ?? '');
    $password    = $data['password'] ?? '';

    $missing = array();
    if (empty($first_name))  $missing[] = 'first_name';
    if (empty($last_name))   $missing[] = 'last_name';
    if (empty($email))       $missing[] = 'email';
    if (empty($password))    $missing[] = 'password';

    if (!empty($missing)) {
        $response = new WP_REST_Response(array(
            "message" => "Missing required fields: " . implode(', ', $missing),
            "fields"  => $missing
        ), 400);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }

    if (!is_email($email)) {
        $response = new WP_REST_Response(array(
            "message" => "Invalid email address",
            "fields"  => array('email')
        ), 400);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }

    if (email_exists($email)) {
        $response = new WP_REST_Response(array("message" => "User already exists"), 409);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }

    $base_username = sanitize_user(strtolower($first_name . '.' . $last_name), true);
    $username = $base_username;
    $i = 1;
    while (username_exists($username)) {
        $username = $base_username . $i;
        $i++;
    }

    $user_id = wp_create_user($username, $password, $email);

    if (is_wp_error($user_id)) {
        $response = new WP_REST_Response(array("message" => $user_id->get_error_message()), 500);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }

    wp_update_user(array(
        'ID'         => $user_id,
        'first_name' => $first_name,
        'last_name'  => $last_name,
    ));
    update_user_meta($user_id, 'firm_name', $firm_name);

    $response = new WP_REST_Response(array(
        "message" => "User created successfully",
        "user_id" => $user_id
    ), 201);
    $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
    return $response;
}

function bcl_subscribe(WP_REST_Request $request)
{
    $data = json_decode($request->get_body(), true);

    if (!is_array($data)) {
        $response = new WP_REST_Response(array("message" => "Invalid or empty request body"), 400);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }

    $user_id            = absint($data['user_id'] ?? 0);
    $plan               = absint($data['plan'] ?? 0);
    $card_name          = sanitize_text_field($data['card_name'] ?? '');
    $payment_method_id  = sanitize_text_field($data['payment_method_id'] ?? '');

    $missing = array();
    if (empty($user_id))           $missing[] = 'user_id';
    if (empty($plan))              $missing[] = 'plan';
    if (empty($card_name))         $missing[] = 'card_name';
    if (empty($payment_method_id)) $missing[] = 'payment_method_id';

    if (!empty($missing)) {
        $response = new WP_REST_Response(array(
            "message" => "Missing required fields: " . implode(', ', $missing),
            "fields"  => $missing
        ), 400);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }

    if (!get_userdata($user_id)) {
        $response = new WP_REST_Response(array(
            "message" => "User not found",
            "fields"  => array('user_id')
        ), 404);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }

    if (!function_exists('pmpro_getLevel') || !class_exists('MemberOrder')) {
        $response = new WP_REST_Response(array("message" => "Paid Memberships Pro not active"), 500);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }

    global $pmpro_level;
    $pmpro_level = pmpro_getLevel($plan);

    if (empty($pmpro_level)) {
        $response = new WP_REST_Response(array(
            "message" => "Invalid membership plan",
            "fields"  => array('plan')
        ), 400);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }

    try {
        $order = new MemberOrder();
        $order->membership_id    = $plan;
        $order->membership_name  = $pmpro_level->name;
        $order->membership_level = $pmpro_level;
        $order->user_id          = $user_id;
        $order->status = "pending";
        $order->setGateway('stripe');

        // Stripe requires a PaymentMethod created client-side (via Stripe.js/Elements)
        // rather than raw card details — the gateway looks this up by ID.
        $order->cardholdername   = $card_name;
        $order->payment_method_id = $payment_method_id;

        $order->billing        = new stdClass();
        $order->billing->name  = $card_name;

        $order->subtotal = $pmpro_level->initial_payment;
        $order->tax = 0;
        $order->total = $pmpro_level->initial_payment;

        if (!$order->process()) {
            $response = new WP_REST_Response(array("message" => $order->error ?: "Payment processing failed"), 402);
            $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
            return $response;
        }

        if (!pmpro_complete_checkout($order)) {
            // The charge succeeded but we couldn't assign the membership level.
            // Try to cancel the payment so the member isn't charged for nothing.
            $order->cancel();
            $response = new WP_REST_Response(array("message" => "Payment succeeded but membership could not be assigned; the charge was cancelled"), 500);
            $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
            return $response;
        }
    } catch (Throwable $e) {
        $response = new WP_REST_Response(array("message" => "Payment error: " . $e->getMessage()), 402);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }

    $response = new WP_REST_Response(array(
        "message" => "Subscription processed successfully",
        "user_id" => $user_id
    ), 201);
    $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
    return $response;
}

function bcl_content(WP_REST_Request $request)
{
    $response = new WP_REST_Response(array(), 200);

    // Set headers.
    $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);

    return $response;
}

function bcl_content_item_all(WP_REST_Request $request)
{
    $parameters = $request->get_params();
    //$parameters['featured'] = filter_var($parameters['id'], FILTER_VALIDATE_BOOLEAN);
    //$parameters['search'] = htmlspecialchars($parameters['search']);

    $args = array(
        'posts_per_page' => empty($parameters['per_page']) ? 20 : $parameters['per_page'],
        'post_type' => empty($parameters['content_type']) ? ['bcl_article', 'bcl_ebook', 'bcl_calculator', 'bcl_tool', 'bcl_pdf', 'bcl_excel'] : $parameters['content_type']
    );

    if (isset($parameters['featured']) && $parameters['featured'] == true) {
        $args['meta_key'] = 'featured';
        $args['meta_value'] = true;
        $args['orderby'] = 'meta_value';
        $args['order'] = 'DESC';
    }
    if (!empty($parameters['search'])) {
        $arg['s'] = $parameters['search'];
        $args['orderby'] = 'relevance';
    }

    if (function_exists('get_fields')) {
        $the_query = new WP_Query($args);

        $postData = [];
        if ($the_query->have_posts()) {
            while ($the_query->have_posts()) {
                $the_query->the_post();
                global $post;
                $fields = get_fields($post->ID);
                $d = array(
                    "id" => $post->ID,
                    "title" => $post->post_title,
                    "content" => $post->post_content,
                    "excerpt" => $post->post_excerpt,
                    "published_date" => $post->post_date,
                    "meta" => array(
                        "content_type" => $post->post_type
                    )
                );
                if ($fields['read_time']) {
                    $data['read_time'] = $fields['read_time'];
                }
                array_push($postData, $d);
            }
        }
        wp_reset_postdata();
        $response = new WP_REST_Response($postData, 200);

        $response->set_headers([
            'Cache-Control' => 'must-revalidate, no-cache, no-store, private',
            'x-wp-total' => $the_query->post_count,
            'x-wp-totalpages' => $the_query->max_num_pages
        ]);

        return $response;
    } else {
        bcl_noAcfResponce();
    }
}

function bcl_request_origin_host()
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';
    $host = parse_url($origin, PHP_URL_HOST);
    return $host ? strtolower($host) : '';
}

function bcl_is_embed_domain_allowed($content_id, $username)
{
    $username = sanitize_user($username, true);
    if (empty($username) || !function_exists('get_fields')) {
        return true;
    }

    $user = get_user_by('login', $username);
    if (!$user) {
        return true;
    }

    $fields  = get_fields('user_' . $user->ID);
    $domains = (array) ($fields['domains']['allowed_domains'] ?? []);
    $domains = array_filter($domains, function ($row) use ($content_id) {
        return absint($row['content_id'] ?? 0) === (int) $content_id;
    });

    // No domains registered for this content -> unrestricted.
    if (empty($domains)) {
        return true;
    }

    $host = bcl_request_origin_host();

    // Local/dev testing is always allowed.
    if (empty($host) || in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
        return true;
    }

    foreach ($domains as $row) {
        $allowed = strtolower(trim($row['url'] ?? ''));
        $allowed = preg_replace('#^https?://#', '', $allowed);
        $allowed = rtrim($allowed, '/');
        if ($allowed === '') {
            continue;
        }
        if ($host === $allowed || $host === 'www.' . $allowed || ('www.' . $host) === $allowed) {
            return true;
        }
    }

    return false;
}

function bcl_content_embed(WP_REST_Request $request)
{
    $parameters = $request->get_params();
    $parameters['id'] = filter_var($parameters['id'], FILTER_SANITIZE_NUMBER_INT, FILTER_NULL_ON_FAILURE);
    if (empty($parameters['id'])) {
        $response = new WP_REST_Response(array(), 404);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }

    if (function_exists('get_fields')) {
        $post = get_post($parameters['id']);
        $fields = get_fields($post->ID);

        if (!bcl_is_embed_domain_allowed($post->ID, $parameters['comp'] ?? '')) {
            $response = new WP_REST_Response(array(
                "title" => $post->post_title,
                "content" => "",
            ), 200);
            $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
            return $response;
        }

        $response = new WP_REST_Response(array(
            "title" => $post->post_title,
            "content" => bcl_calculator_code($post,$fields),
        ), 200);

        // Set headers.
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);

        return $response;
    } else {
        bcl_noAcfResponce();
    }
}

function bcl_get_user_content_settings($user_id, $content_id)
{
    if (empty($user_id) || empty($content_id)) {
        return array();
    }

    $prefix = 'content_settings_' . $content_id . '_';
    $prefix_len = strlen($prefix);

    $settings = array();
    foreach (get_user_meta($user_id) as $meta_key => $meta_value) {
        if (strncmp($meta_key, $prefix, $prefix_len) === 0) {
            $field_name = substr($meta_key, $prefix_len);
            $settings[$field_name] = maybe_unserialize($meta_value[0]);
        }
    }

    return $settings;
}

function bcl_content_item(WP_REST_Request $request)
{
    $parameters = $request->get_params();
    $parameters['id'] = filter_var($parameters['id'], FILTER_SANITIZE_NUMBER_INT, FILTER_NULL_ON_FAILURE);
    if (empty($parameters['id'])) {
        $response = new WP_REST_Response(array(), 404);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }
    if (function_exists('get_fields')) {
        $post = get_post($parameters['id']);
        $fields = get_fields($post->ID);

        $user_id = get_current_user_id();
        $user_fields = get_fields('user_' . $user_id);
        $domains = (array) ($user_fields['domains']['allowed_domains'] ?? []);

        // Per-user, per-content overrides (not managed via ACF; stored as user meta
        // keyed 'content_settings_{content_id}_{field_name}'). Anything not set here
        // falls back to the content's own $fields.
        $user_settings = bcl_get_user_content_settings($user_id, $post->ID);
        $calculator_fields = array_merge($fields, $user_settings);

        $data = array(
            "id" => $post->ID,
            "title" => $post->post_title,
            "content" => bcl_calculator_code($post, $calculator_fields),
            "excerpt" => $post->post_excerpt,
            "published_date" => $post->post_date,
            "user_settings" => $user_settings,
            "meta" => array(
                "content_type" => $post->post_type,
                "allowed_domains" => $domains ?? []
            )
        );
        if (isset($fields['enable_whats_inside_items']) && ($fields['enable_whats_inside_items'] == true || $fields['enable_whats_inside_items'] == 'yes')) {
            if ($fields['whats_inside']) {
                $data['meta']['whats_inside'] = $fields['whats_inside'];
            }
        }
        if (isset($fields['read_time'])) {
            $data['read_time'] = $fields['read_time'];
        }
        if (isset($fields['file_download'])) {
            $data['meta']['file_download'] = $fields['file_download'];
        }
        if (isset($fields['allow_branding'])) {
            $data['meta']['allow_branding'] = $fields['allow_branding'];
        } else {
            $data['meta']['allow_branding'] = false;
        }

        $response = new WP_REST_Response($data, 200);

        // Set headers.
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);

        return $response;
    } else {
        bcl_noAcfResponce();
    }
}

function bcl_update_address_fields(array $address, string $field_prefix, $user)
{
    foreach (array('line_1', 'line_2', 'city', 'state', 'postal_code', 'country') as $key) {
        if (isset($address[$key])) {
            update_field($field_prefix . '_' . $key, sanitize_text_field($address[$key]), 'user_' . $user);
        }
    }
}

function bcl_get_address_fields(array $fields, string $group_name)
{
    return array(
        "line_1" => $fields[$group_name]['line_1'] ?? "",
        "line_2" => $fields[$group_name]['line_2'] ?? "",
        "city" => $fields[$group_name]['city'] ?? "",
        "state" => $fields[$group_name]['state'] ?? "",
        "postal_code" => $fields[$group_name]['postal_code'] ?? "",
        "country" => $fields[$group_name]['country'] ?? "",
    );
}

function bcl_firm(WP_REST_Request $request)
{
    $user = get_current_user_id();
    if (!$user) {
        $response = new WP_REST_Response(array("message" => "User Not Found"), 404);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }

    if (!function_exists('get_fields')) {
        return bcl_noAcfResponce();
    }

    if ($request->get_method() === 'PATCH') {
        if (!function_exists('update_field')) {
            return bcl_noAcfResponce();
        }

        $data = json_decode($request->get_body(), true);
        if (!is_array($data)) {
            $response = new WP_REST_Response(array("message" => "Invalid or empty request body"), 400);
            $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
            return $response;
        }

        if (isset($data['firm_name'])) {
            update_field('brand_kit_brand_name', htmlspecialchars($data['firm_name'], ENT_QUOTES), 'user_' . $user);
        }
        if (isset($data['website'])) {
            update_field('brand_kit_website', filter_var($data['website'], FILTER_SANITIZE_URL, FILTER_NULL_ON_FAILURE), 'user_' . $user);
        }
        if (isset($data['primary_color'])) {
            update_field('brand_kit_primary_color', htmlspecialchars($data['primary_color'], ENT_QUOTES), 'user_' . $user);
        }
        if (isset($data['accent_color'])) {
            update_field('brand_kit_accent_color', htmlspecialchars($data['accent_color'], ENT_QUOTES), 'user_' . $user);
        }

        if (is_array($data['address'] ?? null)) {
            bcl_update_address_fields($data['address'], 'firm_address', $user);
        }
        if (is_array($data['billing_address'] ?? null)) {
            bcl_update_address_fields($data['billing_address'], 'billing_address', $user);
        }
    }

    $fields = get_fields('user_' . $user);
    $response = new WP_REST_Response(array(
        "firm_name" => $fields['brand_kit']['brand_name'] ?? "Test Brand",
        "plan" => "Test Plan",
        "next_billing_date" => "N/A",
        "card_last4" => "N/A",
        "logo_url" => $fields['brand_kit']['brand_logo'],
        "website" => $fields['brand_kit']['website'] ?? "",
        "primary_color" => $fields['brand_kit']['primary_color'] ?? "#1F4E79",
        "accent_color" =>  $fields['brand_kit']['accent_color'] ?? "#2E75B6",
        "address" => bcl_get_address_fields($fields, 'firm_address'),
        "billing_address" => bcl_get_address_fields($fields, 'billing_address'),
    ), 200);
    $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
    return $response;
}

function bcl_firm_subscription(WP_REST_Request $request)
{
    $response = new WP_REST_Response(array(), 200);

    // Set headers.
    $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);

    return $response;
}

function bcl_firm_team(WP_REST_Request $request)
{
    $response = new WP_REST_Response(array(), 200);

    // Set headers.
    $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);

    return $response;
}

function bcl_firm_stats(WP_REST_Request $request)
{
    global $wpdb;

    $content_types = array('bcl_article', 'bcl_ebook', 'bcl_calculator', 'bcl_tool', 'bcl_pdf', 'bcl_excel');

    // New content = items of the tracked types published so far this calendar month.
    $new_content_query = new WP_Query(array(
        'post_type'      => $content_types,
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'date_query'     => array(
            array(
                'year'  => (int) current_time('Y'),
                'month' => (int) current_time('n'),
            ),
        ),
    ));
    $new_content = $new_content_query->found_posts;

    // Active embeds = distinct content items with at least one analytics event recorded for this user.
    $active_embeds = 0;
    $user = wp_get_current_user();
    if ($user && $user->exists()) {
        $table = $wpdb->prefix . 'bcl_analytics';
        $active_embeds = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT content_id) FROM $table WHERE username = %s",
            $user->user_login
        ));
    }

    $response = new WP_REST_Response(array(
        "new_content" => (int) $new_content,
        "downloads_ytd" => 0,
        "active_embeds" => $active_embeds,
        "manual_requests" => 0
    ), 200);

    // Set headers.
    $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);

    return $response;
}

function bcl_branded_assets(WP_REST_Request $request)
{
    $response = new WP_REST_Response(array(), 200);

    // Set headers.
    $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);

    return $response;
}

function bcl_branded_assets_generate(WP_REST_Request $request)
{
    $parameters = $request->get_url_params();
    if (empty($parameters['id'])) {
        $response = new WP_REST_Response(array(), 404);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }
    $post = get_post($parameters['id']);

    $response = new WP_REST_Response(array(), 200);

    // Set headers.
    $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);

    return $response;
}

function bcl_firm_brand_logo(WP_REST_Request $request)
{
    $user = get_current_user_id();
    if ($user) {
        if (function_exists('get_fields') && function_exists('update_field')) {
            if ($request->get_method() == 'POST') {
                $files = $request->get_file_params();
                $file_key = isset($files['logo']) ? 'logo' : (isset($files['file']) ? 'file' : array_key_first($files ?? []));
                if (empty($file_key) || empty($files[$file_key]['tmp_name']) || !empty($files[$file_key]['error'])) {
                    $response = new WP_REST_Response(array("message" => "No File Uploaded"), 400);
                    $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
                    return $response;
                }
                $file = $files[$file_key];

                // Check the file is genuinely an image and of an allowed type.
                $allowed_mimes = array(
                    'jpg|jpeg|jpe' => 'image/jpeg',
                    'png'          => 'image/png',
                    'gif'          => 'image/gif',
                    'webp'         => 'image/webp',
                );
                $image_info = @getimagesize($file['tmp_name']);
                $filetype = wp_check_filetype_and_ext($file['tmp_name'], $file['name'], $allowed_mimes);
                if ($image_info === false || empty($filetype['type']) || !in_array($filetype['type'], $allowed_mimes, true)) {
                    $response = new WP_REST_Response(array("message" => "File Must Be An Image (JPG, PNG, GIF or WEBP)"), 415);
                    $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
                    return $response;
                }

                require_once ABSPATH . 'wp-admin/includes/file.php';
                require_once ABSPATH . 'wp-admin/includes/image.php';
                require_once ABSPATH . 'wp-admin/includes/media.php';

                $attachment_id = media_handle_sideload($file, 0, null, array('mimes' => $allowed_mimes, 'test_form' => false));
                if (is_wp_error($attachment_id)) {
                    $response = new WP_REST_Response(array("message" => $attachment_id->get_error_message()), 500);
                    $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
                    return $response;
                }

                update_field('brand_kit_brand_logo', $attachment_id, 'user_' . $user); // Brand Logo
            }
            $fields = get_fields('user_' . $user);
            $response = new WP_REST_Response(array(
                "logo_url" => $fields['brand_kit']['brand_logo'] ?? null
            ), 200);
            $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
            return $response;
        } else {
            bcl_noAcfResponce();
        }
    } else {
        $response = new WP_REST_Response(array("message" => "User Not Found"), 404);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }
}

function bcl_firm_brand(WP_REST_Request $request)
{
    $user = get_current_user_id();
    if ($user) {
        $method = $request->get_method();
        if ($method == 'POST' || $method == 'PUT' || $method == 'PATCH') {
            if (function_exists('get_fields') && function_exists('update_field')) {
                $data = json_decode($request->get_body(), true);
                $data['firm_name'] = htmlspecialchars($data['firm_name'], ENT_QUOTES);
                $data['primary_color'] = htmlspecialchars($data['primary_color'], ENT_QUOTES);
                $data['accent_color'] = htmlspecialchars($data['accent_color'], ENT_QUOTES);
                $data['website'] = filter_var($data['website'], FILTER_SANITIZE_URL, FILTER_NULL_ON_FAILURE);
                update_field('brand_kit_brand_name', $data['firm_name'], 'user_' . $user); // Brand Name
                update_field('brand_kit_primary_color', $data['primary_color'], 'user_' . $user); // primary_color
                update_field('brand_kit_accent_color', $data['accent_color'], 'user_' . $user); // accent_color
                update_field('brand_kit_website', $data['website'], 'user_' . $user); // website
                $fields = get_fields('user_' . $user);
                $response = new WP_REST_Response(array(
                    "firm_name" => $fields['brand_kit']['brand_name'],
                    "logo_url" => $fields['brand_kit']['brand_logo'],
                    "website" => $fields['brand_kit']['website'],
                    "primary_color" => $fields['brand_kit']['primary_color'],
                    "accent_color" =>  $fields['brand_kit']['accent_color']
                ), 200);
                $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
                return $response;
            } else {
                bcl_noAcfResponce();
            }
        } else {
            if (function_exists('get_fields')) {
                $fields = get_fields('user_' . $user);
                $response = new WP_REST_Response(array(
                    "firm_name" => $fields['brand_kit']['brand_name'],
                    "logo_url" => $fields['brand_kit']['brand_logo'],
                    "website" => $fields['brand_kit']['website'],
                    "primary_color" => $fields['brand_kit']['primary_color'],
                    "accent_color" =>  $fields['brand_kit']['accent_color']
                ), 200);
                $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
                return $response;
            } else {
                bcl_noAcfResponce();
            }
        }
    } else {
        $response = new WP_REST_Response(array("message" => "User Not Found"), 404);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }
}

function bcl_analytics_parse_timestamp($raw)
{
    if (!empty($raw)) {
        // Accept unix timestamps (seconds or milliseconds) or any strtotime-parseable string.
        if (is_numeric($raw)) {
            $unix = (int) $raw;
            if ($unix > 9999999999) { // milliseconds
                $unix = (int) ($unix / 1000);
            }
            return gmdate('Y-m-d H:i:s', $unix);
        }
        $unix = strtotime($raw);
        if ($unix !== false) {
            return gmdate('Y-m-d H:i:s', $unix);
        }
    }
    return current_time('mysql', true);
}

function bcl_analytics(WP_REST_Request $request)
{
    $parameters = $request->get_url_params();
    $content_id = absint($parameters['id'] ?? 0);
    if (empty($content_id)) {
        $response = new WP_REST_Response(array(), 404);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'bcl_analytics';

    if ($request->get_method() === 'POST') {
        $data = json_decode($request->get_body(), true);
        if (!is_array($data)) {
            $response = new WP_REST_Response(array("message" => "Invalid or empty request body"), 400);
            $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
            return $response;
        }

        $event = sanitize_text_field($data['event'] ?? '');
        if (empty($event)) {
            $response = new WP_REST_Response(array("message" => "Missing required field: event"), 400);
            $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
            return $response;
        }

        $page_url = esc_url_raw($data['pageUrl'] ?? '');
        $time     = bcl_analytics_parse_timestamp($data['timestamp'] ?? '');
        $duration = is_numeric($data['durationSeconds'] ?? null) ? round((float) $data['durationSeconds'], 2) : null;

        // `comp` is the username of the account the content is embedded with.
        $username = sanitize_user($data['comp'] ?? '', true);

        $wpdb->insert($table, array(
            'content_id'        => $content_id,
            'event'             => substr($event, 0, 50),
            'page_url'          => substr($page_url, 0, 255),
            'duration_seconds'  => $duration,
            'username'          => substr($username, 0, 60),
            'time'              => $time,
        ), array('%d', '%s', '%s', '%f', '%s', '%s'));

        $response = new WP_REST_Response(array("message" => "Event recorded"), 201);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }

    // GET — sum up recorded events by type for this content ID, optionally scoped to one user via `comp`.
    $query_params = $request->get_params();
    $filter_username = sanitize_user($query_params['comp'] ?? '', true);

    $where = "content_id = %d";
    $args  = array($content_id);
    if (!empty($filter_username)) {
        $where .= " AND username = %s";
        $args[] = $filter_username;
    }

    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT event, COUNT(*) as count FROM $table WHERE $where GROUP BY event",
        $args
    ));

    $totals = array();
    $total_events = 0;
    foreach ($rows as $row) {
        $count = (int) $row->count;
        $totals[$row->event] = $count;
        $total_events += $count;
    }

    $engagement = $wpdb->get_row($wpdb->prepare(
        "SELECT COUNT(duration_seconds) as count, SUM(duration_seconds) as total, AVG(duration_seconds) as average
           FROM $table WHERE $where AND event = 'engagement' AND duration_seconds IS NOT NULL",
        $args
    ));

    // Per-user breakdown, grouped by the `comp` username stored with each event.
    $user_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT username, event, COUNT(*) as count FROM $table WHERE $where AND username != '' GROUP BY username, event",
        $args
    ));

    $users = array();
    foreach ($user_rows as $row) {
        $uname = $row->username;
        if (!isset($users[$uname])) {
            $users[$uname] = array(
                "total_events"     => 0,
                "events"           => array(),
                "total_engagement" => 0,
                "avg_engagement"   => 0,
            );
        }
        $count = (int) $row->count;
        $users[$uname]['events'][$row->event] = $count;
        $users[$uname]['total_events'] += $count;
    }

    $user_engagement_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT username, SUM(duration_seconds) as total, AVG(duration_seconds) as average
           FROM $table WHERE $where AND event = 'engagement' AND duration_seconds IS NOT NULL AND username != ''
           GROUP BY username",
        $args
    ));

    foreach ($user_engagement_rows as $row) {
        $uname = $row->username;
        if (!isset($users[$uname])) {
            $users[$uname] = array(
                "total_events"     => 0,
                "events"           => array(),
                "total_engagement" => 0,
                "avg_engagement"   => 0,
            );
        }
        $users[$uname]['total_engagement'] = $row->total !== null ? round((float) $row->total, 2) : 0;
        $users[$uname]['avg_engagement']   = $row->average !== null ? round((float) $row->average, 2) : 0;
    }

    $response = new WP_REST_Response(array(
        "content_id"      => $content_id,
        "total_events"    => $total_events,
        "events"          => $totals,
        "views"            => $totals && $totals['view'] !== null ? $totals['view'] : 0,
        "total_engagement" => $engagement && $engagement->total !== null ? round((float) $engagement->total, 2) : 0,
        "avg_engagement"   => $engagement && $engagement->average !== null ? round((float) $engagement->average, 2) : 0,
    ), 200);

    // Set headers.
    $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);

    return $response;
}

function bcl_content_settings(WP_REST_Request $request)
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        $response = new WP_REST_Response(array("message" => "User Not Found"), 404);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }

    $parameters = $request->get_params();
    $content_id  = absint($parameters['id'] ?? 0);
    if (empty($content_id)) {
        $response = new WP_REST_Response(array("message" => "Invalid content ID"), 400);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }

    if (!function_exists('get_fields') || !function_exists('update_field')) {
        return bcl_noAcfResponce();
    }

    $post = get_post($content_id);
    if (!$post) {
        $response = new WP_REST_Response(array("message" => "Content not found"), 404);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }

    $method = $request->get_method();
    if ($method === 'GET') {
        // Per-user overrides fall back to the content's own ACF fields for any
        // field the user hasn't set.
        $fields        = get_fields($post->ID);
        $user_settings = bcl_get_user_content_settings($user_id, $post->ID);
        $settings      = array_merge((array) $fields, $user_settings);

        $response = new WP_REST_Response(array(
            "content_id" => $content_id,
            "settings"   => $settings,
        ), 200);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }
    else{
        // PATCH — update the current user's per-content setting overrides.
        // Stored as user meta keyed 'content_settings_{content_id}_{field_name}',
        // mirroring bcl_get_user_content_settings().
        $data     = json_decode($request->get_body(), true);
        $settings = (array) ($data['settings'] ?? $data);

        if (empty($settings)) {
            $response = new WP_REST_Response(array("message" => "No settings provided"), 400);
            $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
            return $response;
        }

        foreach ($settings as $field_name => $value) {
            $field_name = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $field_name);
            if ($field_name === '') {
                continue;
            }
            $value = is_string($value) ? sanitize_text_field($value) : $value;
            update_user_meta($user_id, 'content_settings_' . $content_id . '_' . $field_name, $value);
        }

        $fields        = get_fields($post->ID);
        $user_settings = bcl_get_user_content_settings($user_id, $post->ID);
        $merged        = array_merge((array) $fields, $user_settings);

        $response = new WP_REST_Response(array(
            "content_id" => $content_id,
            "settings"   => $merged,
        ), 200);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }
}

function bcl_content_allowed_domains(WP_REST_Request $request)
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        $response = new WP_REST_Response(array("message" => "User Not Found"), 404);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }

    $parameters = $request->get_params();
    $content_id  = absint($parameters['id'] ?? 0);
    if (empty($content_id)) {
        $response = new WP_REST_Response(array("message" => "Invalid content ID"), 400);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }

    if (!function_exists('get_fields') || !function_exists('update_field')) {
        return bcl_noAcfResponce();
    }

    $method = $request->get_method();

    // GET — return all allowed domains for this post ID across all users
    if ($method === 'GET') {
        global $wpdb;

        // ACF stores repeater rows (inside the `domains` group) as:
        //   domains_allowed_domains_{index}_content_id  => post ID
        //   domains_allowed_domains_{index}_url          => domain URL
        // Query every usermeta row where content_id equals the requested post ID.
        $id_rows = $wpdb->get_results($wpdb->prepare(
            "SELECT user_id, meta_key, meta_value
               FROM {$wpdb->usermeta}
              WHERE meta_key LIKE %s
                AND meta_value = %s",
            'domains\_allowed\_domains\_%\_content\_id',
            (string) $content_id
        ));

        $urls = [];
        foreach ($id_rows as $row) {
            // Derive the sibling URL key, e.g. domains_allowed_domains_2_content_id
            // → domains_allowed_domains_2_url
            $url_key = preg_replace('/_content_id$/', '_url', $row->meta_key);
            $url     = get_user_meta($row->user_id, $url_key, true);
            if (!empty($url)) {
                $urls[] = array(
                    'user_id'    => (int) $row->user_id,
                    'content_id' => $content_id,
                    'url'        => $url,
                );
            }
        }

        $response = new WP_REST_Response(array(
            "content_id"      => $content_id,
            "allowed_domains" => $urls,
        ), 200);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }

    // POST — add a new url + content_id pair if not already present
    $data = json_decode($request->get_body(), true);
    $url  = filter_var($data['domain'] ?? '', FILTER_SANITIZE_URL);

    $url = str_replace("https://","",$url);
    $url = str_replace("http://","",$url);


    if (empty($url)) {
        $response = new WP_REST_Response(array("message" => "URL is required"), 400);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }

    $fields        = get_fields('user_' . $user_id);
    $existing_rows = (array) ($fields['domains']['allowed_domains'] ?? []);

    // Check for a duplicate — same content_id AND url
    foreach ($existing_rows as $row) {
        if (absint($row['content_id']) === $content_id && $row['url'] === $url) {
            $response = new WP_REST_Response(array(
                "message"         => "Domain already exists",
                "allowed_domains" => $existing_rows,
            ), 200);
            $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
            return $response;
        }
    }

    // Append and persist using the repeater's field key so ACF resolves it correctly
    $existing_rows[] = array(
        'content_id' => $content_id,
        'url'        => $url,
    );
    // domains_allowed_domains
    update_field('field_6a138bcb79287', $existing_rows, 'user_' . $user_id);

    $response = new WP_REST_Response(array(
        "message"         => "Domain added successfully",
        "allowed_domains" => $existing_rows,
    ), 201);
    $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
    return $response;
}

function bcl_admin_branding_queue(WP_REST_Request $request)
{
    $parameters = $request->get_url_params();
    if (empty($parameters['id'])) {
        $response = new WP_REST_Response(array(), 404);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }
    $response = new WP_REST_Response(array(), 200);

    // Set headers.
    $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);

    return $response;
}

function bcl_getpage(WP_REST_Request $request)
{
    $parameters = $request->get_params();
    if(gettype($parameters['slug']) == 'object'){
        $slug = '';
    }
    else{
        $slug = sanitize_title($parameters['slug'] ?? '');
    }

    if (empty($slug)) {
        $front_page_id = (int) get_option('page_on_front');
        $post = $front_page_id ? get_post($front_page_id) : null;
    } else {
        $post = get_page_by_path($slug, OBJECT, 'page');
    }

    if (!$post) {
        $response = new WP_REST_Response(array("message" => "Page not found"), 404);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }

    $data = array(
        "id"             => $post->ID,
        "title"          => $post->post_title,
        "content"        => $post->post_content,
        "slug"           => $post->post_name,
        "status"         => $post->post_status,
        "published_date" => $post->post_date,
        "modified_date"  => $post->post_modified,
        "built_with_elementor" => false
    );

    $elementor_edit_mode = get_post_meta($post->ID, '_elementor_edit_mode', true);
    $elementor_data      = get_post_meta($post->ID, '_elementor_data', true);

    if ($elementor_edit_mode === 'builder' && !empty($elementor_data)) {
        $data['built_with_elementor'] = true;
        if (class_exists("\\Elementor\\Plugin")) {
            $pluginElementor = \Elementor\Plugin::instance();
            $data['content'] = $pluginElementor->frontend->get_builder_content($post->ID);
        }
        $data['elementor'] = array(
            "version"              => get_post_meta($post->ID, '_elementor_version', true),
            "page_settings"        => get_post_meta($post->ID, '_elementor_page_settings', true),
            "elementor_css"        => get_post_meta($post->ID,'_elementor_css',true)
        );
    }

    $response = new WP_REST_Response($data, 200);
    $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
    return $response;
}

// Routes
add_action('rest_api_init', function () {
    register_rest_route('bcl/v1', '/firm/stats', array(
        'methods' => 'GET',
        'callback' => 'bcl_firm_stats',
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ));
    register_rest_route('bcl/v1', '/firm/subscription', array(
        'methods' => 'GET',
        'callback' => 'bcl_firm_subscription',
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ));
    register_rest_route('bcl/v1', '/firm/team', array(
        'methods' => 'GET',
        'callback' => 'bcl_firm_team',
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ));
    register_rest_route('bcl/v1', '/firm/brand-kit', array(
        'methods' => array('GET', 'POST', 'PATCH', 'PUT'),
        'callback' => 'bcl_firm_brand',
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ));
    register_rest_route('bcl/v1', '/firm/logo', array(
        'methods' => array('GET', 'POST'),
        'callback' => 'bcl_firm_brand_logo',
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ));
    register_rest_route('bcl/v1', '/firm/branded-assets', array(
        'methods' => 'GET',
        'callback' => 'bcl_branded_assets',
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ));
    register_rest_route('bcl/v1', '/firm/branded-assets/generate/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'bcl_branded_assets_generate',
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ));
    register_rest_route('bcl/v1', '/firm', array(
        'methods' => ['GET','PATCH'],
        'callback' => 'bcl_firm',
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ));
    register_rest_route('bcl/v1', '/bcl_content_embed', array(
        'methods' => 'GET',
        'callback' => 'bcl_content_embed',
        'permission_callback' => '__return_true',
    ));
    register_rest_route('bcl/v1', '/bcl_content_item', array(
        'methods' => 'GET',
        'callback' => 'bcl_content_item_all',
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ));
    register_rest_route('bcl/v1', '/bcl_content_item/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'bcl_content_item',
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ));
    register_rest_route('bcl/v1', '/content/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'bcl_content',
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ));
    register_rest_route('bcl/v1', '/content/(?P<id>\d+)/analytics', array(
        'methods' => ['GET', 'POST'],
        'callback' => 'bcl_analytics',
        'permission_callback' => function (WP_REST_Request $request) {
            if($request->get_method() == "POST"){
                return true;
            }
            else{
                return current_user_can('read');
            }
        },
    ));
    register_rest_route('bcl/v1', '/content/(?P<id>\d+)/allowed-domains', array(
        'methods' => array('GET', 'POST', 'DELETE'),
        'callback' => 'bcl_content_allowed_domains',
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ));

    register_rest_route('bcl/v1', '/content/(?P<id>\d+)/settings', array(
        'methods' => array('GET', 'POST', 'PATCH'),
        'callback' => 'bcl_content_settings',
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ));

    register_rest_route('bcl/v1', '/admin/branding-queue', array(
        'methods' => 'GET',
        'callback' => 'bcl_admin_branding_queue',
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ));

    register_rest_route('bcl/v1', '/packages', array(
        'methods' => 'GET',
        'callback' => 'bcl_getpackeges',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('bcl/v1', '/curencies', array(
        'methods' => 'GET',
        'callback' => 'bcl_getcurencies',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('bcl/v1', '/register', array(
        'methods' => 'POST',
        'callback' => 'bcl_register',
        'permission_callback' => '__return_true'
    ));

    register_rest_route('bcl/v1', '/subscribe', array(
        'methods' => 'POST',
        'callback' => 'bcl_subscribe',
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ));

    register_rest_route('bcl/v1', '/page', array(
        'methods' => 'GET',
        'callback' => 'bcl_getpage',
        'permission_callback' => '__return_true',
        'args' => array(
            'slug' => array(
                'required'          => false,
                'sanitize_callback' => 'sanitize_title',
            ),
        ),
    ));

    register_rest_route('bcl/v1', '/menu', array(
        'methods' => 'GET',
        'callback' => 'bcl_getmenu',
        'permission_callback' => '__return_true',
        'args' => array(
            'name' => array(
                'required'          => true,
                'sanitize_callback' => 'sanitize_text_field',
            ),
        ),
    ));
});

function bcl_getcurencies(WP_REST_Request $request)
{
    if (function_exists('lmf_build_currency_items')) {
        $currency = lmf_build_currency_items();
        $response = new WP_REST_Response($currency, 200);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    } else {

        $currency = json_decode('[
    {
        "code": "USD",
        "label": "USD ($)",
        "active": false
    },
    {
        "code": "EUR",
        "label": "EUR (\u20ac)",
        "active": false
    },
    {
        "code": "GBP",
        "label": "GBP (\u00a3)",
        "active": false
    },
    {
        "code": "AUD",
        "label": "AUD ($)",
        "active": false
    },
    {
        "code": "CAD",
        "label": "CAD ($)",
        "active": false
    },
    {
        "code": "NZD",
        "label": "NZD ($)",
        "active": true
    }
]');
        $response = new WP_REST_Response($currency, 200);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }
}

function bcl_getpackeges(WP_REST_Request $request)
{
    if (!function_exists('pmpro_getAllLevels')) {
        $response = new WP_REST_Response(array("message" => "Paid Memberships Pro not active"), 500);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }

    $levels = pmpro_getAllLevels(false, true);
    // get_pmpro_membership_level_meta
    $packages = array_values(array_map(function ($level) {
        $currency = "NZD";
        $symbol = "$";
        if (function_exists('get_pmpro_membership_level_meta')) {
            $data = get_pmpro_membership_level_meta($level->id, 'pmpro_custom_currency', true);
            $parts = explode(',', $data);
            $currency = $parts[0];
            $symbol = $parts[1];
            if ($currency == "DEFAULT") {
                $currency = 'NZD';
                $symbol = "$";
            }
        }

        return array(
            'id'             => (int) $level->id,
            'name'           => $level->name,
            'description'    => $level->description,
            'price' => (float) $level->billing_amount,
            'cycle_number'   => (int) $level->cycle_number,
            'cycle_period'   => $level->cycle_period,
            'initial_payment' => $level->initial_payment,
            'currency' => $currency,
            'symbol' => $symbol
        );
    }, $levels));

    $response = new WP_REST_Response($packages, 200);
    $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
    return $response;
}

function bcl_getmenu(WP_REST_Request $request)
{
    $parameters = $request->get_params();
    $name = sanitize_text_field($parameters['name'] ?? '');

    if (empty($name)) {
        $response = new WP_REST_Response(array("message" => "Menu name is required"), 400);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }

    $menu = wp_get_nav_menu_object($name);

    if (!$menu) {
        $response = new WP_REST_Response(array("message" => "Menu not found"), 404);
        $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
        return $response;
    }

    $items = wp_get_nav_menu_items($menu->term_id);

    if (!$items) {
        $items = [];
    }

    $formatted = array_map(function ($item) {
        return array(
            'id'        => (int) $item->ID,
            'title'     => $item->title,
            'url'       => $item->url,
            'target'    => $item->target,
            'parent'    => (int) $item->menu_item_parent,
            'order'     => (int) $item->menu_order,
            'classes'   => array_filter($item->classes),
            'object'    => $item->object,
            'object_id' => (int) $item->object_id,
        );
    }, $items);

    $response = new WP_REST_Response(array(
        'name'  => $menu->name,
        'slug'  => $menu->slug,
        'items' => array_values($formatted),
    ), 200);
    $response->set_headers(['Cache-Control' => 'must-revalidate, no-cache, no-store, private']);
    return $response;
}

function bcl_before_jwt_token($data, $user)
{
    //$data['user'] = $user;
    $d = $data;
    $d['username'] = $user->data->user_login;
    $d['roles'] = $user->roles;
    return $d;
}
add_filter('jwt_auth_token_before_dispatch', 'bcl_before_jwt_token', 10, 3);
