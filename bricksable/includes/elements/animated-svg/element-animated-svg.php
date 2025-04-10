<?php
namespace Bricks;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Bricksable_Animated_SVG extends Element {
	public $category = 'bricksable';
	public $name     = 'ba-animated-svg';
	public $icon     = 'ti-vector';
	public $tag      = 'svg';
	public $scripts  = array( 'bricksableAnimatedSVG' );

	public function get_label() {
		return esc_html__( 'Animated SVG', 'bricksable' );
	}

	public function get_keywords() {
		return array( 'svg' );
	}

	public function set_control_groups() {
		$this->control_groups['settings'] = array(
			'title' => esc_html__( 'Animation Settings', 'bricksable' ),
			'tab'   => 'content',
		);
		unset( $this->control_groups['_typography'] );
	}

	public function enqueue_scripts() {
		wp_enqueue_style( 'ba-animated-svg' );
		wp_enqueue_script( 'ba-animated-svg' );
		wp_localize_script(
			'ba-animated-svg',
			'bricksableAnimatedSVGData',
			array(
				'animatedSVGInstances' => array(),
			)
		);
	}

	public function set_controls() {
		$this->controls['file'] = array(
			'tab'  => 'content',
			'type' => 'svg',
		);

		$this->controls['svgAnimationInfo'] = array(
			'tab'     => 'content',
			'content' => esc_html__( 'For optimal experience, works best with stroke and transparent fill. Feel free to adjust the settings to fit your design.', 'bricksable' ),
			'type'    => 'info',
		);

		$this->controls['height'] = array(
			'tab'      => 'content',
			'label'    => esc_html__( 'Height', 'bricksable' ),
			'type'     => 'number',
			'units'    => true,
			'css'      => array(
				array(
					'property' => 'height',
					'selector' => 'svg',
				),
			),
			'required' => array( 'file', '!=', '' ),
		);

		$this->controls['width'] = array(
			'tab'      => 'content',
			'label'    => esc_html__( 'Width', 'bricksable' ),
			'type'     => 'number',
			'units'    => true,
			'css'      => array(
				array(
					'property' => 'width',
					'selector' => 'svg',
				),
			),
			'required' => array( 'file', '!=', '' ),
		);

		$this->controls['strokeWidth'] = array(
			'tab'      => 'content',
			'label'    => esc_html__( 'Stroke width', 'bricksable' ),
			'type'     => 'number',
			'min'      => 1,
			'css'      => array(
				array(
					'property'  => 'stroke-width',
					'selector'  => ' *',
					'important' => true,
				),
			),
			'required' => array( 'file', '!=', '' ),
		);

		$this->controls['stroke'] = array(
			'tab'      => 'content',
			'label'    => esc_html__( 'Stroke color', 'bricksable' ),
			'type'     => 'color',
			'css'      => array(
				array(
					'property'  => 'stroke',
					'selector'  => ' :not([stroke="none"])',
					'important' => true,
				),
			),
			'required' => array( 'file', '!=', '' ),
		);

		$this->controls['fill'] = array(
			'tab'      => 'content',
			'label'    => esc_html__( 'Fill', 'bricksable' ),
			'type'     => 'color',
			'css'      => array(
				array(
					'property'  => 'fill',
					'selector'  => ' :not([fill="none"])',
					'important' => true,
				),
			),
			'required' => array( 'file', '!=', '' ),
		);

		$this->controls['svgAnimationStart'] = array(
			'tab'         => 'content',
			'group'       => 'settings',
			'label'       => esc_html__( 'Start Animation', 'bricksable' ),
			'type'        => 'select',
			'options'     => array(
				'inViewport' => esc_html__( 'In Viewport', 'bricksable' ),
				'autostart'  => esc_html__( 'Autostart', 'bricksable' ),
				'manual'     => esc_html__( 'Manual', 'bricksable' ),

			),
			'placeholder' => esc_html__( 'In Viewport', 'bricksable' ),
			'default'     => 'inViewport',
			'rerender'    => true,
		);

		$this->controls['svgAnimationReAnimation'] = array(
			'tab'      => 'content',
			'group'    => 'settings',
			'label'    => esc_html__( 'Animate (On Every Viewport)', 'bricksable' ),
			'type'     => 'checkbox',
			'default'    => false,
			'required' => array( 'svgAnimationStart', '=', 'inViewport' ),
		);

		$this->controls['svgAnimationManual'] = array(
			'tab'         => 'content',
			'group'       => 'settings',
			'label'       => esc_html__( 'Animation Type', 'bricksable' ),
			'type'        => 'select',
			'options'     => array(
				'hover' => esc_html__( 'Hover', 'bricksable' ),
				'click' => esc_html__( 'Click', 'bricksable' ),
			),
			'placeholder' => esc_html__( 'Hover', 'bricksable' ),
			'default'     => 'hover',
			'rerender'    => true,
			'required'    => array( 'svgAnimationStart', '=', 'manual' ),
		);

		$this->controls['svgAnimationMouseLeave'] = array(
			'tab'         => 'content',
			'group'       => 'settings',
			'label'       => esc_html__( 'On Mouse Leave', 'bricksable' ),
			'type'        => 'select',
			'options'     => array(
				'stop'    => esc_html__( 'Stop Animate', 'bricksable' ),
				'reverse' => esc_html__( 'Reverse Animate', 'bricksable' ),
			),
			'placeholder' => esc_html__( 'Stop Animate', 'bricksable' ),
			'default'     => 'stop',
			'rerender'    => true,
			'required'    => array(
				array( 'svgAnimationStart', '=', 'manual' ),
				array( 'svgAnimationManual', '=', 'hover' ),
			),
		);

		$this->controls['svgAnimationReRender'] = array(
			'tab'         => 'content',
			'group'       => 'settings',
			'label'       => esc_html__( 'Re-render (On Hover)', 'bricksable' ),
			'type'        => 'checkbox',
			'inline'      => true,
			'description' => esc_html__( 'Turning this on will trigger the SVG animation to restart after the hover animation finishes.', 'bricksable' ),
			'required'    => array(
				array( 'svgAnimationStart', '=', 'manual' ),
				array( 'svgAnimationManual', '=', 'hover' ),
			),
		);

		$this->controls['svgAnimationType'] = array(
			'tab'         => 'content',
			'group'       => 'settings',
			'label'       => esc_html__( 'Animation Type', 'bricksable' ),
			'type'        => 'select',
			'options'     => array(
				'delayed'  => esc_html__( 'Delayed', 'bricksable' ),
				'sync'     => esc_html__( 'Sync', 'bricksable' ),
				'oneByOne' => esc_html__( 'oneByOne', 'bricksable' ),
			),
			'default'     => 'delayed',
			'rerender'    => true,
			'description' => esc_html__( 'Defines what kind of animation will be used.', 'bricksable' ),
		);

		$this->controls['svgAnimationDuration'] = array(
			'tab'         => 'content',
			'group'       => 'settings',
			'label'       => esc_html__( 'Duration', 'bricksable' ),
			'type'        => 'number',
			'units'       => false,
			'default'     => '200',
			'rerender'    => true,
			'description' => esc_html__( 'Animation duration, in frames.', 'bricksable' ),
		);

		$this->controls['svgAnimationDelay'] = array(
			'tab'         => 'content',
			'group'       => 'settings',
			'label'       => esc_html__( 'Delay', 'bricksable' ),
			'type'        => 'number',
			'units'       => false,
			'default'     => '0',
			'rerender'    => true,
			'required'    => array( 'svgAnimationType', '=', 'delayed' ),
			'description' => esc_html__( 'Time between the drawing of first and last path, in frames (only for delayed animations). Delay must be shorter than duration.', 'bricksable' ),
		);

		$this->controls['svgAnimationAnimTimingFunction'] = array(
			'tab'      => 'content',
			'group'    => 'settings',
			'label'    => esc_html__( 'Animation Timing Function', 'bricksable' ),
			'type'     => 'select',
			'options'  => array(
				'EASE'            => esc_html__( 'Ease', 'bricksable' ),
				'EASE_IN'         => esc_html__( 'Ease In', 'bricksable' ),
				'EASE_OUT'        => esc_html__( 'Ease Out', 'bricksable' ),
				'EASE_OUT_BOUNCE' => esc_html__( 'Ease Out Bounce', 'bricksable' ),
			),
			'default'  => 'EASE',
			'rerender' => true,
		);
	}

