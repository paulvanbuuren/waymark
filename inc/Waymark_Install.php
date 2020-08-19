<?php

class Waymark_Install {
	private static $plugin_path;
	
	static function init() {
		self::$plugin_path = str_replace('inc/Waymark_Install', 'Waymark', __FILE__);
		
		register_activation_hook(self::$plugin_path, array('Waymark_Install', 'do_install'));
		register_uninstall_hook(self::$plugin_path, array('Waymark_Install', 'do_uninstall'));

		add_action('admin_init', array('Waymark_Install', 'activation_redirect'));
		add_action('admin_init', array('Waymark_Install', 'update_check'));

		add_filter('plugin_action_links_waymark/Waymark.php', array('Waymark_Install', 'add_action_links'));		
	}

	static function get_data_item($key) {
		if(! is_array(self::$data)) {
			return null;
		}
		
		if(array_key_exists($key, self::$data)) {
			return self::$data[$key];
		} else {
			return null;
		}
	}	
	
	//Thanks https://stackoverflow.com/a/2463514
	static function activation_redirect() {
		if(get_option('waymark_activation_redirect')) {
			delete_option('waymark_activation_redirect');

			wp_redirect(admin_url('edit.php?post_type=waymark_map'));
			
			die;		
		}
	}
	
	static function do_install() {
		//Flush permalinks
		$Waymark_Types = new Waymark_Types;		
		$Waymark_Types->register_types();
    flush_rewrite_rules();
    
    //Trigger redirect
    add_option('waymark_activation_redirect', true);		
	}

	static function do_uninstall() {
		delete_option('Waymark_Settings');			
		delete_option('Waymark_Settings_Backup');			
		delete_option('Waymark_Version');			
	}

	static function add_action_links($links) {
		$links_before = array();

		$links_after = array(
			'<a href="' . admin_url('edit.php?post_type=waymark_map') . '">' . esc_html__('Maps', 'waymark-plugin') . '</a>',
			'<a href="' . admin_url('edit.php?post_type=waymark_map&page=waymark-settings') . '">' . esc_html__('Settings', 'waymark-plugin') . '</a>'
		);				
		
		return array_merge($links_before, $links, $links_after);
	}
	
	static function update_check() {
		//If versions differ
		if(get_option('Waymark_Version') != Waymark_Config::get_item('plugin_version')) {
	    //Backup Settings
	    add_option('Waymark_Settings_' . get_option('Waymark_Version'), get_option('Waymark_Settings'));		
	    
			//Flush permalinks
	    flush_rewrite_rules();
			
			//Update version
	    update_option('Waymark_Version', Waymark_Config::get_item('plugin_version'));		
		}		
	}
}	

Waymark_Install::init();