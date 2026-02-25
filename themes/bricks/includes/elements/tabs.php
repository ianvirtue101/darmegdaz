<?php
namespace Bricks;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Element_Tabs extends Element {
	public $category = 'general';
	public $name     = 'tabs';
	public $icon     = 'ti-layout-tab';
	public $scripts  = [ 'bricksTabs' ];

	public function get_label() {
		return esc_html__( 'Tabs', 'bricks' );
	}

	public function set_control_groups() {
		$this->control_groups['title'] = [
			'title' => esc_html__( 'Title', 'bricks' ),
			'tab'   => 'content',
		];

		$this->control_groups['content'] = [
			'title' => esc_html__( 'Content', 'bricks' ),
			'tab'   => 'content',
		];
	}

	public function set_controls() {
		$this->controls['tabs'] = [
			'tab'         => 'content',
			'placeholder' => esc_html__( 'Tab', 'bricks' ),
			'type'        => 'repeater',
			'description' => esc_html__( 'Set "ID" on items above to open via anchor link.', 'bricks' ) . ' ' . esc_html__( 'No spaces. No pound (#) sign.', 'bricks' ),
			'fields'      => [
				'icon'         => [
					'label' => esc_html__( 'Icon', 'bricks' ),
					'type'  => 'icon',
				],

				'iconPosition' => [
					'label'       => esc_html__( 'Icon position', 'bricks' ),
					'type'        => 'select',
					'options'     => $this->control_options['iconPosition'],
					'inline'      => true,
					'placeholder' => esc_html__( 'Left', 'bricks' ),
					'required'    => [ 'icon', '!=', '' ],
				],

				'title'        => [
					'label' => esc_html__( 'Title', 'bricks' ),
					'type'  => 'text',
				],

				'anchorId'     => [
					'label' => esc_html__( 'ID', 'bricks' ),
					'type'  => 'text',
				],

				'content'      => [
					'label' => esc_html__( 'Content', 'bricks' ),
					'type'  => 'editor',
				],
			],

			'default'     => [
				[
					'title'   => esc_html__( 'Title', 'bricks' ) . ' 1',
					'content' => esc_html__( 'Content goes here ..', 'bricks' ) . ' (1)',
				],
				[
					'title'   => esc_html__( 'Title', 'bricks' ) . ' 2',
					'content' => esc_html__( 'Content goes here ..', 'bricks' ) . ' (2)',
				],
			],
		];

		$this->controls['layout'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Layout', 'bricks' ),
			'type'        => 'select',
			'options'     => [
				'horizontal' => esc_html__( 'Horizontal', 'bricks' ),
				'vertical'   => esc_html__( 'Vertical', 'bricks' ),
			],
			'inline'      => true,
			'placeholder' => esc_html__( 'Horizontal', 'bricks' ),
		];

		$this->controls['openTabOn'] = [
			'label'       => esc_html__( 'Open tab on', 'bricks' ),
			'type'        => 'select',
			'options'     => [
				'click'      => esc_html__( 'Click', 'bricks' ),
				'mouseenter' => esc_html__( 'Hover', 'bricks' ),
			],
			'inline'      => true,
			'placeholder' => 'Click',
		];

		// Expand item on page load (@since 1.12)
		$this->controls['openTab'] = [
			'label'       => esc_html__( 'Open tab index', 'bricks' ),
			'type'        => 'text',
			'description' => esc_html__( 'Index of the item to expand on page load, start at 0.', 'bricks' ),
			'inline'      => true,
			'placeholder' => '0',
		];

		// TITLE

		$this->controls['titleGrow'] = [
			'tab'      => 'content',
			'group'    => 'title',
			'label'    => esc_html__( 'Stretch', 'bricks' ),
			'type'     => 'checkbox',
			'css'      => [
				[
					'selector' => '.tab-title',
					'property' => 'flex-grow',
					'value'    => '1',
				],
			],
			'required' => [ 'layout', '!=', 'vertical' ],
		];

		$this->controls['titleHorizontal'] = [
			'tab'      => 'content',
			'group'    => 'title',
			'label'    => esc_html__( 'Align', 'bricks' ),
			'type'     => 'justify-content',
			'css'      => [
				[
					'property' => 'justify-content',
					'selector' => '.tab-menu',
				],
			],
			'required' => [ 'layout', '!=', 'vertical' ],
		];

		$this->controls['titlePadding'] = [
			'tab'     => 'content',
			'group'   => 'title',
			'label'   => esc_html__( 'Padding', 'bricks' ),
			'type'    => 'spacing',
			'css'     => [
				[
					'property' => 'padding',
					'selector' => '.tab-title',
				],
			],
			'default' => [
				'top'    => 20,
				'right'  => 20,
				'bottom' => 20,
				'left'   => 20,
			],
		];

		$this->controls['titleBackgroundColor'] = [
			'tab'   => 'content',
			'group' => 'title',
			'label' => esc_html__( 'Background', 'bricks' ),
			'type'  => 'color',
			'css'   => [
				[
					'property' => 'background-color',
					'selector' => '.tab-title',
				],
			],
		];

		$this->controls['titleBorder'] = [
			'tab'     => 'content',
			'group'   => 'title',
			'label'   => esc_html__( 'Border', 'bricks' ),
			'type'    => 'border',
			'css'     => [
				[
					'property' => 'border',
					'selector' => '.tab-title',
				],
			],
			'default' => [
				'width' => [
					'top'    => 1,
					'right'  => 1,
					'bottom' => 0,
					'left'   => 1,
				],
				'style' => 'solid',
				'color' => [
					'rgb' => '#dedede',
				],
			],
		];

		$this->controls['titleTypography'] = [
			'tab'   => 'content',
			'group' => 'title',
			'label' => esc_html__( 'Typography', 'bricks' ),
			'type'  => 'typography',
			'css'   => [
				[
					'property' => 'font',
					'selector' => '.tab-title',
				],
			],
		];

		$this->controls['titleActiveBackgroundColor'] = [
			'tab'   => 'content',
			'group' => 'title',
			'label' => esc_html__( 'Active background', 'bricks' ),
			'type'  => 'color',
			'css'   => [
				[
					'property' => 'background-color',
					'selector' => '.tab-title.brx-open',
				],
			],
		];

		$this->controls['titleActiveBorder'] = [
			'tab'   => 'content',
			'group' => 'title',
			'label' => esc_html__( 'Active border', 'bricks' ),
			'type'  => 'border',
			'css'   => [
				[
					'property' => 'border',
					'selector' => '.tab-title.brx-open',
				],
			],
		];

		$this->controls['titleActiveTypography'] = [
			'tab'   => 'content',
			'group' => 'title',
			'label' => esc_html__( 'Active typography', 'bricks' ),
			'type'  => 'typography',
			'css'   => [
				[
					'property' => 'font',
					'selector' => '.tab-title.brx-open',
				],
			],
		];

		// CONTENT

		$this->controls['contentPadding'] = [
			'tab'     => 'content',
			'group'   => 'content',
			'label'   => esc_html__( 'Padding', 'bricks' ),
			'type'    => 'spacing',
			'css'     => [
				[
					'property' => 'padding',
					'selector' => '.tab-content',
				],
			],
			'default' => [
				'top'    => 20,
				'right'  => 20,
				'bottom' => 20,
				'left'   => 20,
			],
		];

		$this->controls['contentTextAlign'] = [
			'tab'    => 'content',
			'group'  => 'content',
			'type'   => 'text-align',
			'label'  => esc_html__( 'Text align', 'bricks' ),
			'css'    => [
				[
					'property' => 'text-align',
					'selector' => '.tab-content',
				],
			],
			'inline' => true,
		];

		$this->controls['contentColor'] = [
			'tab'   => 'content',
			'group' => 'content',
			'label' => esc_html__( 'Text color', 'bricks' ),
			'type'  => 'color',
			'css'   => [
				[
					'property' => 'color',
					'selector' => '.tab-content',
				],
			],
		];

		$this->controls['contentBackgroundColor'] = [
			'tab'   => 'content',
			'group' => 'content',
			'label' => esc_html__( 'Background color', 'bricks' ),
			'type'  => 'color',
			'css'   => [
				[
					'property' => 'background-color',
					'selector' => '.tab-content',
				],
			],
		];

		$this->controls['contentBorder'] = [
			'tab'     => 'content',
			'group'   => 'content',
			'label'   => esc_html__( 'Border', 'bricks' ),
			'type'    => 'border',
			'css'     => [
				[
					'property' => 'border',
					'selector' => '.tab-content',
				],
			],
			'default' => [
				'width' => [
					'top'    => 1,
					'right'  => 1,
					'bottom' => 1,
					'left'   => 1,
				],
				'style' => 'solid',
				'color' => [
					'rgb' => '#dedede',
				],
			],
		];
	}