	public function render() {
		$settings = $this->settings;
		$svg_path = ! empty( $this->settings['file']['id'] ) ? get_attached_file( $this->settings['file']['id'] ) : false;
		$svg      = $svg_path ? Helpers::file_get_contents( $svg_path ) : false;

		if ( ! $svg ) {
			return $this->render_element_placeholder( array( 'title' => esc_html__( 'No SVG selected.', 'bricksable' ) ) );
		}

		// Animated SVG Settings Options.
		$animated_svg_options = array(
			'start'                => isset( $settings['svgAnimationStart'] ) ? $settings['svgAnimationStart'] : 'inViewport',
			'manualMethod'         => isset( $settings['svgAnimationManual'] ) ? $settings['svgAnimationManual'] : 'hover',
			'onMouseLeave'         => isset( $settings['svgAnimationMouseLeave'] ) ? $settings['svgAnimationMouseLeave'] : 'reverse',
			'type'                 => isset( $settings['svgAnimationType'] ) ? $settings['svgAnimationType'] : 'delayed',
			'duration'             => isset( $settings['svgAnimationDuration'] ) ? $settings['svgAnimationDuration'] : '200',
			'delay'                => isset( $settings['svgAnimationDelay'] ) && 'delayed' === $settings['svgAnimationType'] ? $settings['svgAnimationDelay'] : '0',
			'animTimingFunction'   => isset( $settings['svgAnimationAnimTimingFunction'] ) ? $settings['svgAnimationAnimTimingFunction'] : 'EASE',
			'svgAnimationReRender' => isset( $settings['svgAnimationReRender'] ) ? true : false,
			'svgAnimationReAnimation' => isset( $settings['svgAnimationReAnimation'] ) ? true : false,
		);

		$this->set_attribute( '_root', 'data-ba-bricks-animated-svg-options', wp_json_encode( $animated_svg_options ) );
		//phpcs:ignore
		echo "<div {$this->render_attributes( '_root' )}>";
		//phpcs:ignore
		echo self::render_svg( $svg );
		//phpcs:ignore
		echo '</div>';
	}
}
