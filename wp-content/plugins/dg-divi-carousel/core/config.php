<?php
require_once ( __DIR__ . '/settings/settings.php');
DG_Config::set_panels(
    array(
        'dg_carousel'   => __('Carousel Module', "dica-divi-carousel"),
    )
);
DG_Config::set_sections([
    'dgdc_module_activation' => [
        'name'  => __( "Activation", "dica-divi-carousel" ),
        'panel' => 'dg_carousel'
    ],
    'dgdc_module_docs' => [
        'name'  => __( "Get Started", "dica-divi-carousel" ),
        'panel' => 'dg_carousel'
    ],
]);


DG_Config::set_settings( [

	// license key
	'dgdc_license_key_setting'  => [
		'type'           => 'license',
		'section_slug'   => 'dgdc_module_activation',
		'name'           => __( "License Key", "dica-divi-carousel" ),
		'effected_field' => 'dgdc_license_key_status'
	],
	'dgdc_license_key_status'   => [
		'type'         => 'license-status',
		'name'         => __( "License Status", "dica-divi-carousel" ),
		'section_slug' => 'dgdc_module_activation'
	],
	// Documentation
	'dgdc_user_guide'           => [
		'type'         => 'docs',
		'section_slug' => 'dgdc_module_docs',
		'name'         => __( "Usage Guide", "dica-divi-carousel" ),
		'doc_text'     => __( "Here you'll find everything you need to get the most out of our plugin, from setup instructions to advanced features. Start exploring to unlock its full potential!", 'dica-divi-carousel' ),
		'doc_url_text' => "View Full Documentation",
		'doc_url'      => "https://www.divigear.com/docs-category/carousel-module/",
	],
	'dgdc_live_demo'            => [
		'type'         => 'docs',
		'section_slug' => 'dgdc_module_docs',
		'name'         => __( "Live Demo", "dica-divi-carousel" ),
		'doc_text'     => __( "Explore the Live Demo to see the plugin in action! Get a hands-on experience with all the features and see how it can transform your workflow. Dive in and start exploring now!", 'dica-divi-carousel' ),
		'doc_url_text' => "Try the Live Demo",
		'doc_url'      => "https://plugins.divigear.com/divi-carousel/",
	],
	'dgdc_need_assistance'      => [
		'type'         => 'docs',
		'section_slug' => 'dgdc_module_docs',
		'name'         => __( "Need Assistance?", "dica-divi-carousel" ),
		'doc_text'     => __( "We're here to help! Whether you have questions, need troubleshooting, or just want some advice, our support team is ready to assist you. Reach out, and we'll get back to you as soon as possible!", 'dica-divi-carousel' ),
		'doc_url_text' => "Contact Support",
		'doc_url'      => "https://www.divigear.com/contact/",
	],
	'dgdc_feature_request'      => [
		'type'         => 'docs',
		'section_slug' => 'dgdc_module_docs',
		'name'         => __( "Have a Feature in Mind?", "dica-divi-carousel" ),
		'doc_text'     => __( "We love hearing your ideas! If there's a feature you'd like to see in future updates, let us know. Your feedback helps us make the plugin even better!", 'dica-divi-carousel' ),
		'doc_url_text' => "Submit a Feature Request",
		'doc_url'      => "https://www.divigear.com/contact/",
	],
	'dgdc_ask_for_review'       => [
		'type'         => 'docs',
		'section_slug' => 'dgdc_module_docs',
		'name'         => __( "Share Your Experience with Us!", "dica-divi-carousel" ),
		'doc_text'     => __( "We'd love to hear what you think! Your feedback helps us improve and lets others know what to expect. Take a moment to leave a review on Trustpilot and share your experience with our plugin.", 'dica-divi-carousel' ),
		'doc_url_text' => "Leave a Review on Trustpilot",
		'doc_url'      => "https://www.trustpilot.com/evaluate/divigear.com",
	],
	'dgdc_cta_further_purchase' => [
		'type'         => 'docs',
		'section_slug' => 'dgdc_module_docs',
		'name'         => __( "Unlock 20% Off Your Next Purchase!", "dica-divi-carousel" ),
		'doc_text'     => __( "As a valued customer, we’re excited to offer you 20% off on your next product purchase! Use the code <b>DGLOYAL20</b> at checkout and enjoy exclusive savings on our premium tools.", 'dica-divi-carousel' ),
		'doc_url_text'      => "Shop Now and Save 20%",
		'doc_url' => "https://www.divigear.com/plugins/",
	],
] );