	public function render() {
		$settings = $this->settings;

		if ( empty( $settings['tabs'] ) ) {
			return $this->render_element_placeholder(
				[
					'title' => esc_html__( 'No tabs added.', 'bricks' ),
				]
			);
		}

		$layout = $settings['layout'] ?? 'horizontal';

		$this->set_attribute( '_root', 'class', $layout );
		$this->set_attribute( '_root', 'aria-orientation', $layout );

		// Open tab on hover (@since 1.10.2)
		if ( ! empty( $settings['openTabOn'] ) ) {
			$this->set_attribute( '_root', 'data-open-on', $settings['openTabOn'] );
		}

		// Expand item on page load (@since 1.12)
		$this->set_attribute( '_root', 'data-open-tab', ! isset( $settings['openTab'] ) ? 0 : $settings['openTab'] );

		// Render
		$output  = "<div {$this->render_attributes( '_root' )}>";
		$output .= '<ul class="tab-menu" role="tablist">';

		foreach ( $settings['tabs'] as $index => $tab ) {
			$tab_title_classes = [ 'tab-title', 'repeater-item' ];

			if ( ! empty( $tab['iconPosition'] ) ) {
				$tab_title_classes[] = "icon-{$tab['iconPosition']}";
			}

			// Set 'id' to open & scroll to specific tab (@since 1.8.6)
			if ( ! empty( $tab['anchorId'] ) ) {
				$this->set_attribute( "tab-title-$index", 'id', $tab['anchorId'] );
			}

			$selected = $index === 0 ? 'true' : 'false';
			$tabindex = $index === 0 ? '0' : '-1';

			$this->set_attribute( "tab-title-$index", 'class', $tab_title_classes );

			$this->set_attribute( "tab-title-$index", 'class', $tab_title_classes );
			$this->set_attribute( "tab-title-$index", 'role', 'tab' );
			$this->set_attribute( "tab-title-$index", 'aria-selected', $selected );
			$this->set_attribute( "tab-title-$index", 'aria-controls', "panel-$this->id-$index" );
			$this->set_attribute( "tab-title-$index", 'id', "brx-tab-{$this->id}-$index" );
			$this->set_attribute( "tab-title-$index", 'tabindex', $tabindex );

			$output .= "<li {$this->render_attributes( "tab-title-$index" )}>";

			// Icon
			$icon = ! empty( $tab['icon'] ) ? self::render_icon( $tab['icon'] ) : false;

			if ( $icon ) {
				$output .= $icon;
			}

			if ( ! empty( $tab['title'] ) ) {
				$output .= "<span>{$this->render_dynamic_data( $tab['title'] )}</span>";
			}

			$output .= '</li>';
		}

		$output .= '</ul>';

		$output .= '<ul class="tab-content" role="presentation">';

		foreach ( $settings['tabs'] as $index => $tab ) {
			$tab_pane_classes = [ 'tab-pane' ];

			$this->set_attribute( "tab-pane-$index", 'class', $tab_pane_classes );
			$this->set_attribute( "tab-pane-$index", 'role', 'tabpanel' );
			$this->set_attribute( "tab-pane-$index", 'aria-labelledby', "brx-tab-$this->id-$index" );
			$this->set_attribute( "tab-pane-$index", 'id', "panel-$this->id-$index" );
			$this->set_attribute( "tab-pane-$index", 'tabindex', '0' );

			$content = ! empty( $tab['content'] ) ? $tab['content'] : false;
			$content = $this->render_dynamic_data( $content );

			$output .= "<li {$this->render_attributes( "tab-pane-$index" )}>" . Helpers::parse_editor_content( $content ) . '</li>';
		}

		$output .= '</ul>';

		$output .= '</div>';

		echo $output;
	}
}
