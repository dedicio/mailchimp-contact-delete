<?php
/**
* Plugin Name: Mailchimp Contact Unsubscribe
* Description: Descadastrar contatos das listas do MailChimp
* Version: 1.0
* Author: Dedicio Coelho
* Author URI: http://dedicio.com
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

$dir = plugin_dir_path( __FILE__ );


register_activation_hook(__FILE__, "activate_mcu");
register_deactivation_hook(__FILE__, "deactivate_mcu");



function remove_menu_mcu() {
	remove_menu_page('ddc-mailchimp-contact-unsubscribe/ddc-mailchimp-contact-unsubscribe-list.php');
	remove_submenu_page('ddc-mailchimp-contact-unsubscribe/ddc-mailchimp-contact-unsubscribe-list.php','ddc-mailchimp-contact-unsubscribe/ddc-prospects-list.php');
	remove_submenu_page('ddc-mailchimp-contact-unsubscribe/ddc-mailchimp-contact-unsubscribe-list.php','ddc-mailchimp-contact-unsubscribe/ddc-prospects-config.php');
}
add_action('admin_menu','remove_menu_mcu');


function register_menu_mcu() {
	add_menu_page(
		'Mailchimp Contact Unsubscribe',
		'Mailchimp Contact Unsubscribe',
		'edit_posts',
		'ddc-mailchimp-contact-unsubscribe/ddc-mailchimp-contact-unsubscribe-list.php',
		'',
		'dashicons-id',
		30
	);
	add_submenu_page(
		'ddc-mailchimp-contact-unsubscribe/ddc-mailchimp-contact-unsubscribe-list.php',
		'Listar contatos',
		'Lista de Contatos',
		'edit_posts',
		'ddc-mailchimp-contact-unsubscribe/ddc-mailchimp-contact-unsubscribe-list.php'
	);
	add_submenu_page(
		'ddc-mailchimp-contact-unsubscribe/ddc-mailchimp-contact-unsubscribe-list.php',
		'Configurações',
		'Configurações MailChimp',
		'create_users',
		'ddc-mailchimp-contact-unsubscribe/ddc-mailchimp-contact-unsubscribe-config.php'
	);
}
add_action('admin_menu','register_menu_mcu');



function activate_mcu() {

	global $mcu_db_version;
	$mcu_db_version = '1.0';

	global $wpdb;
	$table_contacts = $wpdb->prefix . 'ddc_mcu';
	$charset_collate = $wpdb->get_charset_collate();

	$sql =
		"
		CREATE TABLE IF NOT EXISTS {$table_contacts} (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  user tinytext NOT NULL,
			contact text NOT NULL,
			list text NOT NULL,
		  PRIMARY KEY  (id)
		) {$charset_collate};
		";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );


	if( !get_option('mcu_apikey') ) add_option('mcu_apikey','','','no');
	if( !get_option('mcu_users') ) :

		$users = get_users();
		$users_array = array();
		foreach( $users as $user ) :
			array_push($users_array,array( $user->user_login => '' ));
		endforeach;
		$users = json_encode($users_array);
		add_option('mcu_users',$users);

	endif;


	add_option( 'mcu_db_version', $mcu_db_version );
	do_action('admin_menu','register_menu_mcu');

}


function deactivate_mcu() {

	do_action('admin_menu','remove_menu_mcu');

}

function uninstall_mcu() {

	// if uninstall.php is not called by WordPress, die
	if (!defined('WP_UNINSTALL_PLUGIN')) {
	    die;
	}

	$mcu_db_version = 'mcu_db_version';

	delete_option($mcu_db_version);

	// for site options in Multisite
	delete_site_option($mcu_db_version);

	// drop a custom database table
	global $wpdb;
	$table_contacts = $wpdb->prefix . 'ddc_mcu';
	$wpdb->query("DROP TABLE IF EXISTS {$table_contacts}");

	delete_option('mcu_users');
	delete_option('mcu_apikey');

}

wp_register_style ( 'bootstrap-grid', plugins_url ( 'css/bootstrap-grid.min.css', __FILE__ ) );
wp_enqueue_style('bootstrap-grid');
