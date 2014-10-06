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
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init' , array($this, 'admin_init'));
		add_action( 'init', array($this, 'register_acf_fields'));
		add_action( 'save_post', array($this, 'reset_mail_sent_meta' ));
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

		register_setting( 'reminder-settings-group', 'reminder_admin_receive_bool' );
		register_setting( 'reminder-settings-group', 'reminder_admin_email' );
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
//			var_dump($users);
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
						'label' => 'Send til skribent',
						'name' => 'reminder_author_bool',
						'type' => 'true_false',
						'required' => 1,
						'message' => 'Huk av for å gi skribent påminnelse',
						'default_value' => 0,
					),
					array (
						'key' => 'field_54326012e38b1',
						'label' => 'Ekstra mottakere',
						'name' => 'reminder_recipients',
						'type' => 'repeater',
						'instructions' => 'Testing adding of users',
						'required' => 1,
						'sub_fields' => array (
							array (
								'key' => 'field_54326031e38b2',
								'label' => 'User',
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
						'row_min' => 1,
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
?>
<div class="wrap">
	<h2><?php _e('Smart Mail Reminder') ?></h2>

	<form method="post" action="options.php">
		<?php settings_fields( 'reminder-settings-group' ); ?>
		<?php do_settings_sections( 'reminder-settings-group' ); ?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e('Administrators epost') ?></th>
				<td><input type="text" name="reminder_admin_email" value="<?php echo esc_attr( get_option('reminder_admin_email') ); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="reminder_admin_receive_bool"><?php _e('Skal administrator få påminnelser?') ?></label></th>
				<td>
					<input type="checkbox" name="reminder_admin_receive_bool" id="reminder_admin_receive_bool" <?php
					if(! esc_attr( get_option('reminder_admin_receive_bool') )) echo "un"; ?>checked />

				</td>
			</tr>
		</table>

		<?php submit_button(); ?>

	</form>
</div>

<?php }

	/* Controller */
	public function reset_mail_sent_meta($post_id) {
		self::set_mail_sent_meta($post_id, 0);
	}

	private static function set_mail_sent_meta($post_id, $value=0) {
		if ( wp_is_post_revision( $post_id ) )
			return;

		if (get_post_meta($post_id, 'reminder_sent'))
			update_post_meta($post_id, 'reminder_sent', $value, get_post_meta($post_id, 'reminder_sent'));
		else
			add_post_meta($post_id, 'reminder_sent', $value);
	}

	public static function cron_daily() {
		$query_args = array(
			'post_type'        => 'post',
			'post_status'      => 'publish',
			'meta_key'         => 'reminder_date',
			'meta_value'       => date("Ymd"),
			'suppress_filters' => true
		); // Get all posts with raminderdate set to today

		$posts = get_posts($query_args);
		foreach ( $posts as $post ) {
			/* @var $post WP_Post */
			$meta = get_post_meta($post->ID);

			if ($meta["reminder_sent"] && $meta["reminder_sent"] == "1")
				continue; //filter all that have been sent today

			$recipients = array();
			$reminder_date = $meta['reminder_date'][0];
			if ($reminder_date && $reminder_date === date("Ymd")) {
				$subject = "[" . get_option("blogname") . "] " . __("Automatisk varsel");
				$message = $meta["reminder_text"][0];
				$footer = __("Artikkel: ") . get_permalink($post->ID);


				if ($meta["reminder_author_bool"][0] == "1") {
					$recipients[] = get_the_author_meta('user_email', $post->post_author);
				}
				if (get_option("reminder_admin_receive_bool")) {
					$recipients[] = get_option("reminder_admin_email");
				}

				for ($i = 0; $i < intval(get_post_meta($post->ID, "reminder_recipients")[0], 10) ; $i++) {
					$recipients[] = get_post_meta($post->ID, "reminder_recipients_" . $i . "_user")[0];
				}

//				var_dump($meta);
				foreach ( self::remove_duplicates($recipients) as $recipient ) {
					wp_mail($recipient, $subject . $footer, $message);
				}
				self::set_mail_sent_meta($post->ID, 1);
			}
		}

	}

	private function remove_duplicates($arr1) {
		$arr2 = array();
		foreach($arr1 as $item) {
			if (! in_array($item, $arr2) )
				$arr2[] = $item;
		}
		return $arr2;
	}


} 