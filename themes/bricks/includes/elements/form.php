<?php
namespace Bricks;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Element_Form extends Element {
	public $category = 'general';
	public $name     = 'form';
	public $icon     = 'ti-layout-cta-left';
	public $tag      = 'form';
	public $scripts  = [ 'bricksForm' ];

	public function get_label() {
		return esc_html__( 'Form', 'bricks' );
	}

	public function enqueue_scripts() {
		if ( isset( $this->settings['enableRecaptcha'] ) ) {
			wp_enqueue_script( 'bricks-google-recaptcha' );
		}

		if ( isset( $this->settings['enableHCaptcha'] ) ) {
			wp_enqueue_script( 'bricks-hcaptcha' );
		}

		if ( isset( $this->settings['enableTurnstile'] ) ) {
			wp_enqueue_script( 'bricks-turnstile' );
		}

		/**
		 * Load Flatpickr library (form field type 'date' found)
		 *
		 * @since 1.8.6 - Load localization file if set (default: English)
		 */
		if ( ! empty( $this->settings['fields'] ) && is_array( $this->settings['fields'] ) ) {
			foreach ( $this->settings['fields'] as $field ) {
				if ( $field['type'] === 'datepicker' ) {
					if ( ! bricks_is_builder() ) {
						wp_enqueue_script( 'bricks-flatpickr' );
						wp_enqueue_style( 'bricks-flatpickr' );
					}

					// Load datepicker localisation
					$l10n = $field['l10n'] ?? '';
					if ( $l10n ) {
						// Hosted locally (@since 2.0)
						wp_enqueue_script( 'bricks-flatpickr-l10n', BRICKS_URL_ASSETS . "js/libs/flatpickr-l10n/$l10n.min.js", [ 'bricks-flatpickr' ], null );
					}
				}
			}
		}
	}

	public function set_control_groups() {
		$this->control_groups['fields'] = [
			'title' => esc_html__( 'Fields', 'bricks' ),
		];

		$this->control_groups['submitButton'] = [
			'title' => esc_html__( 'Submit button', 'bricks' ),
		];

		$this->control_groups['actions'] = [
			'title' => esc_html__( 'Actions', 'bricks' ),
		];

		$this->control_groups['notices'] = [
			'title' => esc_html__( 'Notices', 'bricks' ),
		];

		$this->control_groups['email'] = [
			'title'    => esc_html__( 'Email', 'bricks' ),
			'required' => [ 'actions', '=', 'email' ],
		];

		$this->control_groups['webhook'] = [
			'title'    => esc_html__( 'Webhook', 'bricks' ),
			'required' => [ 'actions', '=', 'webhook' ],
		];

		$this->control_groups['confirmation'] = [
			'title'    => esc_html__( 'Confirmation email', 'bricks' ),
			'required' => [ 'actions', '=', 'email' ],
		];

		$this->control_groups['redirect'] = [
			'title'    => esc_html__( 'Redirect', 'bricks' ),
			'required' => [ 'actions', '=', 'redirect' ],
		];

		$this->control_groups['mailchimp'] = [
			'title'    => 'Mailchimp',
			'required' => [ 'actions', '=', 'mailchimp' ],
		];

		$this->control_groups['sendgrid'] = [
			'title'    => 'Sendgrid',
			'required' => [ 'actions', '=', 'sendgrid' ],
		];

		$this->control_groups['registration'] = [
			'title'    => esc_html__( 'User Registration', 'bricks' ),
			'required' => [ 'actions', '=', 'registration' ],
		];

		$this->control_groups['login'] = [
			'title'    => esc_html__( 'User Login', 'bricks' ),
			'required' => [ 'actions', '=', 'login' ],
		];

		$this->control_groups['lostPassword'] = [
			'title'    => esc_html__( 'Lost password', 'bricks' ),
			'required' => [ 'actions', '=', 'lost-password' ],
		];

		$this->control_groups['resetPassword'] = [
			'title'    => esc_html__( 'Reset password', 'bricks' ),
			'required' => [ 'actions', '=', 'reset-password' ],
		];

		// @since 1.9.2
		if ( \Bricks\Database::get_setting( 'saveFormSubmissions', false ) ) {
			$this->control_groups['save-submission'] = [
				'title'    => esc_html__( 'Save submission', 'bricks' ),
				'required' => [ 'actions', '=', 'save-submission' ],
			];
		}

		// @since 1.11.1
		$this->control_groups['unlock-password-protection'] = [
			'title'    => esc_html__( 'Unlock password protection', 'bricks' ),
			'required' => [ 'actions', '=', 'unlock-password-protection' ],
		];

		$this->control_groups['spam'] = [
			'title' => esc_html__( 'Spam protection', 'bricks' ),
		];
	}

