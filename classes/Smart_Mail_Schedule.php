<?php
/**
 * Created by PhpStorm.
 * User: jimtrim
 * Date: 06/10/14
 * Time: 08:41
 */

namespace Smartmedia;


class Smart_Mail_Schedule {
    private static $instance = null;

    public static function get_instance() {
        if ( ! isset( self::$instance ) )
            self::$instance = new self;

        return self::$instance;
    }

    private function __construct() {}

    public static function activate() {}

    public static function deactivate() {}

    /*
		public static function uninstall() {
			if ( __FILE__ != WP_UNINSTALL_PLUGIN )
				return;
		}
	*/
} 