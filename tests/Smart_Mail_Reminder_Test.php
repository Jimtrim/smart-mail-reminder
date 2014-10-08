<?php

class Smart_Mail_Reminder_Test extends WP_UnitTestCase {

	/* @var Smart_Mail_Reminder reminder */
	private $reminder;

	function setUp() {
		parent::setUp();
		$this->reminder = Smart_Mail_Reminder::get_instance();
	}

	function test_remove_duplicates() {
		$real = array("aa@aa.aa", "bb@bb.bb", "aa@aa.aa", "cc@cc.cc");
		$expected = array("aa@aa.aa", "bb@bb.bb", "cc@cc.cc");

		$real = $this->reminder->remove_duplicates($real);

		$this->assertTrue($expected === $real);
	}
}