	public function set_controls() {
		// Get wp date format (in builder)
		$date_format = bricks_is_builder() ? get_option( 'date_format' ) : '';

		// Group: Fields
		$this->controls['fields'] = [
			'tab'           => 'content',
			'group'         => 'fields',
			'placeholder'   => esc_html__( 'Field', 'bricks' ),
			'type'          => 'repeater',
			'selector'      => '.form-group',
			'titleProperty' => 'label',
			'fields'        => [
				'type'                       => [
					'label'     => esc_html__( 'Type', 'bricks' ),
					'type'      => 'select',
					'options'   => [
						'email'      => esc_html__( 'Email', 'bricks' ),
						'text'       => esc_html__( 'Text', 'bricks' ),
						'textarea'   => esc_html__( 'Textarea', 'bricks' ),
						'tel'        => esc_html__( 'Tel', 'bricks' ),
						'number'     => esc_html__( 'Number', 'bricks' ),
						'url'        => esc_html__( 'URL', 'bricks' ),
						'checkbox'   => esc_html__( 'Checkbox', 'bricks' ),
						'select'     => esc_html__( 'Select', 'bricks' ),
						'radio'      => esc_html__( 'Radio', 'bricks' ),
						'file'       => esc_html__( 'Files', 'bricks' ),
						'datepicker' => esc_html__( 'Datepicker', 'bricks' ),
						'password'   => esc_html__( 'Password', 'bricks' ),
						'rememberme' => esc_html__( 'Remember me', 'bricks' ),
						'html'       => 'HTML',
						'hidden'     => esc_html__( 'Hidden', 'bricks' ),
					],
					'clearable' => false,
				],

				// Password protection info
				'passwordProtectionInfo'     => [
					'content'  => sprintf(
						// Translators: %s = Article link
						'%s %s',
						esc_html__( 'Set the required password under Settings > Template settings > Password protection for template-wide protection, or set an individual password directly for each post/page.', 'bricks' ),
						Helpers::article_link( 'password-protection/#password-source-options', esc_html__( 'Learn more', 'bricks' ) )
					),
					'type'     => 'info',
					'required' => [ 'type', '=', 'password' ],
				],

				// Password toggle (@since 1.12)
				'passwordToggle'             => [
					'label'    => esc_html__( 'Password toggle', 'bricks' ),
					'type'     => 'checkbox',
					'required' => [ 'type', '=', 'password' ],
				],

				'passwordShowIcon'           => [
					'label'    => esc_html__( 'Show password', 'bricks' ) . ': ' . esc_html__( 'Icon', 'bricks' ),
					'type'     => 'icon',
					'required' => [
						[ 'type', '=', 'password' ],
						[ 'passwordToggle', '=', true ],
					],
				],

				'passwordHideIcon'           => [
					'label'    => esc_html__( 'Hide password', 'bricks' ) . ': ' . esc_html__( 'Icon', 'bricks' ),
					'type'     => 'icon',
					'required' => [
						[ 'type', '=', 'password' ],
						[ 'passwordToggle', '=', true ],
					],
				],

				'passwordShowIconTypography' => [
					'label'    => esc_html__( 'Show password', 'bricks' ) . ': ' . esc_html__( 'Typography', 'bricks' ),
					'type'     => 'typography',
					'css'      => [
						[
							'property' => 'font',
							'selector' => '.password-toggle .show-password i',
						],
					],
					'required' => [
						[ 'type', '=', 'password' ],
						[ 'passwordToggle', '=', true ],
					],
				],

				'passwordHideIconTypography' => [
					'label'    => esc_html__( 'Hide password', 'bricks' ) . ': ' . esc_html__( 'Typography', 'bricks' ),
					'type'     => 'typography',
					'css'      => [
						[
							'property' => 'font',
							'selector' => '.password-toggle .hide-password i',
						],
					],
					'required' => [
						[ 'type', '=', 'password' ],
						[ 'passwordToggle', '=', true ],
					],
				],

				'passwordIconPosition'       => [
					'label'       => esc_html__( 'Icon position', 'bricks' ),
					'type'        => 'dimensions',
					'css'         => [
						[
							'selector' => '.password-toggle',
						],
					],
					'placeholder' => [
						'top'   => '50%',
						'right' => '12px',
					],
					'required'    => [
						[ 'type', '=', 'password' ],
						[ 'passwordToggle', '=', true ],
					],
				],

				'min'                        => [
					'label'    => esc_html__( 'Min', 'bricks' ),
					'type'     => 'number',
					'min'      => 0,
					'max'      => 100,
					'required' => [ 'type', '=', [ 'number' ] ],
				],

				'max'                        => [
					'label'    => esc_html__( 'Max', 'bricks' ),
					'type'     => 'number',
					'min'      => 0,
					'max'      => 100,
					'required' => [ 'type', '=', [ 'number' ] ],
				],

				'step'                       => [
					'label'    => esc_html__( 'Step', 'bricks' ),
					'type'     => 'number',
					'min'      => 0,
					'required' => [ 'type', '=', [ 'number' ] ],
				],

				'label'                      => [
					'label' => esc_html__( 'Label', 'bricks' ),
					'type'  => 'text',
				],

				'placeholder'                => [
					'label'    => esc_html__( 'Placeholder', 'bricks' ),
					'type'     => 'text',
					'required' => [ 'type', '!=', [ 'file', 'hidden', 'html' ] ],
				],

				'value'                      => [
					'label'    => esc_html__( 'Value', 'bricks' ),
					'type'     => 'text',
					'info'     => esc_html__( 'Set the default field value/content.', 'bricks' ),
					'required' => [
						[ 'type', '!=', [ 'file', 'html' ] ],
						[ 'isHoneypot', '!=', true ], // Honeypot fields value should be empty by default (@since 1.12.2)
					],
				],

				'minLength'                  => [
					'label'    => esc_html__( 'Min. length', 'bricks' ),
					'type'     => 'number',
					'min'      => 0,
					'required' => [ 'type', '=', [ 'email', 'number', 'text', 'tel', 'url', 'password', 'textarea' ] ],
				],

				'maxLength'                  => [
					'label'    => esc_html__( 'Max. length', 'bricks' ),
					'type'     => 'number',
					'min'      => 0,
					'required' => [ 'type', '=', [ 'email', 'number', 'text', 'tel', 'url', 'password', 'textarea' ] ],
				],

				'checkboxInfo'               => [
					'content'  => esc_html__( 'Separate values by comma.', 'bricks' ),
					'type'     => 'info',
					'required' => [
						[ 'type', '=', 'checkbox' ],
						[ 'value', '!=', '' ],
					],
				],

				'datepickerInfo'             => [
					'content'  => esc_html__( 'Use the date format as set under Settings > General > Date format', 'bricks' ) . " ($date_format)",
					'type'     => 'info',
					'required' => [
						[ 'type', '=', 'datepicker' ],
						[ 'isHoneypot', '!=', true ], // For Honeypot we don't show value, so we also don't show the alert message (@since 1.12.2)
					]
				],

				'name'                       => [
					'label'    => esc_html__( 'Attribute', 'bricks' ) . ': ' . esc_html__( 'Name', 'bricks' ),
					'type'     => 'text',
					'info'     => esc_html__( 'Use valid HTML syntax. No spaces.', 'bricks' ),
					'required' => [ 'type', '!=', [ 'html' ] ],
				],

				// @since 1.9.9
				'autocomplete'               => [
					'label'    => esc_html__( 'Attribute', 'bricks' ) . ': ' . esc_html__( 'Autocomplete', 'bricks' ),
					'type'     => 'text',
					'info'     => 'on/off',
					'required' => [
						[ 'type', '=', [ 'text', 'textarea', 'email', 'number', 'password', 'tel', 'url' ] ],
						[ 'isHoneypot', '!=', true ] // Honeypot fields will have autocomplete set to "off" (@since 1.12.2)
					],
				],

				// @since 1.9.9
				'spellcheck'                 => [
					'label'    => esc_html__( 'Attribute', 'bricks' ) . ': ' . esc_html__( 'Spellcheck', 'bricks' ),
					'type'     => 'text',
					'info'     => 'off, etc.',
					'required' => [ 'type', '=', [ 'text', 'textarea', 'email', 'url' ] ],
				],

				'errorMessage'               => [
					'label'    => esc_html__( 'Error message', 'bricks' ),
					'type'     => 'text',
					'info'     => esc_html__( 'On input, blur and submit', 'bricks' ),
					'required' => [ 'type', '!=', [ 'hidden', 'radio', 'checkbox', 'select', 'file', 'datepicker' ] ],
				],

				'fileUploadSeparator'        => [
					'label'    => esc_html__( 'Files', 'bricks' ),
					'type'     => 'separator',
					'required' => [ 'type', '=', 'file' ],
				],

				'fileUploadButtonText'       => [
					'type'        => 'text',
					'placeholder' => esc_html__( 'Choose files', 'bricks' ),
					'default'     => esc_html__( 'Choose files', 'bricks' ),
					'required'    => [ 'type', '=', 'file' ],
				],

				'fileUploadLimit'            => [
					'label'    => esc_html__( 'Max. files', 'bricks' ) . ' (#)',
					'type'     => 'number',
					'min'      => 1,
					'max'      => 50,
					'required' => [ 'type', '=', 'file' ],
				],

				'fileUploadSize'             => [
					'label'    => esc_html__( 'Max. size', 'bricks' ) . ' (MB)',
					'type'     => 'number',
					'min'      => 1,
					'max'      => 50,
					'required' => [ 'type', '=', 'file' ],
				],

				// Save uploaded files (@since 1.9.2)
				'fileUploadStorage'          => [
					'label'       => esc_html__( 'Save file', 'bricks' ),
					'type'        => 'select',
					'options'     => [
						'attachment' => esc_html__( 'Save in media library', 'bricks' ),
						'directory'  => esc_html__( 'Save in custom directory', 'bricks' ),
					],
					'placeholder' => esc_html__( 'No', 'bricks' ),
					'required'    => [ 'type', '=', 'file' ],
				],

				'fileUploadStorageDirectory' => [
					'label'       => esc_html__( 'Directory name', 'bricks' ),
					'type'        => 'text',
					'placeholder' => 'form-files',
					'desc'        => esc_html__( 'Directory is created in your "uploads" directory if it doesn\'t exist.', 'bricks' ),
					'required'    => [
						[ 'type', '=', 'file' ],
						[ 'fileUploadStorage', '=', 'directory' ],
					],
				],

				'fileUploadStorageInfo'      => [
					'type'     => 'info',
					'content'  => esc_html__( 'Users could upload potentially malicious files through your form. To minimize this risk, please specify the "Allowed file formats" below.', 'bricks' ),
					'required' => [
						[ 'type', '=', 'file' ],
						[ 'fileUploadStorage', '!=', '' ],
					],
				],

				'fileUploadAllowedTypes'     => [
					'label'       => esc_html__( 'Allowed file formats', 'bricks' ),
					'placeholder' => 'pdf,jpg,...',
					'type'        => 'text',
					'required'    => [ 'type', '=', 'file' ],
				],

				'fileUploadTypography'       => [
					'tab'      => 'content',
					'label'    => esc_html__( 'Typography', 'bricks' ),
					'type'     => 'typography',
					'css'      => [
						[
							'property' => 'font',
							'selector' => '.choose-files',
						],
					],
					'required' => [ 'type', '=', 'file' ],
				],

				'fileUploadBackground'       => [
					'tab'      => 'content',
					'label'    => esc_html__( 'Background', 'bricks' ),
					'type'     => 'color',
					'css'      => [
						[
							'property' => 'background-color',
							'selector' => '.choose-files',
						],
					],
					'required' => [ 'type', '=', 'file' ],
				],

				'fileUploadBorder'           => [
					'tab'      => 'content',
					'label'    => esc_html__( 'Border', 'bricks' ),
					'type'     => 'border',
					'css'      => [
						[
							'property' => 'border',
							'selector' => '.choose-files',
						],
					],
					'required' => [ 'type', '=', 'file' ],
				],

				'width'                      => [
					'label'       => esc_html__( 'Width', 'bricks' ) . ' (%)',
					'type'        => 'number',
					'unit'        => '%',
					'min'         => 0,
					'max'         => 100,
					'placeholder' => 100,
					'css'         => [
						[
							'property' => 'width',
						],
					],
					'required'    => [
						[ 'type', '!=', 'hidden' ],
						// [ 'columns', '=', '' ],
					],
				],

				'height'                     => [
					'label'    => esc_html__( 'Height', 'bricks' ),
					'type'     => 'number',
					'units'    => true,
					'css'      => [
						[
							'property' => 'height',
						],
					],
					'required' => [ 'type', '=', [ 'textarea' ] ],
				],

				// Added 'resize' option to textarea (@since 1.11)
				'resize'                     => [
					'label'       => esc_html__( 'Resize', 'bricks' ),
					'type'        => 'select',
					'options'     => [
						'none'       => esc_html__( 'None', 'bricks' ),
						'vertical'   => esc_html__( 'Vertical', 'bricks' ),
						'horizontal' => esc_html__( 'Horizontal', 'bricks' ),
						'both'       => esc_html__( 'Both', 'bricks' ),
					],
					'css'         => [
						[
							'property' => 'resize',
							'selector' => 'textarea',
						],
					],
					'inline'      => true,
					'placeholder' => esc_html__( 'Vertical', 'bricks' ),
					'required'    => [ 'type', '=', [ 'textarea' ] ],
				],

				'time'                       => [
					'label'    => esc_html__( 'Enable time', 'bricks' ),
					'type'     => 'checkbox',
					'required' => [ 'type', '=', 'datepicker' ],
				],

				'l10n'                       => [
					'label'       => esc_html__( 'Language', 'bricks' ),
					'type'        => 'text',
					'inline'      => true,
					'description' => '<a href="https://github.com/flatpickr/flatpickr/tree/master/src/l10n" target="_blank">' . esc_html__( 'Language codes', 'bricks' ) . '</a> (de, es, fr, etc.)',
					'required'    => [ 'type', '=', [ 'datepicker' ] ],
				],

				'minTime'                    => [
					'label'       => esc_html__( 'Min. time', 'bricks' ),
					'type'        => 'text',
					'placeholder' => esc_html__( '09:00', 'bricks' ),
					'required'    => [ 'time', '!=', '' ],
				],

				'maxTime'                    => [
					'label'       => esc_html__( 'Max. time', 'bricks' ),
					'type'        => 'text',
					'placeholder' => esc_html__( '20:00', 'bricks' ),
					'required'    => [ 'time', '!=', '' ],
				],

				// Honeypot (@since 1.12.2)
				'isHoneypot'                 => [
					'label'       => esc_html__( 'Honeypot', 'bricks' ),
					'type'        => 'checkbox',
					'required'    => [
						'type',
						'=',
						[ 'email', 'text', 'textarea', 'tel', 'number', 'url', 'checkbox',  'select', 'radio', 'datepicker', 'password' ]
					],
					'description' => esc_html__( 'When enabled, this field acts as a spam trap. It will not be visible to users, but will capture any bots that fill it out.', 'bricks' ),
				],

				'required'                   => [
					'label'    => esc_html__( 'Required', 'bricks' ),
					'type'     => 'checkbox',
					'inline'   => true,
					'required' => [
						[ 'type', '!=', [ 'hidden', 'html' ] ],
						[ 'isHoneypot', '!=', true ], // Honeypot fields can not be required (@since 1.12.2)
					],
				],

				'options'                    => [
					'label'    => esc_html__( 'Options (one per line)', 'bricks' ),
					'type'     => 'textarea',
					'required' => [ 'type', '=', [ 'checkbox', 'select', 'radio', ] ],
				],

				'valueLabelOptions'          => [
					// translators: %s: key:value
					'label'    => sprintf( esc_html__( 'Set options as %s', 'bricks' ), 'value:label' ),
					'type'     => 'checkbox',
					'inline'   => true,
					'required' => [ 'type', '=', [ 'checkbox', 'select', 'radio', ] ],
				],

				'valueLabelInfo'             => [
					'content'  => esc_html__( 'Separate value & label by ":".', 'bricks' ) . ' ' . esc_html__( 'Example', 'bricks' ) . '<strong>: DE:Germany</strong>',
					'type'     => 'info',
					'required' => [
						[ 'type', '=', [ 'checkbox', 'select', 'radio', ] ],
						[ 'valueLabelOptions', '=', true ],
					],
				],

				'html'                       => [
					'label'        => 'HTML',
					'type'         => 'code',
					'mode'         => 'text/html',
					'hasVariables' => false,
					'description'  => sprintf(
						esc_html__( 'To add decorative text, but not user input. Runs through %s.', 'bricks' ),
						'<a href="https://developer.wordpress.org/reference/functions/wp_kses_post/" target="_blank">wp_kses_post</a>'
					),
					'required'     => [ 'type', '=', [ 'html' ] ],
				],
			],

			'default'       => [
				[
					'type'        => 'text',
					'label'       => esc_html__( 'Name', 'bricks' ),
					'placeholder' => esc_html__( 'Your Name', 'bricks' ),
					'id'          => Helpers::generate_random_id( false ),
				],
				[
					'type'        => 'email',
					'label'       => esc_html__( 'Email', 'bricks' ),
					'placeholder' => esc_html__( 'Your Email', 'bricks' ),
					'required'    => true,
					'id'          => Helpers::generate_random_id( false ),
				],
				[
					'type'        => 'textarea',
					'label'       => esc_html__( 'Message', 'bricks' ),
					'placeholder' => esc_html__( 'Your Message', 'bricks' ),
					'required'    => true,
					'id'          => Helpers::generate_random_id( false ),
				],
			],
		];

		// Remove 'passwordProtectionInfo' control if current template type is not 'password_protection' (@since 1.11.1)
		if ( Templates::get_template_type( get_the_ID() ) !== 'password_protection' ) {
			unset( $this->controls['fields']['fields']['passwordProtectionInfo'] );
		}

		$this->controls['requiredAsterisk'] = [
			'tab'   => 'content',
			'group' => 'fields',
			'label' => esc_html__( 'Show required asterisk', 'bricks' ),
			'type'  => 'checkbox',
		];

		$this->controls['showLabels'] = [
			'tab'   => 'content',
			'group' => 'fields',
			'label' => esc_html__( 'Show labels', 'bricks' ),
			'type'  => 'checkbox',
		];

		$this->controls['labelTypography'] = [
			'tab'      => 'content',
			'group'    => 'fields',
			'label'    => esc_html__( 'Label typography', 'bricks' ),
			'type'     => 'typography',
			'css'      => [
				[
					'property' => 'font',
					'selector' => 'label',
				],
				[
					'property' => 'font',
					'selector' => '.label',
				],
			],
			'required' => [ 'showLabels' ],
		];

		$this->controls['placeholderTypography'] = [
			'tab'   => 'content',
			'group' => 'fields',
			'label' => esc_html__( 'Placeholder typography', 'bricks' ),
			'type'  => 'typography',
			'css'   => [
				[
					'property' => 'font',
					'selector' => '::placeholder',
				],
				[
					'property' => 'font',
					'selector' => 'select',
				],
			],
		];

		// Disable form validation on input or blur (@since 1.12)
		$this->controls['disableFormValidationOn'] = [
			'tab'         => 'content',
			'group'       => 'fields',
			'label'       => esc_html__( 'Disable form validation', 'bricks' ),
			'placeholder' => esc_html__( 'Select', 'bricks' ),
			'type'        => 'select',
			'multiple'    => true,
			'options'     => [
				'input' => esc_html__( 'On input', 'bricks' ),
				'blur'  => esc_html__( 'On blur', 'bricks' ),
			],
			'description' => esc_html__( 'By default, form fields are validated on input, blur and submit.', 'bricks' ),
		];

		/**
		 * Grid columns
		 *
		 * NOTE: Not yet in use
		 */
		// $this->controls['columns'] = [
		// 'tab'         => 'content',
		// 'group'       => 'fields',
		// 'label'       => esc_html__( 'Columns', 'bricks' ),
		// 'type'        => 'number',
		// 'css'         => [
		// [
		// 'property' => 'grid-template-columns',
		// 'selector' => '',
		// 'value'    => 'repeat(%s, 1fr)',
		// ],
		// [
		// 'selector' => '',
		// 'property' => 'display',
		// 'value'    => 'grid',
		// ],
		// [
		// 'selector' => '.submit-button-wrapper',
		// 'property' => 'align-items',
		// 'value'    => 'flex-start',
		// ],
		// ],
		// 'placeholder' => 1,
		// ];

		// $this->controls['columnGap'] = [
		// 'tab'      => 'content',
		// 'group'    => 'fields',
		// 'label'    => esc_html__( 'Column gap', 'bricks' ),
		// 'type'     => 'number',
		// 'units'    => true,
		// 'css'      => [
		// [
		// 'property' => 'column-gap',
		// 'selector' => '',
		// ],
		// ],
		// 'required' => [ 'columns', '!=', '' ],
		// ];

		// $this->controls['rowGap'] = [
		// 'tab'      => 'content',
		// 'group'    => 'fields',
		// 'label'    => esc_html__( 'Row gap', 'bricks' ),
		// 'type'     => 'number',
		// 'units'    => true,
		// 'css'      => [
		// [
		// 'property' => 'row-gap',
		// 'selector' => '',
		// ],
		// [
		// 'selector' => '.form-group:not(:last-child)',
		// 'property' => 'padding',
		// 'value'    => '0',
		// ],
		// ],
		// 'required' => [ 'columns', '!=', '' ],
		// ];

		// Field

		$this->controls['fieldSeparator'] = [
			'tab'   => 'content',
			'group' => 'fields',
			'label' => esc_html__( 'Field', 'bricks' ),
			'type'  => 'separator',
		];

		$this->controls['fieldMargin'] = [
			'tab'   => 'content',
			'group' => 'fields',
			'label' => esc_html__( 'Spacing', 'bricks' ),
			'type'  => 'spacing',
			'css'   => [
				// Use padding (as margin results in line-breaks)
				[
					'property' => 'padding',
					'selector' => '.form-group:not(:last-child):not(.captcha)',
				],
			],
		];

		$this->controls['fieldPadding'] = [
			'tab'   => 'content',
			'group' => 'fields',
			'label' => esc_html__( 'Padding', 'bricks' ),
			'type'  => 'spacing',
			'css'   => [
				[
					'property' => 'padding',
					'selector' => '.form-group input',
				],
				[
					'property' => 'padding',
					'selector' => '.flatpickr',
				],
				[
					'property' => 'padding',
					'selector' => 'select',
				],
				[
					'property' => 'padding',
					'selector' => 'textarea',
				],
			],
		];

		$this->controls['horizontalAlignFields'] = [
			'tab'   => 'content',
			'group' => 'fields',
			'label' => esc_html__( 'Alignment', 'bricks' ),
			'type'  => 'justify-content',
			'css'   => [
				[
					'property' => 'justify-content',
				],
			],
		];

		$this->controls['fieldBackgroundColor'] = [
			'tab'   => 'content',
			'group' => 'fields',
			'label' => esc_html__( 'Background color', 'bricks' ),
			'type'  => 'color',
			'css'   => [
				[
					'property' => 'background-color',
					'selector' => '.form-group input',
				],
				[
					'property' => 'background-color',
					'selector' => '.flatpickr',
				],
				[
					'property' => 'background-color',
					'selector' => 'select',
				],
				[
					'property' => 'background-color',
					'selector' => 'textarea',
				],
			],
		];

		$this->controls['fieldBorder'] = [
			'tab'   => 'content',
			'group' => 'fields',
			'label' => esc_html__( 'Border', 'bricks' ),
			'type'  => 'border',
			'css'   => [
				[
					'property' => 'border',
					'selector' => '.form-group input',
				],
				[
					'property' => 'border',
					'selector' => '.flatpickr',
				],
				[
					'property' => 'border',
					'selector' => 'select',
				],
				[
					'property' => 'border',
					'selector' => 'textarea',
				],
				[
					'property' => 'border',
					'selector' => '.bricks-button:not([type=submit])',
				],
				[
					'property' => 'border',
					'selector' => '.choose-files',
				],
			],
		];

		$this->controls['fieldTypography'] = [
			'tab'   => 'content',
			'group' => 'fields',
			'label' => esc_html__( 'Typography', 'bricks' ),
			'type'  => 'typography',
			'css'   => [
				[
					'property' => 'font',
					'selector' => '.form-group input',
				],
				[
					'property' => 'font',
					'selector' => 'select',
				],
				[
					'property' => 'font',
					'selector' => 'textarea',
				],
			],
		];

		// Group: Submit Button

		$this->controls['submitButtonText'] = [
			'tab'         => 'content',
			'group'       => 'submitButton',
			'label'       => esc_html__( 'Text', 'bricks' ),
			'type'        => 'text',
			'inline'      => true,
			'placeholder' => esc_html__( 'Send', 'bricks' ),
		];

		$this->controls['submitButtonSize'] = [
			'tab'     => 'content',
			'group'   => 'submitButton',
			'label'   => esc_html__( 'Size', 'bricks' ),
			'type'    => 'select',
			'inline'  => true,
			'options' => $this->control_options['buttonSizes'],
		];

		$this->controls['submitButtonStyle'] = [
			'tab'         => 'content',
			'group'       => 'submitButton',
			'label'       => esc_html__( 'Style', 'bricks' ),
			'type'        => 'select',
			'inline'      => true,
			'options'     => $this->control_options['styles'],
			'default'     => 'primary',
			'placeholder' => esc_html__( 'Custom', 'bricks' ),
		];

		// $this->controls['submitButtonSpan'] = [
		// 'tab'      => 'content',
		// 'group'    => 'submitButton',
		// 'label'    => esc_html__( 'Span .. columns', 'bricks' ),
		// 'type'     => 'number',
		// 'css'      => [
		// [
		// 'property' => 'grid-column',
		// 'selector' => '.submit-button-wrapper',
		// 'value'    => 'span %s',
		// ],
		// ],
		// 'required' => [ 'columns', '!=', '' ],
		// ];

		$this->controls['submitButtonWidth'] = [
			'tab'   => 'content',
			'group' => 'submitButton',
			'label' => esc_html__( 'Width', 'bricks' ) . ' (%)',
			'type'  => 'number',
			'unit'  => '%',
			'css'   => [
				[
					'property' => 'width',
					'selector' => '.submit-button-wrapper',
				],
			],
			// 'required' => [ 'columns', '=', '' ],
		];

		$this->controls['submitButtonMargin'] = [
			'tab'   => 'content',
			'group' => 'submitButton',
			'label' => esc_html__( 'Margin', 'bricks' ),
			'type'  => 'spacing',
			'css'   => [
				[
					'property' => 'margin',
					'selector' => '.submit-button-wrapper',
				],
			],
		];

		$this->controls['submitButtonTypography'] = [
			'tab'   => 'content',
			'group' => 'submitButton',
			'label' => esc_html__( 'Typography', 'bricks' ),
			'type'  => 'typography',
			'css'   => [
				[
					'property' => 'font',
					'selector' => '.bricks-button',
				]
			],
		];

		$this->controls['submitButtonBackgroundColor'] = [
			'tab'   => 'content',
			'group' => 'submitButton',
			'label' => esc_html__( 'Background', 'bricks' ),
			'type'  => 'color',
			'css'   => [
				[
					'property' => 'background-color',
					'selector' => '.bricks-button',
				]
			],
		];

		$this->controls['submitButtonBorder'] = [
			'tab'   => 'content',
			'group' => 'submitButton',
			'label' => esc_html__( 'Border', 'bricks' ),
			'type'  => 'border',
			'css'   => [
				[
					'property' => 'border',
					'selector' => 'button[type=submit].bricks-button',
				],
			],
		];

		$this->controls['submitButtonIcon'] = [
			'tab'   => 'content',
			'group' => 'submitButton',
			'label' => esc_html__( 'Icon', 'bricks' ),
			'type'  => 'icon',
		];

		$this->controls['submitButtonIconPosition'] = [
			'tab'         => 'content',
			'group'       => 'submitButton',
			'label'       => esc_html__( 'Icon position', 'bricks' ),
			'type'        => 'select',
			'options'     => $this->control_options['iconPosition'],
			'inline'      => true,
			'placeholder' => esc_html__( 'Right', 'bricks' ),
			'required'    => [ 'submitButtonIcon', '!=', '' ],
		];

		// Group: Actions

		$this->controls['actions'] = [
			'tab'         => 'content',
			'group'       => 'actions',
			'type'        => 'select',
			'label'       => esc_html__( 'Actions after successful form submit', 'bricks' ),
			'placeholder' => esc_html__( 'None', 'bricks' ),
			'options'     => Integrations\Form\Init::get_available_actions(),
			'multiple'    => true,
			'description' => esc_html__( 'Select action(s) you want to perform after form has been successfully submitted.', 'bricks' ),
			'default'     => [ 'email' ],
		];

		$this->controls['info'] = [
			'tab'      => 'content',
			'group'    => 'actions',
			'content'  => esc_html__( 'You did not select any action(s). So when this form is submitted nothing happens.', 'bricks' ),
			'type'     => 'info',
			'required' => [ 'actions', '=', '' ],
		];

		$this->controls['successMessage'] = [
			'tab'     => 'content',
			'group'   => 'actions',
			'label'   => esc_html__( 'Success message', 'bricks' ),
			'type'    => 'text',
			'default' => esc_html__( 'Message successfully sent. We will get back to you as soon as possible.', 'bricks' ),
		];

		// Group Notices (@since 1.11.1)

		$this->controls['noticeCloseAfter'] = [
			'tab'   => 'content',
			'group' => 'notices',
			'label' => esc_html__( 'Close after', 'bricks' ) . ' (ms)',
			'type'  => 'number',
			'min'   => 1,
		];

		$this->controls['noticeCloseButton'] = [
			'tab'   => 'content',
			'group' => 'notices',
			'label' => esc_html__( 'Close button', 'bricks' ),
			'type'  => 'checkbox',
		];

		// Group: Email

		$this->controls['emailInfo'] = [
			'tab'     => 'content',
			'group'   => 'email',
			'type'    => 'info',
			'content' => esc_html__( 'Use any form field value via it\'s ID like this: {{form_field}}. Replace "form_field" with the actual field ID.', 'bricks' ),
		];

		$this->controls['emailSubject'] = [
			'tab'     => 'content',
			'group'   => 'email',
			'label'   => esc_html__( 'Subject', 'bricks' ),
			'type'    => 'text',
			'default' => 'Contact form request',
		];

		$this->controls['emailTo'] = [
			'tab'       => 'content',
			'group'     => 'email',
			'label'     => esc_html__( 'Send to email address', 'bricks' ),
			'type'      => 'select',
			'options'   => [
				// translators: %s: admin email
				'admin_email' => sprintf( '%s (' . get_option( 'admin_email' ) . ')', esc_html__( 'Admin email', 'bricks' ) ),
				'custom'      => esc_html__( 'Custom email address', 'bricks' ),
			],
			'default'   => 'admin_email',
			'clearable' => false,
		];

		$this->controls['emailToCustom'] = [
			'tab'         => 'content',
			'group'       => 'email',
			'label'       => esc_html__( 'Send to custom email address', 'bricks' ),
			'description' => esc_html__( 'Accepts multiple addresses separated by comma', 'bricks' ),
			'type'        => 'text',
			'required'    => [ 'emailTo', '=', 'custom' ],
		];

		$this->controls['emailBcc'] = [
			'tab'   => 'content',
			'group' => 'email',
			'label' => esc_html__( 'BCC email address', 'bricks' ),
			'type'  => 'text',
		];

		$this->controls['fromEmail'] = [
			'tab'   => 'content',
			'group' => 'email',
			'label' => esc_html__( 'From email address', 'bricks' ),
			'type'  => 'text',
		];

		$this->controls['fromName'] = [
			'tab'         => 'content',
			'group'       => 'email',
			'label'       => esc_html__( 'From name', 'bricks' ),
			'type'        => 'text',
			'description' => esc_html__( 'Default', 'bricks' ) . ': ' . esc_html__( 'Site title', 'bricks' ),
			'default'     => get_option( 'blogname' ),
		];

		$this->controls['replyToEmail'] = [
			'tab'         => 'content',
			'group'       => 'email',
			'label'       => esc_html__( 'Reply to email address', 'bricks' ),
			'type'        => 'text',
			'placeholder' => 'Name <reply@domain.com>',
			'description' => esc_html__( 'Comma-separated list of name and email address or email addresses only.' ) . ' ' . esc_html__( 'Default', 'bricks' ) . ': ' . esc_html__( 'Email address in submitted form', 'bricks' ),
		];

		$this->controls['emailContent'] = [
			'tab'         => 'content',
			'group'       => 'email',
			'label'       => esc_html__( 'Email content', 'bricks' ),
			'type'        => 'textarea',
			'description' => sprintf(
				'%s %s %s',
				esc_html__( 'Use field IDs to personalize your message.', 'bricks' ),
				esc_html( 'Type {{all_fields}} to output all the field labels and values of the submitted form.', 'bricks' ),
				Helpers::article_link( 'form-element/#email', esc_html__( 'Learn more', 'bricks' ) )
			),
		];

		$this->controls['emailErrorMessage'] = [
			'tab'     => 'content',
			'group'   => 'email',
			'label'   => esc_html__( 'Error message', 'bricks' ),
			'type'    => 'text',
			'default' => esc_html__( 'Submission failed. Please reload the page and try to submit the form again.', 'bricks' ),
		];

		$this->controls['htmlEmail'] = [
			'tab'     => 'content',
			'group'   => 'email',
			'label'   => esc_html__( 'HTML email', 'bricks' ),
			'type'    => 'checkbox',
			'default' => true,
		];

		// Group: Webhook (@since 2.0)
		$this->controls['webhooks'] = [
			'tab'           => 'content',
			'group'         => 'webhook',
			'type'          => 'repeater',
			'label'         => esc_html__( 'Endpoints', 'bricks' ),
			'placeholder'   => esc_html__( 'Endpoint', 'bricks' ),
			'desc'          => esc_html__( 'The webhook endpoint(s) to send the submitted form data to.', 'bricks' ),
			'titleProperty' => 'name',
			'fields'        => [
				'name'         => [
					'label' => esc_html__( 'Name', 'bricks' ),
					'type'  => 'text',
				],
				'url'          => [
					'label'       => esc_html__( 'Endpoint URL', 'bricks' ),
					'type'        => 'text',
					'description' => esc_html__( 'The URL to send the form data to.', 'bricks' ),
				],
				'contentType'  => [
					'label'       => esc_html__( 'Data format', 'bricks' ),
					'type'        => 'select',
					'options'     => [
						'json'      => 'JSON',
						'form-data' => esc_html__( 'Form data', 'bricks' ),
					],
					'default'     => 'json',
					'placeholder' => 'JSON',
					'description' => esc_html__( 'Format to send the data in.', 'bricks' ),
				],
				'dataTemplate' => [
					'label'          => esc_html__( 'Data', 'bricks' ),
					'type'           => 'code',
					'hasDynamicData' => true,
					'description'    => esc_html__( 'Customize how the data is structured. Leave empty to send all form fields.', 'bricks' ) . ' ' .
								esc_html__( 'Example: {"name": "{{43f295}}", "email": "{{a5c626}}"}', 'bricks' ),
				],
				'headers'      => [
					'label'          => esc_html__( 'Headers', 'bricks' ),
					'type'           => 'code',
					'hasDynamicData' => true,
					'description'    => esc_html__( 'Add custom headers in JSON format. Leave empty for default headers.', 'bricks' ) . ' ' .
								esc_html__( 'Example: {"Authorization": "Bearer token"}', 'bricks' ),
				],
			],
		];

		$this->controls['webhookMaxSize'] = [
			'tab'         => 'content',
			'group'       => 'webhook',
			'label'       => esc_html__( 'Max payload size', 'bricks' ) . ' (KB)',
			'type'        => 'number',
			'placeholder' => '1024', // = Default: 1 MB
			'min'         => 1,
			'description' => esc_html__( 'Maximum size of the webhook payload in kilobytes.', 'bricks' ) . ' (' .
								esc_html__( 'Default', 'bricks' ) . ': 1024)',
		];

		$this->controls['webhookRateLimit'] = [
			'tab'         => 'content',
			'group'       => 'webhook',
			'label'       => esc_html__( 'Rate limiting', 'bricks' ),
			'type'        => 'checkbox',
			'description' => esc_html__( 'Limit the number of webhook requests that can be sent per hour.', 'bricks' ),
		];

		$this->controls['webhookRateLimitRequests'] = [
			'tab'         => 'content',
			'group'       => 'webhook',
			'label'       => esc_html__( 'Max requests per hour', 'bricks' ),
			'type'        => 'number',
			'min'         => 1,
			'placeholder' => '60',
			'description' => esc_html__( 'Maximum number of webhook requests allowed per hour.', 'bricks' ) . ' (' . esc_html__( 'Default', 'bricks' ) . ': 60)',
			'required'    => [ 'webhookRateLimit', '=', true ],
		];

		$this->controls['webhookErrorIgnore'] = [
			'tab'         => 'content',
			'group'       => 'webhook',
			'label'       => esc_html__( 'Continue on error', 'bricks' ),
			'type'        => 'checkbox',
			'description' => esc_html__( 'If enabled, form submission will succeed even if the webhook fails. Errors will be logged to the server error log.', 'bricks' ),
		];

		$this->controls['webhookErrorMessage'] = [
			'tab'      => 'content',
			'group'    => 'webhook',
			'label'    => esc_html__( 'Error message', 'bricks' ),
			'type'     => 'text',
			'required' => [ 'webhookErrorIgnore', '=', false ],
		];

		// Group: Confirmation email (@since 1.7.2)

		$this->controls['confirmationEmailDescription'] = [
			'tab'     => 'content',
			'group'   => 'confirmation',
			'type'    => 'info',
			'content' => Helpers::article_link( 'form/#confirmation-email', esc_html__( 'Please ensure SMTP is set up on this site so all outgoing emails are delivered properly.', 'bricks' ) ),
		];

		$this->controls['confirmationEmailSubject'] = [
			'tab'   => 'content',
			'group' => 'confirmation',
			'label' => esc_html__( 'Subject', 'bricks' ),
			'type'  => 'text',
		];

		$this->controls['confirmationEmailTo'] = [
			'tab'         => 'content',
			'group'       => 'confirmation',
			'label'       => esc_html__( 'Send to email address', 'bricks' ),
			'type'        => 'text',
			'description' => esc_html__( 'Default', 'bricks' ) . ': ' . esc_html__( 'Email address in submitted form', 'bricks' ),
		];

		$this->controls['confirmationFromEmail'] = [
			'tab'         => 'content',
			'group'       => 'confirmation',
			'label'       => esc_html__( 'From email address', 'bricks' ),
			'type'        => 'text',
			'description' => esc_html__( 'Default', 'bricks' ) . ': ' . esc_html__( 'Admin email', 'bricks' ),
		];

		$this->controls['confirmationFromName'] = [
			'tab'         => 'content',
			'group'       => 'confirmation',
			'label'       => esc_html__( 'From name', 'bricks' ),
			'description' => esc_html__( 'Default', 'bricks' ) . ': ' . esc_html__( 'Site title', 'bricks' ),
			'type'        => 'text',
		];

		$this->controls['confirmationReplyToEmail'] = [
			'tab'         => 'content',
			'group'       => 'confirmation',
			'label'       => esc_html__( 'Reply to email address', 'bricks' ),
			'type'        => 'text',
			'placeholder' => 'Name <reply@domain.com>',
			'description' => esc_html__( 'Comma-separated list of name and email address or email addresses only.' ) . ' ' . esc_html__( 'Default', 'bricks' ) . ': ' . esc_html__( 'From email address', 'bricks' ),
		];

		$this->controls['confirmationEmailContent'] = [
			'tab'         => 'content',
			'group'       => 'confirmation',
			'label'       => esc_html__( 'Email content', 'bricks' ),
			'type'        => 'textarea',
			'description' => sprintf(
				'%s %s %s',
				esc_html__( 'Use field IDs to personalize your message.', 'bricks' ),
				esc_html( 'Type {{all_fields}} to output all the field labels and values of the submitted form.', 'bricks' ),
				Helpers::article_link( 'form-element/#email', esc_html__( 'Learn more', 'bricks' ) )
			),
		];

		$this->controls['confirmationEmailHTML'] = [
			'tab'   => 'content',
			'group' => 'confirmation',
			'label' => esc_html__( 'HTML email', 'bricks' ),
			'type'  => 'checkbox',
		];

		// Group: Redirect

		$this->controls['redirectInfo'] = [
			'tab'     => 'content',
			'group'   => 'redirect',
			'content' => esc_html__( 'Redirect is only triggered after successful form submit.', 'bricks' ),
			'type'    => 'info',
		];

		$this->controls['redirectAdminUrl'] = [
			'tab'         => 'content',
			'group'       => 'redirect',
			'label'       => esc_html__( 'Redirect to admin area', 'bricks' ),
			'type'        => 'checkbox',
			'placeholder' => admin_url(),
		];

		$this->controls['redirect'] = [
			'tab'         => 'content',
			'group'       => 'redirect',
			'label'       => esc_html__( 'Custom redirect URL', 'bricks' ),
			'type'        => 'text',
			'placeholder' => get_option( 'siteurl' ),
		];

		$this->controls['redirectTimeout'] = [
			'tab'   => 'content',
			'group' => 'redirect',
			'label' => esc_html__( 'Redirect after (ms)', 'bricks' ),
			'type'  => 'number',
		];

		// Group: Mailchimp (apiKeyMailchimp via global settings)

		$this->controls['mailchimpInfo'] = [
			'tab'      => 'content',
			'group'    => 'mailchimp',
			'content'  => sprintf(
				// translators: %s: Bricks settings URL
				esc_html__( 'Mailchimp API key required! Add key in dashboard under: %s', 'bricks' ),
				'<a href="' . Helpers::settings_url( '#tab-api-keys' ) . '" target="_blank">Bricks > ' . esc_html__( 'Settings', 'bricks' ) . ' > API keys</a>'
			),
			'type'     => 'info',
			'required' => [ 'apiKeyMailchimp', '=', '', 'globalSettings' ],
		];

		$this->controls['mailchimpDoubleOptIn'] = [
			'tab'      => 'content',
			'group'    => 'mailchimp',
			'label'    => esc_html__( 'Double opt-in', 'bricks' ),
			'type'     => 'checkbox',
			'required' => [ 'apiKeyMailchimp', '!=', '', 'globalSettings' ],
		];

		$mailchimp_list_options = [];

		foreach ( Integrations\Form\Actions\Mailchimp::get_list_options() as $list_id => $list ) {
			$mailchimp_list_options[ $list_id ] = $list['name'];
		}

		$this->controls['mailchimpList'] = [
			'tab'         => 'content',
			'group'       => 'mailchimp',
			'label'       => esc_html__( 'List', 'bricks' ),
			'placeholder' => esc_html__( 'Select', 'bricks' ),
			'type'        => 'select',
			'options'     => $mailchimp_list_options,
			'required'    => [ 'apiKeyMailchimp', '!=', '', 'globalSettings' ],
		];

		$this->controls['mailchimpGroups'] = [
			'tab'         => 'content',
			'group'       => 'mailchimp',
			'label'       => esc_html__( 'Groups', 'bricks' ),
			'placeholder' => esc_html__( 'Select', 'bricks' ),
			'type'        => 'select',
			'options'     => [], // Populate in builder via 'mailchimpList' (PanelControl.vue)
			'multiple'    => true,
			'required'    => [
				[ 'apiKeyMailchimp', '!=', '', 'globalSettings' ],
				[ 'mailchimpList', '!=', '' ],
			],
		];

		$this->controls['mailchimpEmail'] = [
			'tab'         => 'content',
			'group'       => 'mailchimp',
			'label'       => esc_html__( 'Field', 'bricks' ) . ': ' . esc_html__( 'Email', 'bricks' ),
			'placeholder' => esc_html__( 'Select', 'bricks' ),
			'type'        => 'select',
			'options'     => [], // Auto-populate with form fields
			'map_fields'  => true, // NOTE: Undocumented
			'required'    => [ 'apiKeyMailchimp', '!=', '', 'globalSettings' ],
		];

		$this->controls['mailchimpFirstName'] = [
			'tab'         => 'content',
			'group'       => 'mailchimp',
			'label'       => esc_html__( 'First name', 'bricks' ),
			'placeholder' => esc_html__( 'Select', 'bricks' ),
			'type'        => 'select',
			'options'     => [], // Auto-populate with form fields
			'map_fields'  => true,
			'required'    => [ 'apiKeyMailchimp', '!=', '', 'globalSettings' ],
		];

		$this->controls['mailchimpLastName'] = [
			'tab'         => 'content',
			'group'       => 'mailchimp',
			'label'       => esc_html__( 'Last name', 'bricks' ),
			'placeholder' => esc_html__( 'Select', 'bricks' ),
			'type'        => 'select',
			'options'     => [], // Auto-populate with form fields
			'map_fields'  => true,
			'required'    => [ 'apiKeyMailchimp', '!=', '', 'globalSettings' ],
		];

		$this->controls['mailchimpPendingMessage'] = [
			'tab'      => 'content',
			'group'    => 'mailchimp',
			'label'    => esc_html__( 'Pending message', 'bricks' ),
			'type'     => 'text',
			'required' => [ 'apiKeyMailchimp', '!=', '', 'globalSettings' ],
			'default'  => esc_html__( 'Please check your email to confirm your subscription.', 'bricks' ),
		];

		$this->controls['mailchimpErrorMessage'] = [
			'tab'      => 'content',
			'group'    => 'mailchimp',
			'label'    => esc_html__( 'Error message', 'bricks' ),
			'type'     => 'text',
			'required' => [ 'apiKeyMailchimp', '!=', '', 'globalSettings' ],
			'default'  => esc_html__( 'Sorry, but we could not subscribe you.', 'bricks' ),
		];

		// Group: Sendgrid (apiKeySendgrid via global settings)

		$this->controls['sendgridInfo'] = [
			'tab'      => 'content',
			'group'    => 'sendgrid',
			'content'  => sprintf(
				// translators: %s: Bricks settings URL
				esc_html__( 'Sendgrid API key required! Add key in dashboard under: %s', 'bricks' ),
				'<a href="' . Helpers::settings_url( '#tab-api-keys' ) . '" target="_blank">Bricks > ' . esc_html__( 'Settings', 'bricks' ) . ' > API keys</a>'
			),
			'type'     => 'info',
			'required' => [ 'apiKeySendgrid', '=', '', 'globalSettings' ],
		];

		$this->controls['sendgridList'] = [
			'tab'         => 'content',
			'group'       => 'sendgrid',
			'label'       => esc_html__( 'List', 'bricks' ),
			'placeholder' => esc_html__( 'Select', 'bricks' ),
			'type'        => 'select',
			'options'     => Integrations\Form\Actions\Sendgrid::get_list_options(),
			'required'    => [ 'apiKeySendgrid', '!=', '', 'globalSettings' ],
		];

		$this->controls['sendgridEmail'] = [
			'tab'         => 'content',
			'group'       => 'sendgrid',
			'label'       => esc_html__( 'Field', 'bricks' ) . ': ' . esc_html__( 'Email', 'bricks' ),
			'placeholder' => esc_html__( 'Select', 'bricks' ),
			'type'        => 'select',
			'options'     => [], // Auto-populate with form fields
			'map_fields'  => true, // NOTE: Undocumented
			'required'    => [ 'apiKeySendgrid', '!=', '', 'globalSettings' ],
		];

		$this->controls['sendgridFirstName'] = [
			'tab'         => 'content',
			'group'       => 'sendgrid',
			'label'       => esc_html__( 'Field', 'bricks' ) . ': ' . esc_html__( 'First name', 'bricks' ),
			'placeholder' => esc_html__( 'Select', 'bricks' ),
			'type'        => 'select',
			'options'     => [], // Auto-populate with form fields
			'map_fields'  => true,
			'required'    => [ 'apiKeySendgrid', '!=', '', 'globalSettings' ],
		];

		$this->controls['sendgridLastName'] = [
			'tab'         => 'content',
			'group'       => 'sendgrid',
			'label'       => esc_html__( 'Field', 'bricks' ) . ': ' . esc_html__( 'Last name', 'bricks' ),
			'placeholder' => esc_html__( 'Select', 'bricks' ),
			'type'        => 'select',
			'options'     => [], // Auto-populate with form fields
			'map_fields'  => true,
			'required'    => [ 'apiKeySendgrid', '!=', '', 'globalSettings' ],
		];

		// NOTE: Undocumented
		if ( defined( 'BRICKS_SENDGRID_DOUBLE_OPT_IN' ) && BRICKS_SENDGRID_DOUBLE_OPT_IN ) {
			$this->controls['sendgridPendingMessage'] = [
				'tab'      => 'content',
				'group'    => 'sendgrid',
				'label'    => esc_html__( 'Pending message', 'bricks' ),
				'type'     => 'text',
				'required' => [ 'apiKeySendgrid', '!=', '', 'globalSettings' ],
				'default'  => esc_html__( 'Please check your email to confirm your subscription.', 'bricks' ),
			];
		}

		$this->controls['sendgridErrorMessage'] = [
			'tab'      => 'content',
			'group'    => 'sendgrid',
			'label'    => esc_html__( 'Error message', 'bricks' ),
			'type'     => 'text',
			'required' => [ 'apiKeySendgrid', '!=', '', 'globalSettings' ],
			'default'  => esc_html__( 'Sorry, but we could not subscribe you.', 'bricks' ),
		];

		// Group: User Login

		$this->controls['loginName'] = [
			'tab'         => 'content',
			'group'       => 'login',
			'label'       => esc_html__( 'Field', 'bricks' ) . ': ' . esc_html__( 'Login', 'bricks' ),
			'placeholder' => esc_html__( 'Select', 'bricks' ),
			'type'        => 'select',
			'options'     => [], // Auto-populate with form fields
			'map_fields'  => true, // NOTE: Undocumented
		];

		$this->controls['loginPassword'] = [
			'tab'         => 'content',
			'group'       => 'login',
			'label'       => esc_html__( 'Field', 'bricks' ) . ': ' . esc_html__( 'Password', 'bricks' ),
			'placeholder' => esc_html__( 'Select', 'bricks' ),
			'type'        => 'select',
			'options'     => [], // Auto-populate with form fields
			'map_fields'  => true, // NOTE: Undocumented
		];

		$this->controls['loginRemember'] = [
			'tab'         => 'content',
			'group'       => 'login',
			'label'       => esc_html__( 'Field', 'bricks' ) . ': ' . esc_html__( 'Remember me', 'bricks' ),
			'placeholder' => esc_html__( 'Select', 'bricks' ),
			'type'        => 'select',
			'options'     => [], // Auto-populate with form fields
			'map_fields'  => true,
		];

		$this->controls['loginErrorMessage'] = [
			'tab'         => 'content',
			'group'       => 'login',
			'label'       => esc_html__( 'Error message', 'bricks' ),
			'description' => esc_html__( 'Enter a generic error message. Otherwise the reason why the login failed is displayed.', 'bricks' ),
			'type'        => 'text',
		];

		// Group: User Registration

		$this->controls['registrationEmail'] = [
			'tab'         => 'content',
			'group'       => 'registration',
			'label'       => esc_html__( 'Field', 'bricks' ) . ': ' . esc_html__( 'Email', 'bricks' ),
			'placeholder' => esc_html__( 'Select', 'bricks' ),
			'type'        => 'select',
			'options'     => [], // Auto-populate with form fields
			'map_fields'  => true, // NOTE: Undocumented
		];

		$this->controls['registrationPassword'] = [
			'tab'         => 'content',
			'group'       => 'registration',
			'label'       => esc_html__( 'Field', 'bricks' ) . ': ' . esc_html__( 'Password', 'bricks' ),
			'placeholder' => esc_html__( 'Select', 'bricks' ),
			'type'        => 'select',
			'options'     => [], // Auto-populate with form fields
			'map_fields'  => true, // NOTE: Undocumented
			'description' => esc_html__( 'Autogenerated if no password is required/submitted.', 'bricks' ),
		];

		$this->controls['registrationPasswordMinLength'] = [
			'tab'         => 'content',
			'group'       => 'registration',
			'label'       => esc_html__( 'Password min. length', 'bricks' ),
			'type'        => 'number',
			'placeholder' => 6,
		];

		$this->controls['registrationUserName'] = [
			'tab'         => 'content',
			'group'       => 'registration',
			'label'       => esc_html__( 'Field', 'bricks' ) . ': ' . esc_html__( 'User name', 'bricks' ),
			'type'        => 'select',
			'options'     => [], // Auto-populate with form fields
			'map_fields'  => true, // NOTE: Undocumented
			'placeholder' => esc_html__( 'Select', 'bricks' ),
			'description' => esc_html__( 'Auto-generated if form only requires email address for registration.', 'bricks' ),
		];

		$this->controls['registrationFirstName'] = [
			'tab'         => 'content',
			'group'       => 'registration',
			'label'       => esc_html__( 'Field', 'bricks' ) . ': ' . esc_html__( 'First name', 'bricks' ),
			'placeholder' => esc_html__( 'Select', 'bricks' ),
			'type'        => 'select',
			'options'     => [], // Auto-populate with form fields
			'map_fields'  => true,
		];

		$this->controls['registrationLastName'] = [
			'tab'         => 'content',
			'group'       => 'registration',
			'label'       => esc_html__( 'Field', 'bricks' ) . ': ' . esc_html__( 'Last name', 'bricks' ),
			'placeholder' => esc_html__( 'Select', 'bricks' ),
			'type'        => 'select',
			'options'     => [], // Auto-populate with form fields
			'map_fields'  => true,
		];

		// @since 1.10.2
		$role_names = wp_roles()->get_names();

		// Remove 'Administrator' & 'Super Admin' roles
		if ( isset( $role_names['administrator'] ) ) {
			unset( $role_names['administrator'] );
		}

		if ( isset( $role_names['super_admin'] ) ) {
			unset( $role_names['super_admin'] );
		}

		$default_user_role = get_option( 'default_role' );

		$this->controls['registrationRole'] = [
			'tab'         => 'content',
			'group'       => 'registration',
			'label'       => esc_html__( 'Role', 'bricks' ),
			'desc'        => esc_html__( 'Administrator role is not allowed as a security precaution.', 'bricks' ),
			'type'        => 'select',
			'options'     => $role_names,
			'placeholder' => $role_names[ $default_user_role ] ?? '',
		];

		$this->controls['registrationAutoLogin'] = [
			'tab'         => 'content',
			'group'       => 'registration',
			'label'       => esc_html__( 'Auto log in user', 'bricks' ),
			'type'        => 'checkbox',
			'description' => esc_html__( 'Log in user after successful registration. Tip: Set action "Redirect" to redirect user to the account/admin area.', 'bricks' ),
		];

		// Send WordPress notification (@since 1.12.2)
		$this->controls['registrationWPNotification'] = [
			'tab'         => 'content',
			'group'       => 'registration',
			'label'       => esc_html__( 'Send WordPress notification', 'bricks' ),
			'type'        => 'checkbox',
			'description' => sprintf(
				esc_html__( 'Trigger "register_new_user" action to send WordPress notification. %s', 'bricks' ),
				Helpers::article_link( 'form-element/#login-registration', esc_html__( 'Learn more', 'bricks' ) )
			),
		];

		// Group: Lost password

		$this->controls['lostPasswordEmailUsername'] = [
			'tab'         => 'content',
			'group'       => 'lostPassword',
			'label'       => esc_html__( 'Field', 'bricks' ) . ': ' . esc_html__( 'Email or username', 'bricks' ),
			'placeholder' => esc_html__( 'Select', 'bricks' ),
			'type'        => 'select',
			'options'     => [], // Auto-populate with form fields
			'map_fields'  => true, // NOTE: Undocumented
		];

		// Group: Reset password

		$this->controls['resetPasswordNew'] = [
			'tab'         => 'content',
			'group'       => 'resetPassword',
			'label'       => esc_html__( 'Field', 'bricks' ) . ': ' . esc_html__( 'Password', 'bricks' ),
			'placeholder' => esc_html__( 'Select', 'bricks' ),
			'type'        => 'select',
			'options'     => [], // Auto-populate with form fields
			'map_fields'  => true, // NOTE: Undocumented
		];

		// Group: Spam Protection

		$this->controls['recaptchaInfo'] = [
			'tab'      => 'content',
			'group'    => 'spam',
			'content'  => sprintf(
				// translators: %s: Bricks settings URL
				esc_html__( 'Google reCAPTCHA API key required! Add key in dashboard under: %s', 'bricks' ),
				'<a href="' . Helpers::settings_url( '#tab-api-keys' ) . '" target="_blank">Bricks > ' . esc_html__( 'Settings', 'bricks' ) . ' > ' . esc_html__( 'API keys', 'bricks' ) . '</a>'
			),
			'type'     => 'info',
			'required' => [ 'apiKeyGoogleRecaptcha', '=', '', 'globalSettings' ],
		];

		$this->controls['enableRecaptcha'] = [
			'tab'      => 'content',
			'group'    => 'spam',
			'label'    => 'reCAPTCHA (Google)',
			'type'     => 'checkbox',
			'required' => [ 'apiKeyGoogleRecaptcha', '!=', '', 'globalSettings' ],
		];

		// Turnstile (Cloudflare)
		$this->controls['turnstileInfo'] = [
			'tab'      => 'content',
			'group'    => 'spam',
			'content'  => sprintf(
				esc_html__( 'Cloudflare Turnstile API key required! Add key in dashboard under: %s', 'bricks' ),
				'<a href="' . Helpers::settings_url( '#tab-api-keys' ) . '" target="_blank">Bricks > ' . esc_html__( 'Settings', 'bricks' ) . ' > ' . '</a>'
			),
			'type'     => 'info',
			'required' => [ 'apiKeyTurnstile', '=', '', 'globalSettings' ],
		];

		$this->controls['enableTurnstile'] = [
			'tab'      => 'content',
			'group'    => 'spam',
			'label'    => 'Turnstile (Cloudflare)',
			'info'     => esc_html__( 'View on frontend', 'bricks' ),
			'type'     => 'checkbox',
			'required' => [ 'apiKeyTurnstile', '!=', '', 'globalSettings' ],
		];

		$this->controls['turnstileSize'] = [
			'tab'         => 'content',
			'group'       => 'spam',
			'label'       => 'Turnstile: ' . esc_html__( 'Size', 'bricks' ),
			'type'        => 'select',
			'inline'      => true,
			'options'     => [
				'normal'  => esc_html__( 'Normal', 'bricks' ),
				'compact' => esc_html__( 'Compact', 'bricks' ),
			],
			'placeholder' => esc_html__( 'Normal', 'bricks' ),
			'required'    => [ 'enableTurnstile', '=', true ],
		];

		$this->controls['turnstileTheme'] = [
			'tab'         => 'content',
			'group'       => 'spam',
			'label'       => 'Turnstile: ' . esc_html__( 'Theme', 'bricks' ),
			'type'        => 'select',
			'inline'      => true,
			'options'     => [
				'light' => esc_html__( 'Light', 'bricks' ),
				'dark'  => esc_html__( 'Dark', 'bricks' ),
			],
			'placeholder' => esc_html__( 'Auto', 'bricks' ),
			'required'    => [ 'enableTurnstile', '=', true ],
		];

		// hCaptcha
		$this->controls['hCaptchaInfo'] = [
			'tab'      => 'content',
			'group'    => 'spam',
			'content'  => sprintf(
				esc_html__( 'hCaptcha key required! Add key in dashboard under: %s', 'bricks' ),
				'<a href="' . Helpers::settings_url( '#tab-api-keys' ) . '" target="_blank">Bricks > ' . esc_html__( 'Settings', 'bricks' ) . ' > ' . esc_html__( 'API keys', 'bricks' ) . '</a>'
			),
			'type'     => 'info',
			'required' => [ 'apiKeyHCaptcha', '=', '', 'globalSettings' ],
		];

		$this->controls['enableHCaptcha'] = [
			'tab'         => 'content',
			'group'       => 'spam',
			'label'       => 'hCaptcha',
			'type'        => 'select',
			'inline'      => true,
			'info'        => esc_html__( 'View on frontend', 'bricks' ),
			'options'     => [
				'visible'   => esc_html__( 'Visible', 'bricks' ),
				'invisible' => esc_html__( 'Invisible', 'bricks' ),
			],
			'placeholder' => esc_html__( 'Disabled', 'bricks' ),
			'required'    => [ 'apiKeyHCaptcha', '!=', '', 'globalSettings' ],
		];

		$this->controls['hCaptchaSize'] = [
			'tab'         => 'content',
			'group'       => 'spam',
			'label'       => 'hCaptcha: ' . esc_html__( 'Size', 'bricks' ),
			'type'        => 'select',
			'inline'      => true,
			'options'     => [
				'normal'  => esc_html__( 'Normal', 'bricks' ),
				'compact' => esc_html__( 'Compact', 'bricks' ),
			],
			'placeholder' => esc_html__( 'Normal', 'bricks' ),
			'required'    => [ 'enableHCaptcha', '=', 'visible' ],
		];

		$this->controls['hCaptchaTheme'] = [
			'tab'         => 'content',
			'group'       => 'spam',
			'label'       => 'hCaptcha: ' . esc_html__( 'Theme', 'bricks' ),
			'type'        => 'select',
			'inline'      => true,
			'options'     => [
				'light' => esc_html__( 'Light', 'bricks' ),
				'dark'  => esc_html__( 'Dark', 'bricks' ),
			],
			'placeholder' => esc_html__( 'Light', 'bricks' ),
			'required'    => [ 'enableHCaptcha', '=', 'visible' ],
		];

		$this->controls['honeypotInfo'] = [
			'tab'     => 'content',
			'group'   => 'spam',
			'content' => esc_html__( 'Honeypot: Create form field(s) and enable the "Honeypot" checkbox. Those honeypot fields aren\'t visible to users, but add an extra layer of protection against spam submissions.', 'bricks' ),
			'type'    => 'info',
		];

		// Upload Button (remove "Text" control group)
		$this->controls['uploadButtonTypography'] = [
			'tab'        => 'content',
			'label'      => esc_html__( 'Files', 'bricks' ) . ' - ' . esc_html__( 'Typography', 'bricks' ),
			'type'       => 'typography',
			'css'        => [
				[
					'property' => 'font',
					'selector' => '.choose-files',
				],
			],
			'deprecated' => true, // Moved within repeater field (@since: 1.4)
		];

		$this->controls['uploadButtonBackgroundColor'] = [
			'tab'        => 'content',
			'label'      => esc_html__( 'Files', 'bricks' ) . ' - ' . esc_html__( 'Background', 'bricks' ),
			'type'       => 'color',
			'css'        => [
				[
					'property' => 'background-color',
					'selector' => '.choose-files',
				],
			],
			'deprecated' => true,
		];

		$this->controls['uploadButtonBorder'] = [
			'tab'        => 'content',
			'label'      => esc_html__( 'Files', 'bricks' ) . ' - ' . esc_html__( 'Border', 'bricks' ),
			'type'       => 'border',
			'css'        => [
				[
					'property' => 'border',
					'selector' => '.choose-files',
				],
			],
			'deprecated' => true,
		];

		// Save submission (@since 1.9.2)
		if ( \Bricks\Database::get_setting( 'saveFormSubmissions', false ) ) {
			$this->controls['submissionFormName'] = [
				'tab'            => 'content',
				'group'          => 'save-submission',
				'label'          => esc_html__( 'Form name', 'bricks' ),
				'type'           => 'text',
				'placeholder'    => esc_html__( 'Contact form', 'bricks' ),
				'description'    => sprintf(
					esc_html__( 'Descriptive name for viewing submissions on the "%s" page.', 'bricks' ),
					'<a href="' . admin_url( 'admin.php?page=bricks-form-submissions' ) . '" target="_blank">' . esc_html__( 'Form Submissions', 'bricks' ) . '</a>'
				),
				'hasDynamicData' => false,
			];

			$this->controls['submissionSaveIp'] = [
				'tab'   => 'content',
				'group' => 'save-submission',
				'label' => esc_html__( 'Save IP address', 'bricks' ),
				'type'  => 'checkbox',
			];

			$this->controls['submissionMaxEntriesSeparator'] = [
				'tab'   => 'content',
				'group' => 'save-submission',
				'type'  => 'separator',
				'label' => esc_html__( 'Max. entries', 'bricks' ),
			];

			// Maximum number of entries
			$this->controls['submissionMaxEntries'] = [
				'tab'         => 'content',
				'group'       => 'save-submission',
				'label'       => esc_html__( 'Max. entries', 'bricks' ),
				'type'        => 'number',
				'description' => esc_html__( 'Set maximum number of form submissions that you want to store in the database.', 'bricks' ),
			];

			$this->controls['submissionMaxEntriesErrorMessage'] = [
				'tab'         => 'content',
				'group'       => 'save-submission',
				'label'       => esc_html__( 'Error message', 'bricks' ),
				'type'        => 'text',
				'placeholder' => esc_html__( 'Maximum number of entries reached.', 'bricks' ),
			];

			$this->controls['submissionDupEntriesSeparator'] = [
				'tab'   => 'content',
				'group' => 'save-submission',
				'type'  => 'separator',
				'label' => esc_html__( 'Prevent duplicates', 'bricks' ),
			];

			$this->controls['submissionSaveIpInfo'] = [
				'tab'      => 'content',
				'group'    => 'save-submission',
				'type'     => 'info',
				'content'  => esc_html__( 'Use "ip" to prevent multiple entries from the same IP address.', 'bricks' ),
				'required' => [ 'submissionSaveIp', '!=', '' ],
			];

			// Prevent duplicate entries
			$this->controls['submissionDupEntries'] = [
				'tab'           => 'content',
				'group'         => 'save-submission',
				'label'         => esc_html__( 'Compare with', 'bricks' ) . ' (' . esc_html__( 'Field ID', 'bricks' ) . ')',
				'type'          => 'repeater',
				'titleProperty' => 'field_id',
				'fields'        => [
					'field_id' => [
						'type'           => 'text',
						'label'          => esc_html__( 'Field ID', 'bricks' ),
						'hasDynamicData' => false,
					],
				],
			];

			$this->controls['submissionDupEntriesErrorMessage'] = [
				'tab'         => 'content',
				'group'       => 'save-submission',
				'label'       => esc_html__( 'Error message', 'bricks' ),
				'type'        => 'text',
				'placeholder' => esc_html__( 'Duplicate entries not allowed.', 'bricks' ),
			];
		}

		// Password protection (@since 1.11.1)
		$this->controls['passwordProtectionPassword'] = [
			'tab'         => 'content',
			'group'       => 'unlock-password-protection',
			'label'       => esc_html__( 'Field', 'bricks' ) . ': ' . esc_html__( 'Password', 'bricks' ),
			'type'        => 'select',
			'options'     => [], // Auto-populate with form fields
			'map_fields'  => true, // NOTE: Undocumented
			'description' => esc_html__( 'If no form field is selected, the first password field in the form is used.', 'bricks' ),
		];

		$this->controls['passwordProtectionErrorMessage'] = [
			'tab'   => 'content',
			'group' => 'unlock-password-protection',
			'label' => esc_html__( 'Error message', 'bricks' ),
			'type'  => 'text',
		];
	}

