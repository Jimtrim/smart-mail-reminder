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

	public static function activate() {}

	public static function deactivate() {}

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

	/* Views */

	public function view_options() {
		echo "<h1>It works</h1>";
	}


} 