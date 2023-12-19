<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$help_text = '<div id="urlyar_help"><img class="urlyarplugin-logo" src="'.$this->plugin_url.'/urlyar-logo.jpg" alt="" />';

	$help_text .= '<h3>'. __('More Information', 'url-shortener') . '</h3>';
	$help_text .= '<p>'.__('Additional information and upgrade notes are available via the plugin\'s <a href="http://salamzadeh.net/plugins/urlyar/docs/url-shortener-wordpress-plugin">Wiki Page</a>', 'url-shortener').'</p>';
	
	$help_text .= '<p>'. __('Do check out the <a href="http://salamzadeh.net/plugins/urlyar/docs/url-shortener-wordpress-plugin/faq">FAQ</a> and <a href="http://salamzadeh.net/plugins/urlyar/docs/url-shortener-wordpress-plugin/known-issues">Known Issues</a> as well').'</p>';


	$help_text .= '<h3>'.__('Version References', 'url-shortener').'</h3>';
	$help_text .= '<p><ul>';
		$help_text .= '<li>'.__('Plugin Version: ', 'url-shortener').$this->plugin_version.'-'.$this->plugin_status.'</li>';
	$help_text .= '</ul></p>';


	$help_text .= '<h3>'. __('Shortener Modules', 'url-shortener').'</h3>'; 
	$help_text .= '<table id="component_list" class="widefat post fixed" cellspacing="0">';
		$help_text .= '<thead>';
			$help_text .= '<tr>';
				$help_text .= '<th scope="col" class="manage-column">' . __('Name', 'url-shortener') . '</th>';
				$help_text .= '<th scope="col" class="manage-column">'. __('Description', 'url-shortener') .'</th>';
				$help_text .= '<th scope="col" class="manage-column colsmall">'. __('ID', 'url-shortener') .'</th>';
			$help_text .= '</tr>';
		$help_text .= '</thead>';
		$help_text .= '<tbody>';
			foreach ($this->shortener_modules as $modules){ 
			$help_text .= '<tr>';
				$help_text .= '<td class="name">';
					$help_text .= '<strong class="checkit">'.$modules['name'] .'</strong>';
					$help_text .= '<span>Version:'. $modules['version'] .'</span>';
				$help_text .= '</td>';
				$help_text .= '<td>';
					$help_text .= $modules['description'];
				$help_text .= '</td>';
				$help_text .= '<td>'.$modules['classname'] .'</td>';
			$help_text .= '</tr>';
			}
		$help_text .= '</tbody>';
	$help_text .= '</table>';




	$help_text .= '<h3>'. __('Support', 'url-shortener') . '</h3>';
	$help_text .= '<p>'. __('You can get support for the plugin via the following channels: ', 'url-shortener') . '<ul>';
		$help_text .= '<li>'. __('<a href="http://wordpress.org/tags/url-shortener">WordPress Support Forum</a>', 'url-shortener'). '</li>';
		$help_text .= '<li>'. __('<a href="https://www.salamzadeh.net/contact/">Send me a message</a>', 'url-shortener').'</li>';
	$help_text .= '</ul></p>';
	$help_text .= '<p>' . __('If you like this plugin and would like to <a href="http://salamzadeh.net/plugins/urlyar/contribute/">Contribute</a> back, I\'ll be very grateful!', 'url-shortener') . '</p>';
$help_text .= '</div>';
?>