	public function render() {
		$settings = $this->settings;
		$fields   = $settings['fields'] ?? [];

		if ( empty( $fields ) ) {
			return $this->render_element_placeholder(
				[
					'title' => esc_html__( 'No form field added.', 'bricks' ),
				]
			);
		}

		// Fields using <input type="X" />
		$input_types = [
			'email',
			'number',
			'text',
			'tel',
			'url',
			'datepicker',
			'password',
			'file',
			'hidden',
		];

		$this->set_attribute( '_root', 'method', 'post' );

		// Add notice data (@since 1.11.1)
		$notice_data = [];

		if ( isset( $settings['noticeCloseAfter'] ) ) {
			$notice_data['closeAfter'] = $settings['noticeCloseAfter'] ?? 3000;
		}

		if ( isset( $settings['noticeCloseButton'] ) ) {
			$notice_data['closeButton'] = $settings['noticeCloseButton'];
		}

		// Only add notice data if any notice settings are set
		if ( ! empty( $notice_data ) ) {
			$this->set_attribute( '_root', 'data-notice', wp_json_encode( $notice_data ) );
		}

		// @since 1.12: Add form error message trigger, if set (as JSON)
		if ( isset( $settings['disableFormValidationOn'] ) ) {
			$this->set_attribute( '_root', 'data-validation-disabled-on', wp_json_encode( $settings['disableFormValidationOn'] ) );
		}

		// Use form element ID to get element settings in form submit logic
		$this->set_attribute( '_root', 'data-element-id', $this->id );

		// Form inside loop: Store the loop object ID, so we can use it in the form submit logic (@since 1.11)

		// NOTE: Will be 0, if we are in a popup AJAX call
		$loop_id = Query::get_loop_object_id();
		if ( $loop_id ) {
			$this->set_attribute( '_root', 'data-loop-object-id', $loop_id );
		}

		// If it's REST call, and loop ID is not set (we are in popup), we try to set it to "post ID"
		// This is needed for the form submit logic to work correctly (@since 2.0)
		elseif ( bricks_is_rest_call() ) {
			// If this is a REST call, we need to set the loop object ID to 0
			$this->set_attribute( '_root', 'data-loop-object-id', get_the_ID() );
		}

		// Use form global element ID to store as form_id (@since 1.9.2)
		$global_element_id = Helpers::get_global_element( $this->element, 'global' );
		if ( $global_element_id ) {
			$this->set_attribute( '_root', 'data-global-id', $global_element_id );
		}

		$this->set_attribute( 'enctype', 'method', 'multipart/form-data' );

		// Check if this form is for password protection
		$is_password_protection_form = false;

		if ( isset( $settings['actions'] ) && in_array( 'unlock-password-protection', $settings['actions'], true ) ) {
			$password_protection_template_id = \Bricks\Database::$active_templates['password_protection'] ?? 0;

			if ( isset( $password_protection_template_id ) ) {
				$is_password_protection_form = Password_Protection::verify_form_in_template( $this->id, $password_protection_template_id );
			}

			// Check if this post is password protected by WordPress
			if ( post_password_required() ) {
				$this->set_attribute( '_root', 'action', wp_login_url() . '?action=postpass' );

				// Change password field name to what WP expects
				foreach ( $fields as &$field ) {
					if ( $field['type'] === 'password' ) {
						$field['name'] = 'post_password';
						break;
					}
				}
			}

		}

		// If this is the password protection form, add the hidden input
		if ( $is_password_protection_form ) {
			$this->set_attribute( 'password_protection_template_id', 'type', 'hidden' );
			$this->set_attribute( 'password_protection_template_id', 'name', 'brx_pp_temp_id' );
			$this->set_attribute( 'password_protection_template_id', 'value', $password_protection_template_id );
		}

		// Append suffix for unique label HTML attributes inside a loop (@since 1.8)
		$field_suffix = Query::is_any_looping() ? '-' . Query::is_any_looping() . '-' . Query::get_loop_index() : '';

		// Generate unique ID for each field (@since 1.12.2)
		// We need to generate them before main loop below, so we can use it for Honeypot style generation
		$fields = array_map(
			function( $field ) use ( $field_suffix ) {
				$field['unique_id'] = Helpers::generate_random_id( false ) . $field_suffix;
				return $field;
			},
			$fields
		);

		// Output inline honeypot styles above the form (@since 1.12.2)
		$honeypot_css = $this->generate_honeypot_field_styles( $fields );
		if ( ! empty( $honeypot_css ) ) {
			echo '<style>' . $honeypot_css . '</style>';
		}

		foreach ( $fields as $index => $field ) {
			// Field ID generated when rendering form repeater in builder panel
			$field_id = isset( $field['id'] ) ? $field['id'] : '';

			// Get a unique field ID to avoid conflicts when the form is inside a query loop or it was duplicated
			// Generating outside this loop (@since 1.12.2)
			$input_unique_id = $field['unique_id'];

			// Field wrapper
			if ( $field['type'] !== 'hidden' ) {
				$this->set_attribute( "field-wrapper-$index", 'class', [ 'form-group', $field['type'] === 'file' ? 'file' : '' ] );
			}

			// Honeypot field: Set attributes (@since 1.12.2)
			if ( isset( $field['isHoneypot'] ) ) {

				// Set autocomplete attribute to "off" #86c368e99 (@since 2.0)
				// Note: "nope" was used @pre 2.0, but was causing accessibility issues
				$field['autocomplete'] = 'off';

				// If the field is "select", we need to add "autocomplete" attribute even here
				if ( $field['type'] === 'select' ) {
					$this->set_attribute( "field-$index", 'autocomplete', $field['autocomplete'] );
				}

				// Set value to empty
				$field['value'] = '';

				// Remove "required" attribute
				unset( $field['required'] );

				// Set "tabindex" to -1 to prevent focus
				$this->set_attribute( "field-$index", 'tabindex', '-1' );

			}

			// Field label
			if ( $field['type'] !== 'checkbox' && $field['type'] !== 'radio' ) {
				$this->set_attribute( "label-$index", 'for', "form-field-{$input_unique_id}" );
			}

			if ( $field['type'] === 'file' ) {
				if ( ! isset( $field['fileUploadLimit'] ) || $field['fileUploadLimit'] > 1 ) {
					$this->set_attribute( "field-$index", 'multiple' );
				}

				if ( ! empty( $field['fileUploadLimit'] ) ) {
					$this->set_attribute( "field-$index", 'data-limit', $field['fileUploadLimit'] );
				}

				if ( isset( $field['fileUploadAllowedTypes'] ) ) {
					$types = str_replace( '.', '', strtolower( $field['fileUploadAllowedTypes'] ) );
					$types = array_map( 'trim', explode( ',', $types ) );

					if ( in_array( 'jpg', $types ) && ! in_array( 'jpeg', $types ) ) {
						$types[] = 'jpeg';
					}

					array_walk(
						$types,
						function( &$value ) {
							$value = '.' . $value;
						}
					);

					$this->set_attribute( "field-$index", 'accept', implode( ',', $types ) );
				}

				if ( ! empty( $field['fileUploadSize'] ) ) {
					$this->set_attribute( "field-$index", 'data-maxsize', $field['fileUploadSize'] );
				}

				// Link the input file to the file preview using a unique ID (the field ID could be duplicated)
				$this->set_attribute( "field-$index", 'data-files-ref', $input_unique_id );

				$this->set_attribute( "file-preview-$index", 'data-files-ref', $input_unique_id );
			}

			if ( isset( $settings['requiredAsterisk'] ) && isset( $field['required'] ) ) {
				$this->set_attribute( "label-$index", 'class', 'required' );
			}

			// Datepicker
			if ( $field['type'] === 'datepicker' ) {
				$this->set_attribute( "field-$index", 'class', 'flatpickr' );

				$time_24h = get_option( 'time_format' );
				$time_24h = strpos( $time_24h, 'H' ) !== false || strpos( $time_24h, 'G' ) !== false;

				$date_format = isset( $field['time'] ) ? get_option( 'date_format' ) . ' H:i' : get_option( 'date_format' );

				$datepicker_options = [
					// 'allowInput' => true,
					'enableTime' => isset( $field['time'] ),
					'minTime'    => isset( $field['minTime'] ) ? $field['minTime'] : '',
					'maxTime'    => isset( $field['maxTime'] ) ? $field['maxTime'] : '',
					'altInput'   => false, // Set to false to avoid unnecessary hidden input field (@since 1.10.2)
					'altFormat'  => $date_format,
					'dateFormat' => $date_format,
					'time_24hr'  => $time_24h,
					// 'today' => date( get_option('date_format') ),
					// 'minDate' => 'today',
					// 'maxDate' => 'January 01, 2020',
				];

				// Localization: https://flatpickr.js.org/localization/ (@since 1.8.6)
				if ( ! empty( $field['l10n'] ) ) {
					$datepicker_options['locale'] = $field['l10n'];
				}

				// @see: https://academy.bricksbuilder.io/article/form-element/#datepicker
				$datepicker_options = apply_filters( 'bricks/element/form/datepicker_options', $datepicker_options, $this );

				$this->set_attribute( "field-$index", 'data-bricks-datepicker-options', wp_json_encode( $datepicker_options ) );
			}

			// Number min/max
			if ( $field['type'] === 'number' ) {
				if ( isset( $field['min'] ) ) {
					$this->set_attribute( "field-$index", 'min', $field['min'] );
				}

				if ( isset( $field['max'] ) ) {
					$this->set_attribute( "field-$index", 'max', $field['max'] );
				}

				// Set 'step' attribute value (@since 2.0)
				$step = isset( $field['step'] ) ? $field['step'] : null;

				if ( is_numeric( $step ) && $step > 0 ) {
					$this->set_attribute( "field-$index", 'step', $step );
				}
			}

			$this->set_attribute( "field-$index", 'id', "form-field-{$input_unique_id}" );

			// Set 'name' attribute value (@since 1.9.2)
			$field_name = isset( $field['name'] ) ? $field['name'] : "form-field-{$field_id}";
			$this->set_attribute( "field-$index", 'name', esc_attr( $field_name ) );

			// Add custom error message attributes (@since 1.9.2)
			if ( ! empty( $field['errorMessage'] ) ) {
				$error_message = esc_attr( $field['errorMessage'] );

				// Add error message attribute if field has validation rules
				if ( isset( $field['required'] ) || isset( $field['min'] ) || isset( $field['max'] ) || $field['type'] === 'email' || $field['type'] === 'url' ) {
					$this->set_attribute( "field-$index", 'data-error-message', $error_message );
				}
			}

			if ( ! empty( $field['label'] ) && ! isset( $settings['showLabels'] ) && $field['type'] != 'hidden' ) {
				$this->set_attribute( "field-$index", 'aria-label', $field['label'] );
			}

			// Text default (@since 1.9.9)
			if ( $field['type'] === 'text' && ! isset( $field['spellcheck'] ) ) {
				$this->set_attribute( "field-$index", 'spellcheck', 'false' );
			}

			// Textarea default (@since 1.9.9)
			if ( $field['type'] === 'textarea' ) {
				if ( ! empty( $field['autocomplete'] ) ) {
					$this->set_attribute( "field-$index", 'autocomplete', esc_attr( $field['autocomplete'] ) );
				}

				$this->set_attribute( "field-$index", 'spellcheck', ! empty( $field['spellcheck'] ) ? esc_attr( $field['spellcheck'] ) : 'false' );
			}

			// Input types type & value
			if ( in_array( $field['type'], $input_types ) ) {
				$field_type = $field['type'] == 'datepicker' ? 'text' : $field['type'];

				$this->set_attribute( "field-$index", 'type', $field_type );

				// Attribute: autocomplete (@since 1.9.9)
				if ( ! empty( $field['autocomplete'] ) ) {
					$this->set_attribute( "field-$index", 'autocomplete', esc_attr( $field['autocomplete'] ) );
				}

				// Attribute: spellcheck (@since 1.9.9)
				if ( ! empty( $field['spellcheck'] ) ) {
					$this->set_attribute( "field-$index", 'spellcheck', esc_attr( $field['spellcheck'] ) );
				}

				/**
				 * Set 'value' attribute (if field type is not file)
				 *
				 * Also render dynamic data tags in builder.
				 *
				 * @since 1.9.2
				 */
				$attr_value = isset( $field['value'] ) && $field['type'] !== 'file' ? $this->render_dynamic_data( $field['value'] ) : '';
				$this->set_attribute( "field-$index", 'value', $attr_value );
			}

			$placeholder_support = [
				'email',
				'number',
				'text',
				'tel',
				'url',
				'datepicker',
				'password',
				'textarea'
			];

			// Placeholder
			if ( in_array( $field['type'], $placeholder_support ) ) {
				if ( isset( $field['placeholder'] ) ) {
					if ( isset( $settings['requiredAsterisk'] ) && isset( $field['required'] ) ) {
						$field['placeholder'] = $field['placeholder'] . ' *';
					}

					$this->set_attribute( "field-$index", 'placeholder', $field['placeholder'] );
				}
			}

			// Min/max. length support (same as placeholder, without datepicker)
			$min_max_length_support = array_diff( $placeholder_support, [ 'datepicker' ] );

			if ( in_array( $field['type'], $min_max_length_support ) ) {
				$min_length = $field['minLength'] ?? false;
				$max_length = $field['maxLength'] ?? false;

				// Ensure min_length is a positive integer
				if ( ! is_numeric( $min_length ) || $min_length < 0 ) {
					$min_length = false;
				}

				// Ensure max_length is a positive integer
				if ( ! is_numeric( $max_length ) || $max_length < 0 ) {
					$max_length = false;
				}

				// Email: Limit to 320 characters by default (RCF 3696 - https://datatracker.ietf.org/doc/html/rfc3696.html)
				if ( $max_length === false && $field['type'] === 'email' ) {
					$max_length = 320;
				}

				if ( $max_length !== false ) {
					$this->set_attribute( "field-$index", 'maxlength', $max_length );
				}

				// @since 2.0
				if ( $min_length !== false ) {
					$this->set_attribute( "field-$index", 'minlength', $min_length );
				}
			}

			if ( isset( $field['required'] ) ) {
				$this->set_attribute( "field-$index", 'required' );
			}
		}

		// Submit button
		$submit_button_icon_position = ! empty( $settings['submitButtonIconPosition'] ) ? $settings['submitButtonIconPosition'] : 'right';

		$this->set_attribute( 'submit-wrapper', 'class', [ 'form-group', 'submit-button-wrapper' ] );

		$submit_button_classes[] = 'bricks-button';

		if ( ! empty( $settings['submitButtonStyle'] ) ) {
			$submit_button_classes[] = "bricks-background-{$settings['submitButtonStyle']}";
		}

		if ( ! empty( $settings['submitButtonSize'] ) ) {
			$submit_button_classes[] = $settings['submitButtonSize'];
		}

		if ( isset( $settings['submitButtonCircle'] ) ) {
			$submit_button_classes[] = 'circle';
		}

		if ( ! empty( $settings['submitButtonIcon'] ) ) {
			$submit_button_classes[] = "icon-$submit_button_icon_position";
		}

		$this->set_attribute( 'submit-button', 'class', $submit_button_classes );

		/**
		 * Render
		 */
		?>
		<form <?php echo $this->render_attributes( '_root' ); ?>>
			<?php

			// If this is the password protection form, render the hidden input
			if ( $is_password_protection_form ) {
					echo '<input ' . $this->render_attributes( 'password_protection_template_id' ) . '>';
			}

			foreach ( $fields as $index => $field ) {
				$field_value = isset( $field['value'] ) ? $this->render_dynamic_data( $field['value'] ) : ''; // @since 1.9.3

				// Using field's unique_id (@since 1.12.2)
				$checkbox_radio_unique_id = $field['unique_id'];

				// Set the role and aria-labelledby attributes for the options wrapper (@since 1.9.6)
				$this->set_attribute( "field-wrapper-$index", 'role', $field['type'] === 'radio' ? 'radiogroup' : 'group' );

				/**
				 * Group label ID for aria-labelledby
				 *
				 * @since 1.9.6
				 * @since 1.9.9: Only needed for checkbox and radio as the label is a <div> element.
				 * @since 1.12: Changed label to unique ID
				 */
				if ( ( $field['type'] === 'checkbox' || $field['type'] === 'radio' ) && ! empty( $field['label'] ) ) {
					$this->set_attribute( "field-wrapper-$index", 'aria-labelledby', "label-{$checkbox_radio_unique_id}" );
				}
				?>

				<div <?php echo $this->render_attributes( "field-wrapper-$index" ); ?>>
				<?php
				// Standard field label
				if ( isset( $settings['showLabels'] ) && ! empty( $field['label'] ) && $field['type'] !== 'checkbox' && $field['type'] !== 'radio' && $field['type'] !== 'hidden' ) {
					echo "<label {$this->render_attributes( "label-$index" )}>{$field['label']}</label>";
				}

				// Group label for checkbox or radio input using a <div> instead of <label> (@since 1.9.6)
				elseif ( isset( $settings['showLabels'] ) && ! empty( $field['label'] ) && in_array( $field['type'], [ 'checkbox', 'radio' ] ) ) {
					// Changed label to unique ID so it's unique if we duplicate the form (@since 1.12)
					echo "<div class=\"label\" id=\"label-{$checkbox_radio_unique_id}\">{$field['label']}</div>";
				}

				/**
				 * Field type rememberme
				 *
				 * Set to render checkbox + "Remember me" label
				 *
				 * @since 1.9.2
				 */
				if ( $field['type'] === 'rememberme' ) {
					$field['type']    = 'checkbox';
					$field['options'] = ! empty( $field['placeholder'] ) ? $field['placeholder'] : esc_html__( 'Remember me', 'bricks' );
				}

				/**
				 * Field type: html (meant for decorative text, not for user input)
				 *
				 * @since 1.9.2
				 */
				if ( $field['type'] === 'html' ) {
					if ( ! empty( $field['html'] ) ) {
						echo wp_kses_post( $field['html'] );
					}
				}

				if ( in_array( $field['type'], $input_types, true ) ) {
					if ( $field['type'] === 'password' && ! empty( $field['passwordToggle'] ) ) { // @since 1.12
						echo '<div class="password-input-wrapper">';
					}

					echo '<input ' . $this->render_attributes( "field-$index" ) . '>';

					// Add password toggle button if enabled and this is a password field (@since 1.12)
					if ( $field['type'] === 'password' && ! empty( $field['passwordToggle'] ) ) {
						echo '<button type="button" class="password-toggle" aria-label="' . esc_attr__( 'Show password', 'bricks' ) . '">';

						if ( ! empty( $field['passwordShowIcon'] ) ) {
							echo '<span class="show-password">';
							echo self::render_icon( $field['passwordShowIcon'] );
							echo '</span>';
						}

						if ( ! empty( $field['passwordHideIcon'] ) ) {
							echo '<span class="hide-password hide">';
							echo self::render_icon( $field['passwordHideIcon'] );
							echo '</span>';
						}

						echo '</button>';
						echo '</div>'; // Close password-input-wrapper
					}
				}

				if ( $field['type'] == 'file' ) {
					$label     = $field['fileUploadButtonText'] ?? esc_html__( 'Choose files', 'bricks' );
					$close_svg = Helpers::file_get_contents( BRICKS_PATH_ASSETS . 'svg/frontend/close.svg' );

					$this->set_attribute( "file-preview-$index", 'class', 'file-result' );
					$this->set_attribute( "file-preview-$index", 'data-error-limit', esc_html__( 'File %s not accepted. File limit exceeded.', 'bricks' ) ); // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
					$this->set_attribute( "file-preview-$index", 'data-error-size', esc_html__( 'File %s not accepted. Size limit exceeded.', 'bricks' ) ); // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment

					$this->set_attribute( "label-$index", 'class', 'choose-files' );
					?>
				<div <?php echo $this->render_attributes( "file-preview-$index" ); ?>>
					<span class="text"></span>
					<button type="button" class="bricks-button remove"><?php esc_html_e( 'Remove', 'bricks' ); ?></button>
					<?php echo $close_svg; ?>
				</div>

				<label <?php echo $this->render_attributes( "label-$index" ); ?>><?php echo $label; ?></label>
				<?php } ?>

				<?php if ( $field['type'] === 'textarea' ) { ?>
				<textarea <?php echo $this->render_attributes( "field-$index" ); ?>><?php echo esc_textarea( $field_value ); ?></textarea>
				<?php } ?>

				<?php if ( $field['type'] === 'select' && ! empty( $field['options'] ) ) { ?>
				<select <?php echo $this->render_attributes( "field-$index" ); ?>>
					<?php
					$select_options     = Helpers::parse_textarea_options( $field['options'] );
					$select_placeholder = false;

					if ( isset( $field['placeholder'] ) ) {
						$select_placeholder = $field['placeholder'];

						if ( isset( $settings['requiredAsterisk'] ) && isset( $field['required'] ) ) {
							$select_placeholder .= ' *';
						}

						echo '<option value="" class="placeholder">' . $select_placeholder . '</option>';
					}
					?>
					<?php
					foreach ( $select_options as $select_option ) {
						$field_key   = trim( $select_option );
						$field_label = trim( $select_option );

						if ( isset( $field['valueLabelOptions'] ) ) {
							$parts = $this->split_text( $select_option );

							$field_key   = $parts['key'];
							$field_label = $parts['value'];
						}
						?>

					<option value="<?php echo esc_attr( strip_tags( $field_key ) ); ?>" <?php selected( $field_value ?? '', $field_key ); ?>><?php echo strip_tags( $field_label ); ?></option>
					<?php } ?>
				</select>
				<?php } ?>

				<?php
				if ( ( $field['type'] === 'checkbox' || $field['type'] === 'radio' ) && ! empty( $field['options'] ) ) {
					$checked_values = array_map( 'trim', explode( ',', $field_value ?? '' ) );
					?>
				<ul class="options-wrapper" <?php echo $this->render_attributes( "options-wrapper-$index" ); ?>>
					<?php $options = Helpers::parse_textarea_options( $field['options'] ); ?>
					<?php
					foreach ( $options as $key => $value ) {
						$field_key   = trim( $value );
						$field_label = trim( $value );

						if ( isset( $field['valueLabelOptions'] ) ) {
							$parts = $this->split_text( $value );

							$field_key   = $parts['key'];
							$field_label = $parts['value'];
						}

						// Get field name
						$field_name = "form-field-{$field['id']}";

						// Use custom field name
						if ( isset( $field['name'] ) ) {
							$field_name = $field['name'];
						}

						// Append [] to the field name to make it an array
						// Even if it's a single value like a radio button (for backwards compatibility)
						$field_name .= '[]';
						?>
					<li>
						<input
							type="<?php echo esc_attr( $field['type'] ); ?>"
							id="<?php echo esc_attr( "form-field-{$checkbox_radio_unique_id}" ) . '-' . $key . $field_suffix; ?>"
							name="<?php echo $field_name; ?>"
							<?php
							if ( isset( $field['required'] ) ) {
								echo esc_attr( 'required ' );
							}

							// Is "Honeypot" field: Add "tabindex" to -1 to prevent focus (@since 1.12.2)
							if ( isset( $field['isHoneypot'] ) ) {
								echo 'tabindex="-1"';
							}

							if ( $field['type'] === 'checkbox' && is_array( $checked_values ) && in_array( $field_key, $checked_values, true ) ) {
								echo esc_attr( 'checked' );
							}

							if ( $field['type'] === 'radio' && $field_key === $field_value ) {
								echo esc_attr( 'checked' );
							}
							?>
							value="<?php echo esc_attr( strip_tags( $field_key ) ); ?>">
							<label for="<?php echo esc_attr( "form-field-{$checkbox_radio_unique_id}" ) . '-' . $key . $field_suffix; ?>"><?php echo $field_label; ?></label>
					</li>
					<?php } ?>
				</ul>
				<?php } ?>
			</div>
				<?php
			}

			/**
			 * STEP: Check if this is a reset password form & add hidden fields from URL query
			 *
			 * @since 1.10.2: Render hidden fields last to prevent nth-child CSS issues
			 */
			if ( isset( $settings['resetPasswordNew'], $settings['actions'] ) &&
				is_array( $settings['actions'] ) &&
				in_array( 'reset-password', $settings['actions'] )
			) {
				?>
				<input type="hidden" name="form-field-key" value="<?php echo esc_attr( $_GET['key'] ?? '' ); ?>">
				<input type="hidden" name="form-field-login" value="<?php echo esc_attr( $_GET['login'] ?? '' ); ?>">
				<?php
			}

			/**
			 * STEP: Check if 'redirect_to' is present in the URL and add it as a hidden field
			 *
			 * @since 1.9.4
			 * @since 1.11: Make sure there's a login action in the form settings
			 */
			if ( isset( $settings['actions'] ) && is_array( $settings['actions'] ) && in_array( 'login', $settings['actions'] ) && isset( $_GET['redirect_to'] ) ) {
				$redirect_to = esc_url_raw( $_GET['redirect_to'] );

				// Add hidden field for 'redirect_to'
				echo '<input type="hidden" name="form-field-redirect_to" value="' . esc_attr( $redirect_to ) . '">';
			}

			// Submit button icon
			$submit_button_icon = isset( $settings['submitButtonIcon'] ) ? self::render_icon( $settings['submitButtonIcon'] ) : '';

			// Reload SVG
			$loading_svg = Helpers::file_get_contents( BRICKS_PATH_ASSETS . 'svg/frontend/reload.svg' );

			// Add loading SVG to submit button
			if ( $loading_svg ) {
				$submit_button_icon .= '<span class="loading">' . $loading_svg . '</span>';
			}

			// Add reCAPTCHA (Google) & hCaptcha HTML
			$captcha_html  = $this->generate_recaptcha_html();
			$captcha_html .= $this->generate_hcaptcha_html();
			$captcha_html .= $this->generate_turnstile_html();

			// Frontend: Render captcha HTML before submit button
			if ( $captcha_html && bricks_is_frontend() ) {
				echo "<div class=\"form-group captcha\">$captcha_html</div>";
			}
			?>

		  <div <?php echo $this->render_attributes( 'submit-wrapper' ); ?>>
				<button type="submit" <?php echo $this->render_attributes( 'submit-button' ); ?>>
					<?php
					if ( $submit_button_icon && $submit_button_icon_position === 'left' ) {
						echo $submit_button_icon;
					}

					if ( ! isset( $settings['submitButtonIcon'] ) || ( isset( $settings['submitButtonIcon'] ) && isset( $settings['submitButtonText'] ) ) ) {
						$this->set_attribute( 'submitButtonText', 'class', 'text' );

						$submit_button_text = isset( $settings['submitButtonText'] ) ? esc_html( $settings['submitButtonText'] ) : esc_html__( 'Send', 'bricks' );

						echo "<span {$this->render_attributes( 'submitButtonText' )}>$submit_button_text</span>";
					}

					if ( $submit_button_icon && $submit_button_icon_position === 'right' ) {
						echo $submit_button_icon;
					}
					?>
				</button>
			</div>
		</form>
		<?php
	}

