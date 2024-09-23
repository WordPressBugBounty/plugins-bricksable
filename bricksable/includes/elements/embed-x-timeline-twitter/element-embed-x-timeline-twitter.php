<?php
namespace Bricks;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Bricksable_Embed_X_Timeline extends Element {
	public $category     = 'bricksable';
	public $name         = 'ba-embed-x-timeline';
	public $icon         = 'ti-twitter';
	public $css_selector = '&.brxe-ba-embed-x-timeline .twitter-timeline>iframe';
	public $scripts      = array( 'bricksableEmbedXTimeline' );
	public $draggable    = false;

	public function get_label() {
		return esc_html__( 'Embed X Timeline (Twitter)', 'bricksable' );
	}

	public function set_controls() {
		$this->controls['xProfile'] = array(
			'tab'         => 'content',
			'label'       => esc_html__( 'X (Twitter) Profile', 'bricksable' ),
			'description' => esc_html__( 'Enter the profile without @.', 'bricksable' ),
			'type'        => 'text',
			'placeholder' => 'WordPress',
			'default'     => 'WordPress',
			'rerender'    => true,
		);

		$this->controls['height'] = array(
			'tab'         => 'content',
			'label'       => esc_html__( 'Height', 'bricksable' ) . ' (px)',
			'info'        => esc_html__( 'Min. height is 200.', 'bricksable' ),
			'type'        => 'number',
			'min'         => 200,
			'placeholder' => 680,
			'default'     => 680,
			'rerender'    => true,
		);

		$this->controls['width'] = array(
			'tab'         => 'content',
			'label'       => esc_html__( 'Width', 'bricksable' ) . ' (px)',
			'info'        => esc_html__( 'Enter width between 220 and 1200.', 'bricksable' ),
			'type'        => 'number',
			'min'         => 220,
			'max'         => 1200,
			'placeholder' => 350,
			'default'     => 350,
			'rerender'    => true,
		);

		$this->controls['theme'] = array(
			'tab'         => 'content',
			'label'       => esc_html__( 'Theme', 'bricksable' ),
			'type'        => 'select',
			'options'     => array(
				'light' => esc_html__( 'Light', 'bricksable' ),
				'dark'  => esc_html__( 'Dark', 'bricksable' ),
			),
			'placeholder' => esc_html__( 'Light', 'bricksable' ),
			'default'     => 'light',
			'inline'      => true,
		);

		// To be added in future version.
		/*
		$this->controls['TweetLimit'] = array(
			'tab'      => 'content',
			'label'    => esc_html__( 'Tweet Limit', 'bricksable' ),
			'info'     => esc_html__( 'Limiting the number of Tweets displayed.', 'bricksable' ),
			'type'     => 'checkbox',
			'rerender' => true,
		);

		$this->controls['TweetLimitNo'] = array(
			'tab'      => 'content',
			'label'    => esc_html__( 'Number of Tweets', 'bricksable' ),
			'info'     => esc_html__( 'Display a specific number of tweets between 1 and 20.', 'bricksable' ),
			'type'     => 'number',
			'unitless' => true,
			'min'      => 1,
			'max'      => 20,
			'step'     => '1',
			'default'  => 5,
			'required' => array( 'TweetLimit', '!=', '' ),
		);*/

		$this->controls['noHeader'] = array(
			'tab'      => 'content',
			'label'    => esc_html__( 'No Header', 'bricksable' ),
			'info'     => esc_html__( 'Hides the timeline header. Implementing sites must add their own Twitter attribution, link to the source timeline, and comply with other Twitter display requirements.', 'bricksable' ),
			'type'     => 'checkbox',
			'rerender' => true,
		);

		$this->controls['noFooter'] = array(
			'tab'      => 'content',
			'label'    => esc_html__( 'No Footer', 'bricksable' ),
			'info'     => esc_html__( 'Hides the timeline footer and Tweet composer link, if included in the timeline widget type.', 'bricksable' ),
			'type'     => 'checkbox',
			'rerender' => true,
		);

		$this->controls['noBorders'] = array(
			'tab'      => 'content',
			'label'    => esc_html__( 'No Borders', 'bricksable' ),
			'info'     => esc_html__( 'Removes all borders within the widget including borders surrounding the widget area and separating Tweets.', 'bricksable' ),
			'type'     => 'checkbox',
			'rerender' => true,
		);

		$this->controls['noScrollBar'] = array(
			'tab'      => 'content',
			'label'    => esc_html__( 'No Scrollbar', 'bricksable' ),
			'info'     => esc_html__( 'Crops and hides the main timeline scrollbar, if visible. Please consider that hiding standard user interface components can affect the accessibility of your website.', 'bricksable' ),
			'type'     => 'checkbox',
			'rerender' => true,
		);

		$this->controls['transparent'] = array(
			'tab'      => 'content',
			'label'    => esc_html__( 'Transparent', 'bricksable' ),
			'info'     => esc_html__( 'Removes the widget’s background color.', 'bricksable' ),
			'type'     => 'checkbox',
			'rerender' => true,
		);

		$this->controls['dnt'] = array(
			'tab'         => 'content',
			'label'       => esc_html__( 'Opt-out of tailoring Twitter', 'bricksable' ),
			'description' => esc_html__( 'When you view Twitter content such as embedded timelines integrated into other websites using Twitter for Websites, Twitter may receive information, including the web page you visited, your IP address, browser type, operating system, and cookie information. This information helps us to improve our products and services. Learn more about the information we receive and how we use it in our privacy policy and cookies policy.', 'bricksable' ),
			'type'        => 'checkbox',
			'rerender'    => true,
		);
	}

