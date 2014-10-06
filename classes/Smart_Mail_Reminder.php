<?php
/**
 * Created by PhpStorm.
 * User: jimtrim
 * Date: 06/10/14
 * Time: 08:41
 */



class Smart_Mail_Reminder {
	private static $instance = null;

	public static function get_instance() {
		if ( ! isset( self::$instance ) )
			self::$instance = new self;

		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_init' , array($this, 'admin_init'));
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	/* Initialization */

	public static function activate() {
		wp_schedule_event(current_time('timestamp'), 'hourly', 'Smart_Mail_Reminder::cron_send');
		self::register_acf_fields();
	}

	public static function deactivate() {
		wp_unschedule_event(current_time('timestamp'), 'Smart_Mail_Reminder::cron_send');
	}

	public static function uninstall() {
		if ( __FILE__ != WP_UNINSTALL_PLUGIN )
			return;
	}

	public function admin_init() {
	}

	public function admin_menu() {
		add_options_page( __('Smart Mail Reminder'), __('Smart Mail Reminder'), 'manage_options',
			'smart-mail-reminder', array( $this, 'view_options') );
	}

	private static function register_acf_fields() {
		if(function_exists("register_field_group"))
		{
			register_field_group(array (
				'id' => 'acf_reminder-to-authors',
				'title' => __('Reminder to author(s)'),
				'fields' => array (
					array (
						'key' => 'field_542e86f726296',
						'label' => __('Påminnelsedato'),
						'name' => 'reminder_date',
						'type' => 'date_picker',
						'required' => 1,
						'date_format' => 'yymmdd',
						'display_format' => 'dd/mm/yy',
						'first_day' => 1,
					),
					array (
						'key' => 'field_542e872f26297',
						'label' => __('Påminnelsetekst'),
						'name' => 'reminder_text',
						'type' => 'textarea',
						'required' => 1,
						'default_value' => 'Dette er en automatisk varsling om et innlegg på distriktssenteret.no.',
						'placeholder' => '',
						'maxlength' => '',
						'rows' => '',
						'formatting' => 'br',
					),
					array (
						'key' => 'field_542e88cd26298',
						'label' => __('Mottaker(e)'),
						'name' => 'reminder_recipients',
						'type' => 'checkbox',
						'required' => 1,
						'choices' => array (
							'author' => 'Skribent',
							'editor' => 'Redaktør',
							'admin' => 'Administrator',
						),
						'default_value' => '',
						'layout' => 'vertical',
					),
				),
				'location' => array (
					array (
						array (
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'post',
							'order_no' => 0,
							'group_no' => 0,
						),
					),
				),
				'options' => array (
					'position' => 'side',
					'layout' => 'default',
					'hide_on_screen' => array (
					),
				),
				'menu_order' => 0,
			));
		}
	}

	/* Views */
	public function view_options() {
		echo "<h1>It works</h1>";
	}

	/* Controller */
	public static function cron_send() {

	}


} 