	/**
	 * Split a text into key-value pairs
	 *
	 * This function takes a text and splits it into key-value pairs using the colon (:) as the delimiter.
	 * If the text contains a colon, it will split the text into two parts: the key and the value.
	 * If the text does not contain a colon, it will use the entire text as both the key and the value.
	 *
	 * @param string $text The text to split.
	 * @return array An array containing the key-value pairs.
	 *
	 * @since 1.10.2
	 */
	public function split_text( $text ) {
		$parts = explode( ':', $text, 2 );

		if ( count( $parts ) === 2 ) {
			return [
				'key'   => trim( ! empty( $parts[0] ) ? $parts[0] : $parts[1] ),
				'value' => trim( ! empty( $parts[1] ) ? $parts[1] : $parts[0] ),
			];
		}

		return [
			'key'   => trim( $text ),
			'value' => trim( $text ),
		];
	}

	/**
	 * Generate recaptcha HTML
	 *
	 * @since 1.5
	 */
	public function generate_recaptcha_html() {
		$settings = $this->settings;

		if ( ! isset( $settings['enableRecaptcha'] ) ) {
			return;
		}

		$recaptcha_key = ! empty( Database::$global_settings['apiKeyGoogleRecaptcha'] ) ? Database::$global_settings['apiKeyGoogleRecaptcha'] : false;

		if ( ! $recaptcha_key ) {
			return;
		}

		$this->set_attribute( 'recaptcha', 'id', 'recaptcha-' . esc_attr( $this->id ) );
		$this->set_attribute( 'recaptcha', 'data-key', $recaptcha_key );
		$this->set_attribute( 'recaptcha', 'class', 'recaptcha-hidden' );

		$html  = '<div class="form-group recaptcha-error">';
		$html .= '<div class="brxe-alert danger">';
		$html .= '<p>' . esc_html__( 'Google reCaptcha: Invalid site key.', 'bricks' ) . '</p>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= "<div {$this->render_attributes( 'recaptcha' )}></div>";

		return $html;
	}

