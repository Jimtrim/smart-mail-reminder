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
		add_action( 'init', array($this, 'register_acf_fields'));
	}

	/* Initialization */

	public static function activate() {
		wp_schedule_event( '1388574000' , 'daily', 'Smart_Mail_Reminder::cron_daily'); // 1.jan 2014 12:00
	}

	public static function deactivate() {
		wp_unschedule_event( '1388574000', 'Smart_Mail_Reminder::cron_daily'); // 1.jan 2014 12:00
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

	public function register_acf_fields() {
		if(function_exists("register_field_group"))
		{
			$users = array();
			foreach ( get_users() as $user ) {
				/* @var $user WP_User */
				$users[ $user->get("user_email") ] = $user->get("user_nicename");
			}
			register_field_group(array (
				'id' => 'acf_reminder-to-authors',
				'title' => 'Reminder to author(s)',
				'fields' => array (
					array (
						'key' => 'field_542e86f726296',
						'label' => 'Påminnelsedato',
						'name' => 'reminder_date',
						'type' => 'date_picker',
						'required' => 1,
						'date_format' => 'yymmdd',
						'display_format' => 'dd/mm/yy',
						'first_day' => 1,
					),
					array (
						'key' => 'field_542e872f26297',
						'label' => 'Påminnelsetekst',
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
						'label' => 'Mottaker(e)',
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
					array (
						'key' => 'field_54326012e38b1',
						'label' => 'Flere mottakere',
						'name' => 'reminder_extra_recipients',
						'type' => 'repeater',
						'instructions' => __('Legg til flere brukere som skal få påminnelse'),
						'required' => 0,
						'sub_fields' => array (
							array (
								'key' => 'field_54326031e38b2',
								'label' => 'Mottakere',
								'name' => 'user',
								'type' => 'select',
								'required' => 1,
								'column_width' => '',
								'choices' => $users,
								'default_value' => '',
								'allow_null' => 0,
								'multiple' => 0,
							),
						),
						'row_min' => 0,
						'row_limit' => '',
						'layout' => 'table',
						'button_label' => 'Legg til bruker',
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
//		$users = array();
//		foreach ( get_users() as $user ) {
//			/* @var $user WP_User */
//			$users[ $user->get("user_email") ] = $user->get("user_nicename");
//		}
//		var_dump($users);

		self::cron_daily();
	}

	/* Controller */
	public static function cron_daily() {
		$query_args = array(
			'post_type'        => 'post',
			'post_status'      => 'publish',
			'suppress_filters' => true
		);
		$posts = get_posts($query_args);
		foreach ( $posts as $post ) {
			/* @var $post WP_Post */

			$recipients = array();
			$meta = get_post_meta($post->ID);
			$reminder_date = $meta['reminder_date'][0];
			if ($reminder_date && $reminder_date === date("Ymd")) {
				$subject = "[" . get_option("blogname") . "] " . __("Automatisk varsel");
				$message = $meta["reminder_text"][0];
				$footer = __("Artikkel: ") . get_permalink($post->ID);


				foreach ( get_post_meta( $post->ID, "reminder_recipients" )[0] as $recipient ) {
					if ($recipient === "author") {
						$recipients[] = get_the_author_meta('user_email', $post->post_author);
					}
					if ($recipient === "editor") {
						//TODO: add editor recipient(s)
					}
					if ($recipient === "admin") {
						//TODO: add admin recipient(s)
					}
				}

				for ($i = 0; $i < intval(get_post_meta($post->ID, "reminder_extra_recipients")[0], 10) ; $i++) {
					$recipients[] = get_post_meta($post->ID, "reminder_extra_recipients_" . $i . "_user")[0];
				}
//				var_dump($recipients);

				echo $subject;
				echo $message;
				echo $footer;

			}
			var_dump($recipients);
		}

	}


} 