<?php
/**
* Plugin Name: Mailchimp Contact Delete
* Description: Excluir contatos das listas do MailChimp
* Version: 1.0
* Author: Dedicio Coelho
* Author URI: http://dedicio.com
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

$dir = plugin_dir_path( __FILE__ );


register_activation_hook(__FILE__, "activate_mcd");
register_deactivation_hook(__FILE__, "deactivate_mcd");



function remove_menu_mcd() {
	remove_menu_page('ddc-mailchimp-contact-delete/ddc-mailchimp-contact-delete-list.php');
	remove_submenu_page('ddc-mailchimp-contact-delete/ddc-mailchimp-contact-delete-list.php','ddc-mailchimp-contact-delete/ddc-prospects-list.php');
	remove_submenu_page('ddc-mailchimp-contact-delete/ddc-mailchimp-contact-delete-list.php','ddc-mailchimp-contact-delete/ddc-prospects-config.php');
}
add_action('admin_menu','remove_menu_mcd');


function register_menu_mcd() {
	add_menu_page(
		'Mailchimp Contact Delete',
		'Mailchimp Contact Delete',
		'edit_posts',
		'ddc-mailchimp-contact-delete/ddc-mailchimp-contact-delete-list.php',
		'',
		'dashicons-id',
		30
	);
	add_submenu_page(
		'ddc-mailchimp-contact-delete/ddc-mailchimp-contact-delete-list.php',
		'Listar contatos',
		'Lista de Contatos',
		'edit_posts',
		'ddc-mailchimp-contact-delete/ddc-mailchimp-contact-delete-list.php'
	);
	add_submenu_page(
		'ddc-mailchimp-contact-delete/ddc-mailchimp-contact-delete-list.php',
		'Configurações',
		'Configurações MailChimp',
		'create_users',
		'ddc-mailchimp-contact-delete/ddc-mailchimp-contact-delete-config.php'
	);
}
add_action('admin_menu','register_menu_mcd');



function activate_mcd() {

	global $mcd_db_version;
	$mcd_db_version = '1.0';

	global $wpdb;
	$table_contacts = $wpdb->prefix . 'ddc_mcd';
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


	if( !get_option('mcd_apikey') ) add_option('mcd_apikey','','','no');
	if( !get_option('mcd_users') ) :

		$users = get_users();
		$users_array = array();
		foreach( $users as $user ) :
			array_push($users_array,array( $user->user_login => '' ));
		endforeach;
		$users = json_encode($users_array);
		add_option('mcd_users',$users);

	endif;


	add_option( 'mcd_db_version', $mcd_db_version );
	do_action('admin_menu','register_menu_mcd');

}


function deactivate_mcd() {

	do_action('admin_menu','remove_menu_mcd');

}

function uninstall_mcd() {

	// if uninstall.php is not called by WordPress, die
	if (!defined('WP_UNINSTALL_PLUGIN')) {
	    die;
	}

	$mcd_db_version = 'mcd_db_version';

	delete_option($mcd_db_version);

	// for site options in Multisite
	delete_site_option($mcd_db_version);

	// drop a custom database table
	global $wpdb;
	$table_contacts = $wpdb->prefix . 'ddc_mcd';
	$wpdb->query("DROP TABLE IF EXISTS {$table_contacts}");

	delete_option('mcd_users');
	delete_option('mcd_apikey');

}

wp_register_style ( 'bootstrap-grid', plugins_url ( 'css/bootstrap-grid.min.css', __FILE__ ) );
wp_enqueue_style('bootstrap-grid');