	// Methods: Frontend-specific.
	public function enqueue_scripts() {
		wp_enqueue_script( 'ba-embed-x-timeline-twitter' );
	}

	public function render() {
		$settings = $this->settings;

		if ( empty( $settings['xProfile'] ) ) {
			return $this->render_element_placeholder( array( 'title' => esc_html__( 'No X (Twitter) Profile provided.', 'bricksable' ) ) );
		}
		$this->set_attribute( 'embed', 'class', array( 'twitter-timeline' ) );
		$this->set_attribute( 'embed', 'href', esc_url( 'https://twitter.com/' ) . esc_attr( $settings['xProfile'] ) );
		$this->set_attribute( 'embed', 'data-theme', esc_attr( $settings['theme'] ) );
		// https://developer.twitter.com/en/docs/twitter-for-websites/timelines/overview.
		$this->set_attribute( 'embed', 'data-chrome', isset( $settings['noHeader'] ) ? 'noheader' : '' );
		$this->set_attribute( 'embed', 'data-chrome', isset( $settings['noFooter'] ) ? 'nofooter' : '' );
		$this->set_attribute( 'embed', 'data-chrome', isset( $settings['noBorders'] ) ? 'noborders' : '' );
		$this->set_attribute( 'embed', 'data-chrome', isset( $settings['noScrollBar'] ) ? 'noscrollbar' : '' );

		if ( isset( $settings['dnt'] ) ) {
			$this->set_attribute( 'embed', 'data-dnt', 'true' );
		}

		/*
		if ( isset( $settings['TweetLimit'] ) ) {
			$this->set_attribute( 'embed', 'data-tweet-limit', $settings['TweetLimitNo'] );
		}
		*/

		if ( ! empty( $settings['width'] ) ) {
			$this->set_attribute( 'embed', 'data-width', $settings['width'] );
		}

		if ( ! empty( $settings['height'] ) ) {
			$this->set_attribute( 'embed', 'data-height', $settings['height'] );
		}

		//phpcs:ignore
		echo "<div {$this->render_attributes( '_root' )}>";
		//phpcs:ignore
		echo "<a {$this->render_attributes( 'embed' )}></a>";
		echo '</div>';
	}
}