	/**
	 * Generate hCaptcha HTML
	 *
	 * @since 1.9.2
	 */
	public function generate_hcaptcha_html() {
		$hcaptcha_mode = $this->settings['enableHCaptcha'] ?? '';

		// Return: hCaptcha not enabled
		if ( ! $hcaptcha_mode ) {
			return;
		}

		// Return: hCaptcha key not set
		if ( empty( Database::$global_settings['apiKeyHCaptcha'] ) ) {
			return;
		}

		$this->set_attribute( 'hcaptcha', 'id', 'hcaptcha-' . esc_attr( $this->id ) );
		$this->set_attribute( 'hcaptcha', 'class', 'h-captcha' );
		$this->set_attribute( 'hcaptcha', 'data-sitekey', Database::$global_settings['apiKeyHCaptcha'] );

		// Visible hCaptcha
		if ( $hcaptcha_mode === 'visible' ) {
			// hCaptcha size
			if ( ! empty( $this->settings['hCaptchaSize'] ) ) {
				$this->set_attribute( 'hcaptcha', 'data-size', esc_attr( $this->settings['hCaptchaSize'] ) );
			}

			// hCaptcha theme
			if ( ! empty( $this->settings['hCaptchaTheme'] ) ) {
				$this->set_attribute( 'hcaptcha', 'data-theme', esc_attr( $this->settings['hCaptchaTheme'] ) );
			}
		}

		// Invisible hCaptcha
		elseif ( $hcaptcha_mode === 'invisible' ) {
			$this->set_attribute( 'hcaptcha', 'data-size', 'invisible' );
			// NOTE: Not in use as we can't pass any args (such as the form ID) to the "onSubmit" callback
			// $this->set_attribute( 'hcaptcha', 'data-callback', 'onSubmit' );
		}

		return "<div {$this->render_attributes( 'hcaptcha' )}></div>";
	}

