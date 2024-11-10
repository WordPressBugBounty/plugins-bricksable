<?php
namespace Bricks;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Bricksable_Embed_Google_Maps extends Element {
	public $category     = 'bricksable';
	public $name         = 'ba-embed-google-maps';
	public $icon         = 'ti-map-alt';
	public $css_selector = '&>iframe';
	public $draggable    = false;

	public function get_label() {
		return esc_html__( 'Embed Google Maps', 'bricksable' );
	}

	public function set_controls() {
		$this->controls['infoApiKey'] = array(
			'tab'      => 'content',
			'content'  => sprintf(
				// translators: %s: Link to settings page.
				esc_html__( 'You\'ve added Google Maps API key in your dashboard under: %s', 'bricksable' ),
				'<a href="' . Helpers::settings_url( '#tab-api-keys' ) . '" target="_blank">Bricks > ' . esc_html__( 'Settings', 'bricksable' ) . ' > API keys.</a><br><br>You can choose to use the Embed Google Maps Element with the API key (recommended for stability) or continue using the standard embed version (Free).'
			),
			'type'     => 'info',
			'required' => array( 'apiKeyGoogleMaps', '!=', '', 'globalSettings' ),
		);

		$this->controls['apiType'] = array(
			'tab'         => 'content',
			'label'       => esc_html__( 'Type', 'bricksable' ),
			'type'        => 'select',
			'options'     => array(
				'api'   => esc_html__( 'Use Google API Version', 'bricksable' ),
				'embed' => esc_html__( 'Use Embed Version (Free)', 'bricksable' ),
			),
			'default'     => 'api',
			'placeholder' => esc_html__( 'Use Google API Key', 'bricksable' ),
			'required'    => array( 'apiKeyGoogleMaps', '!=', '', 'globalSettings' ),
		);

		$this->controls['address'] = array(
			'label'       => esc_html__( 'Address', 'bricksable' ),
			'type'        => 'text',
			'trigger'     => array( 'blur', 'enter' ),
			'placeholder' => esc_html__( 'Berlin, Germany', 'bricksable' ),
			'default'     => esc_html__( 'Berlin, Germany', 'bricksable' ),
			'description' => esc_html__( 'Enter the address of the pin', 'bricksable' ),
		);
		$this->controls['height']  = array(
			'tab'         => 'content',
			'label'       => esc_html__( 'Height', 'bricksable' ),
			'type'        => 'number',
			'units'       => true,
			'css'         => array(
				array(
					'property' => 'height',
					'selector' => '>iframe',
				),
				array(
					'property' => 'width',
					'selector' => '&',
					'value'    => '100%',
				),
				array(
					'property' => 'width',
					'selector' => '>iframe',
					'value'    => '100%',
				),
				array(
					'property' => 'border',
					'selector' => '>iframe',
					'value'    => 'none',
				),
				array(
					'property' => 'line-height',
					'selector' => '&',
					'value'    => '0',
				),
			),
			'placeholder' => '300px',
			'default'     => '300px',
		);

		$this->controls['zoom'] = array(
			'tab'         => 'content',
			'label'       => esc_html__( 'Zoom level', 'bricksable' ),
			'type'        => 'number',
			'step'        => 1,
			'min'         => 0,
			'max'         => 20,
			'placeholder' => 12,
			'default'     => 12,
		);
	}

	public function render() {
		$settings = $this->settings;
		if ( empty( $settings['address'] ) ) {
			return $this->render_element_placeholder( array( 'title' => esc_html__( 'No address provided.', 'bricksable' ) ) );
		}

		$google_api_key = esc_html( \Bricks\Database::get_setting( 'apiKeyGoogleMaps' ) );
		$api_type       = $settings['apiType'];
		$address        = $this->render_dynamic_data( $settings['address'] );

		$api_or_not = '';
		switch ( $api_type ) {
			case 'api':
				$api_or_not = empty( $google_api_key ) ? 'https://maps.google.com/maps?q=%1$s&amp;t=m&amp;z=%2$s&amp;output=embed&amp;iwloc=near;&hl=%3$s' : 'https://www.google.com/maps/embed/v1/place?key=%4$s&q=%1$s&amp;zoom=%2$s;&language=%3$s';
				break;
			case 'embed':
				$api_or_not = 'https://maps.google.com/maps?q=%1$s&amp;t=m&amp;z=%2$s&amp;output=embed&amp;iwloc=near;&hl=%3$s';
				break;
			default:
				$api_or_not = 'https://maps.google.com/maps?q=%1$s&amp;t=m&amp;z=%2$s&amp;output=embed&amp;iwloc=near;&hl=%3$s';
				break;
		}
		$google_settings_params = sprintf(
			$api_or_not,
			rawurlencode( $address ),
			esc_attr( $settings['zoom'] ),
			esc_attr( get_locale() ),
			$google_api_key
		);

		// Render.
		$this->set_attribute( 'iframe', 'frameborder', '0' );
		$this->set_attribute( 'iframe', 'scrolling', 'no' );
		$this->set_attribute( 'iframe', 'marginheight', '0' );
		$this->set_attribute( 'iframe', 'marginwidth', '0' );
		$this->set_attribute( 'iframe', 'referrerPolicy', 'no-referrer-when-downgrade' );
		$this->set_attribute( 'iframe', 'loading', 'lazy' );
		$this->set_attribute( 'iframe', 'src', esc_url( $google_settings_params ) );
		$this->set_attribute( 'iframe', 'title', esc_attr( $settings['address'] ) );
		$this->set_attribute( 'iframe', 'aria-label', esc_attr( $settings['address'] ) );
		$this->set_attribute( 'iframe', 'allowfullscreen' );

		$output  = "<div {$this->render_attributes( '_root' )}>";
		$output .= "<iframe {$this->render_attributes( 'iframe' )}>";
		$output .= '</iframe>';

		$output .= '</div>';
		//phpcs:ignore
		echo $output;
	}
}
