<?php

/**
  * The plugin bootstrap file
  *
  * @link              https://robertdevore.com
  * @since             1.0.0
  * @package           Markdown_Exporter
  *
  * @wordpress-plugin
  *
  * Plugin Name: Markdown Exporter for WordPress®
  * Description: Seamlessly convert your WordPress posts, pages, and custom content types into well-structured Markdown (MD) files. Featuring customizable export settings, support for Advanced Custom Fields (ACF) and Pods, and a real-time progress bar for efficient content management.
  * Plugin URI:  https://github.com/robertdevore/markdown-exporter-for-wordpress/
  * Version:     1.1.0
  * Author:      Robert DeVore
  * Author URI:  https://robertdevore.com/
  * License:     GPL-2.0+
  * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
  * Text Domain: markdown-exporter
  * Domain Path: /languages
  * Update URI:  https://github.com/robertdevore/markdown-exporter-for-wordpress/
  */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Set the plugin version.
define( 'MARKDOWN_EXPORTER_VERSION', '1.1.0' );

// Include the class.
require 'classes/MarkdownExporter.php';

// Include the PluginUpdateChecker.
require 'vendor/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
	'https://github.com/robertdevore/markdown-exporter-for-wordpress/',
	__FILE__,
	'markdown-exporter-for-wordpress'
);

// Set the branch that contains the stable release.
$myUpdateChecker->setBranch( 'main' );

// Check if Composer's autoloader is already registered globally.
if ( ! class_exists( 'RobertDevore\WPComCheck\WPComPluginHandler' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use RobertDevore\WPComCheck\WPComPluginHandler;

new WPComPluginHandler( plugin_basename( __FILE__ ), 'https://robertdevore.com/why-this-plugin-doesnt-support-wordpress-com-hosting/' );

// Create variable for settings link filter.
$plugin_name = plugin_basename( __FILE__ );

/**
 * Load plugin text domain for translations
 * 
 * @since 1.1.0
 * @return void
 */
function mewp_load_textdomain() {
    load_plugin_textdomain( 
        'markdown-exporter', 
        false, 
        dirname( plugin_basename( __FILE__ ) ) . '/languages/'
    );
}
add_action( 'plugins_loaded', 'mewp_load_textdomain' );


/**
 * Add settings link on plugin page
 *
 * @param array $links an array of links related to the plugin.
 * 
 * @since  1.0.1
 * @return array updatead array of links related to the plugin.
 */
function markdown_exporter_settings_link( $links ) {
    // Settings link.
    $settings_link = '<a href="tools.php?page=markdown-exporter">' . esc_html__( 'Settings', 'markdown-exporter' ) . '</a>';
    // Add the settings link to the $links array.
    array_unshift( $links, $settings_link );

    return $links;
}
add_filter( "plugin_action_links_$plugin_name", 'markdown_exporter_settings_link' );

// Initialize class.
new MarkdownExporter();
