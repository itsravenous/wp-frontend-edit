<?php
/**
 * @package WPFrontEndEdit
 * @version 0.1
 */
/*
Plugin Name: Front-End Edit
Plugin URI: https://github.com/itsravenous/wp-frontend-edit
Description: Allows HTML forms to be submitted to edit the current post in Wordpress. 
Author: Tom Jenkins
Version: 0.1
Author URI: http://itsravenous.com
*/

require_once('htmlpurifier/library/HTMLPurifier.auto.php');
require_once('inc/processor.php');


function frontend_edit_init() {
	global $post;

	$feep = new FrontEndEditProcessor();
	$feep->post = $post;
	$feep->process_submission($_POST);
}

add_action('wp', 'frontend_edit_init');

$feep_errors = NULL;

?>