	/**
	 * Generate Turnstile HTML
	 *
	 * @since 1.9.2
	 */
	public function generate_turnstile_html() {
		// Return: Turnstile not enabled
		if ( ! isset( $this->settings['enableTurnstile'] ) ) {
			return;
		}

		if ( ! empty( $this->settings['turnstileSize'] ) ) {
			$this->set_attribute( 'turnstile', 'data-size', esc_attr( $this->settings['turnstileSize'] ) );
		}

		if ( ! empty( $this->settings['turnstileTheme'] ) ) {
			$this->set_attribute( 'turnstile', 'data-theme', esc_attr( $this->settings['turnstileTheme'] ) );
		}

		// Return: Turnstile key not set
		if ( empty( Database::$global_settings['apiKeyTurnstile'] ) ) {
			return;
		}

		$this->set_attribute( 'turnstile', 'id', 'turnstile-' . esc_attr( $this->id ) );
		$this->set_attribute( 'turnstile', 'class', 'cf-turnstile' );
		$this->set_attribute( 'turnstile', 'data-sitekey', Database::$global_settings['apiKeyTurnstile'] );

		return "<div {$this->render_attributes( 'turnstile' )}></div>";
	}

	/**
	 * Generate CSS styles for honeypot fields
	 *
	 * @param array $fields The form fields
	 *
	 * @since 1.12.2
	 */
	public function generate_honeypot_field_styles( $fields ) {
		$honeypot_selectors = [];
		$css                = '';

		foreach ( $fields as $index => $field ) {
			// Target only honeypot fields
			if ( ! isset( $field['isHoneypot'] ) ) {
				continue;
			}

			// If type is "select", we need to target the select element
			if ( $field['type'] === 'select' ) {
				$honeypot_selectors[] = "select#form-field-{$field['unique_id']}";
			}

			// If type is "textarea", we need to target the textarea element
			elseif ( $field['type'] === 'textarea' ) {
				$honeypot_selectors[] = "textarea#form-field-{$field['unique_id']}";
			}

			// If type is "checkbox" or "radio" we need to check if ID *starts* with the unique ID
			elseif ( $field['type'] === 'checkbox' || $field['type'] === 'radio' ) {
				$honeypot_selectors[] = "input[id^='form-field-{$field['unique_id']}']";
			}

			// Default: Target input fields
			else {
				$honeypot_selectors[] = "input#form-field-{$field['unique_id']}";
			}
		}

		// If we have honeypot fields, generate CSS
		if ( ! empty( $honeypot_selectors ) ) {
			$css = 'div.form-group:has(' . implode( ',', $honeypot_selectors ) . ')';

			// Set defined CSS rules
			$css_properties = [
				'opacity'  => '0 !important',
				'position' => 'absolute !important',
				'top'      => '-9999px !important',
				'left'     => '-9999px !important',
				'height'   => '0 !important',
				'width'    => '0 !important',
				'z-index'  => '-1 !important',
				'padding'  => '0 !important',
			];

			$css .= '{' . implode(
				';',
				array_map(
					fn( $key, $value) => "$key: $value",
					array_keys( $css_properties ),
					$css_properties
				)
			) . '}';
		}

		return $css;
	}
}
