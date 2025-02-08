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
  * Version:     1.0.1
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

define( 'MARKDOWN_EXPORTER_VERSION', '1.0.1' );

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
        'gallery-modals', 
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

/**
 * Class Markdown_Exporter
 *
 * Handles the export of WordPress content to Markdown format with a customizable settings page.
 */
class Markdown_Exporter {

    /**
     * Constructor.
     *
     * Initializes the Markdown Exporter plugin by hooking into necessary WordPress actions.
     *
     * @since  1.0.0
     * @return void
     */
    public function __construct() {
        // Register the settings page under the Tools menu.
        add_action( 'admin_menu', [ $this, 'register_settings_page' ] );

        // Initialize plugin settings.
        add_action( 'admin_init', [ $this, 'register_settings' ] );

        // Handle the AJAX export request.
        add_action( 'wp_ajax_markdown_export', [ $this, 'export_content' ] );

        // Enqueue necessary scripts and styles for the admin area.
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    /**
     * Registers the Markdown Exporter settings page as a submenu under the Tools menu.
     *
     * This method adds a new submenu item titled "Markdown Exporter" under the existing
     * "Tools" menu in the WordPress admin dashboard. Users with the `manage_options` capability
     * can access the settings page to configure and initiate content exports.
     *
     * @since  1.0.0
     * @return void
     */
    public function register_settings_page() {
        add_submenu_page(
            'tools.php',
            esc_html__( 'Markdown Exporter', 'markdown-exporter' ),
            esc_html__( 'Markdown Exporter', 'markdown-exporter' ),
            'manage_options',
            'markdown-exporter',
            [ $this, 'settings_page_callback' ]
        );
    }


    /**
     * Renders the Markdown Exporter settings page in the WordPress admin dashboard.
     *
     * This method outputs the HTML markup for the settings page, including the export form,
     * progress bar, and export log. Users can initiate the export process by clicking the
     * "Export" button, which triggers the AJAX export functionality.
     *
     * @since  1.0.0
     * @return void
     */
    public function settings_page_callback() {
        ?>
        <div class="wrap">
            <h1>
                <?php esc_html_e( 'Markdown Exporter Settings', 'markdown-exporter' ); ?>
                <a id="markdown-exporter-support-btn" href="https://robertdevore.com/contact/" target="_blank" class="button button-alt" style="margin-left: 10px;">
                    <span class="dashicons dashicons-format-chat" style="vertical-align: middle;"></span> <?php esc_html_e( 'Support', 'markdown-editor' ); ?>
                </a>
                <a id="markdown-exporter-docs-btn" href="https://robertdevore.com/articles/markdown-exporter-for-wordpress/" target="_blank" class="button button-alt" style="margin-left: 5px;">
                    <span class="dashicons dashicons-media-document" style="vertical-align: middle;"></span> <?php esc_html_e( 'Documentation', 'markdown-editor' ); ?>
                </a>
            </h1>
            <hr />
            <form id="markdown-exporter-form">
                <?php
                // Output necessary hidden fields for the settings API.
                settings_fields( 'markdown_exporter_options' );
                do_settings_sections( 'markdown-exporter' );
                ?>
                <button type="button" id="export-button" class="button button-primary">
                    <?php esc_html_e( 'Export', 'markdown-exporter' ); ?>
                </button>
                <div id="progress-bar-wrapper" style="margin-top: 20px; display: none;">
                    <div id="progress-bar-container">
                        <div id="progress-bar"></div>
                        <span id="progress-text">0%</span>
                    </div>
                </div>
                <div id="export-log-wrapper" style="margin-top: 20px; display: none;">
                    <h2><?php esc_html_e( 'Export Log', 'markdown-exporter' ); ?></h2>
                    <pre id="export-log"></pre>
                </div>
            </form>
        </div>
        <?php
    }

    /**
     * Enqueues the necessary scripts and styles for the Markdown Exporter plugin.
     *
     * This method loads the Select2 library for enhanced select fields, the custom CSS for
     * the plugin, and the main JavaScript file responsible for handling the export functionality.
     * It ensures that these assets are only loaded on the Markdown Exporter settings page
     * to optimize performance.
     *
     * @since  1.0.0
     * @return void
     */
    public function enqueue_scripts( $hook ) {
        if ( $hook !== 'tools_page_markdown-exporter' ) {
            return;
        }

        // Enqueue Select2 CSS.
        wp_enqueue_style( 'select2-css', plugins_url( 'assets/css/select2.min.css', __FILE__ ), [], MARKDOWN_EXPORTER_VERSION );

        // Enqueue custom CSS for the plugin.
        wp_enqueue_style( 'markdown-exporter-css', plugins_url( 'assets/css/markdown-exporter.css', __FILE__ ), [], MARKDOWN_EXPORTER_VERSION );

        // Enqueue Select2 JS.
        wp_enqueue_script( 'select2-js', plugins_url( 'assets/js/select2.min.js', __FILE__ ), [ 'jquery' ], MARKDOWN_EXPORTER_VERSION, true );

        // Enqueue the main script.
        wp_enqueue_script( 'markdown-exporter-script', plugins_url( 'assets/js/markdown-exporter.js', __FILE__ ), [ 'jquery', 'select2-js' ], MARKDOWN_EXPORTER_VERSION, true );

        // Localize script with AJAX URL and nonce.
        wp_localize_script( 'markdown-exporter-script', 'markdownExporter', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'markdown_export_nonce' ),
        ]);
    }

    /**
     * Registers plugin settings, sections, and fields.
     *
     * This method sets up the settings API for the Markdown Exporter plugin by registering
     * the main settings, adding a settings section for export options, and defining the
     * necessary settings fields including Post Types, Date Range, Author, Post Status, and Taxonomies.
     *
     * @since  1.0.0
     * @return void
     */
    public function register_settings() {
        register_setting( 'markdown_exporter_options', 'markdown_exporter_settings', [ $this, 'sanitize_settings' ] );
        
        add_settings_section(
            'markdown_exporter_main',
            esc_html__( 'Export Options', 'markdown-exporter' ),
            [ $this, 'settings_section_callback' ],
            'markdown-exporter'
        );

        add_settings_field( 'post_type', esc_html__( 'Post Types', 'markdown-exporter' ), [ $this, 'post_type_callback' ], 'markdown-exporter', 'markdown_exporter_main' );
        add_settings_field( 'date_range', esc_html__( 'Date Range', 'markdown-exporter' ), [ $this, 'date_range_callback' ], 'markdown-exporter', 'markdown_exporter_main' );
        add_settings_field( 'author', esc_html__( 'Author', 'markdown-exporter' ), [ $this, 'author_callback' ], 'markdown-exporter', 'markdown_exporter_main' );
        add_settings_field( 'post_status', esc_html__( 'Post Status', 'markdown-exporter' ), [ $this, 'post_status_callback' ], 'markdown-exporter', 'markdown_exporter_main' );
        add_settings_field( 'taxonomies', esc_html__( 'Taxonomies', 'markdown-exporter' ), [ $this, 'taxonomy_callback' ], 'markdown-exporter', 'markdown_exporter_main' );
    }

    /**
     * Renders the settings section description for the Markdown Exporter plugin.
     *
     * This method outputs the descriptive text for the main settings section,
     * guiding users on selecting options for exporting content to Markdown.
     *
     * @since  1.0.0
     * @return void
     */
    public function settings_section_callback() {
        esc_html_e( 'Select options for exporting content to Markdown.', 'markdown-exporter' );
    }

    /**
     * Renders the Post Types dropdown field in the Markdown Exporter settings.
     *
     * This method outputs a multi-select dropdown allowing users to choose specific
     * post types to include in the export. If no post types are selected, all public
     * post types will be exported by default.
     *
     * @since  1.0.0
     * @return void
     */
    public function post_type_callback() {
        $post_types = get_post_types( [ 'public' => true ], 'objects' );
        $options    = get_option( 'markdown_exporter_settings' );
    
        echo '<select name="markdown_exporter_settings[post_type][]" multiple style="width: 100%;">';
        foreach ( $post_types as $post_type ) {
            $selected = in_array( $post_type->name, $options['post_type'] ?? [] ) ? 'selected' : '';
            echo '<option value="' . esc_attr( $post_type->name ) . '" ' . $selected . '>' . esc_html( $post_type->label ) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . esc_html__( 'Select specific post types to export. Leave empty to export all public post types.', 'markdown-exporter' ) . '</p>';
    }
    
    /**
     * Renders the Date Range fields in the Markdown Exporter settings.
     *
     * This method outputs two date input fields, "From" and "To", enabling users
     * to specify a date range for the content they wish to export. Only posts within
     * the selected dates will be included in the export.
     *
     * @since  1.0.0
     * @return void
     */
    public function date_range_callback() {
        $options = get_option( 'markdown_exporter_settings' );
        ?>
        <input type="date" name="markdown_exporter_settings[date_from]" value="<?php echo esc_attr( $options['date_from'] ?? '' ); ?>" placeholder="From" style="margin-right: 10px;">
        <input type="date" name="markdown_exporter_settings[date_to]" value="<?php echo esc_attr( $options['date_to'] ?? '' ); ?>" placeholder="To">
        <?php
    }

    /**
     * Renders the Author dropdown field in the Markdown Exporter settings.
     *
     * This method outputs a dropdown menu allowing users to select a specific author
     * whose content will be exported. Selecting "All Authors" will include content from
     * all authors in the export.
     *
     * @since  1.0.0
     * @return void
     */
    public function author_callback() {
        $authors = get_users( [ 'who' => 'authors' ] );
        $options = get_option( 'markdown_exporter_settings' );

        echo '<select name="markdown_exporter_settings[author]" style="width:100%;">';
        echo '<option value="">' . esc_html__( 'All Authors', 'markdown-exporter' ) . '</option>';
        foreach ( $authors as $author ) {
            $selected = ( $options['author'] ?? '' ) === $author->ID ? 'selected' : '';
            echo '<option value="' . esc_attr( $author->ID ) . '" ' . $selected . '>' . esc_html( $author->display_name ) . '</option>';
        }
        echo '</select>';
    }

    /**
     * Renders the Post Status selection field in the Markdown Exporter settings.
     *
     * This method outputs a dropdown allowing users to select a specific post status
     * (e.g., Published, Draft) to filter the posts included in the export. Selecting "All Statuses"
     * will include posts regardless of their status.
     *
     * @since  1.0.0
     * @return void
     */
    public function post_status_callback() {
        $statuses = get_post_statuses();
        $options  = get_option( 'markdown_exporter_settings' );

        echo '<select name="markdown_exporter_settings[post_status]" style="width:100%;">';
        echo '<option value="">' . esc_html__( 'All Statuses', 'markdown-exporter' ) . '</option>';
        foreach ( $statuses as $status => $label ) {
            $selected = ( $options['post_status'] ?? '' ) === $status ? 'selected' : '';
            echo '<option value="' . esc_attr( $status ) . '" ' . $selected . '>' . esc_html( $label ) . '</option>';
        }
        echo '</select>';
    }

    /**
     * Renders the Taxonomies selection field in the Markdown Exporter settings.
     *
     * This method outputs a multi-select dropdown allowing users to choose specific taxonomies
     * (e.g., Categories, Tags) to filter the posts included in the export. If no taxonomies are selected,
     * posts from all taxonomies will be exported.
     *
     * @since  1.0.0
     * @return void
     */

    public function taxonomy_callback() {
        $taxonomies = get_taxonomies( [ 'public' => true ], 'objects' );
        $options    = get_option( 'markdown_exporter_settings' );

        echo '<select name="markdown_exporter_settings[taxonomies][]" multiple style="width:100%;">';
        foreach ( $taxonomies as $taxonomy ) {
            $selected = in_array( $taxonomy->name, $options['taxonomies'] ?? [] ) ? 'selected' : '';
            echo '<option value="' . esc_attr( $taxonomy->name ) . '" ' . $selected . '>' . esc_html( $taxonomy->label ) . '</option>';
        }
        echo '</select>';
    }

    /**
     * Sanitizes and validates the Markdown Exporter settings input.
     *
     * This method processes the user-submitted settings from the export form, ensuring that all
     * inputs are properly sanitized to prevent security vulnerabilities. It handles fields such as
     * post types, date range, author, post status, and taxonomies.
     *
     * @since  1.0.0
     *
     * @param array $settings The array of settings input from the export form.
     * @return array The sanitized and validated settings array.
     */
    public function sanitize_settings( $settings ) {
        $settings['post_type']   = array_map( 'sanitize_text_field', $settings['post_type'] ?? [] );
        $settings['date_from']   = sanitize_text_field( $settings['date_from'] );
        $settings['date_to']     = sanitize_text_field( $settings['date_to'] );
        $settings['author']      = sanitize_text_field( $settings['author'] ?? '' );
        $settings['post_status'] = sanitize_text_field( $settings['post_status'] ?? '' );
        $settings['taxonomies']  = array_map( 'sanitize_text_field', $settings['taxonomies'] ?? [] );

        return $settings;
    }

    /**
     * Handles the AJAX request to export WordPress content to Markdown files.
     *
     * This method processes the export based on user-selected settings, gathers the relevant posts,
     * converts them to Markdown with front matter, compiles them into a ZIP archive, and
     * provides a download link. It also logs the export process and returns statistics.
     *
     * @since  1.0.0
     * @return void
     */
    public function export_content() {
        // Verify nonce.
        check_ajax_referer( 'markdown_export_nonce', 'nonce' );

        // Initialize log array.
        $logs       = [];
        $start_time = microtime( true );

        // Retrieve and parse incoming form data.
        if ( isset( $_POST['form_data'] ) ) {
            parse_str( wp_unslash( $_POST['form_data'] ), $form_data );
        } else {
            wp_send_json_error( __( 'No form data received.', 'markdown-exporter' ) );
        }

        // Extract 'markdown_exporter_settings' from parsed form data.
        if ( isset( $form_data['markdown_exporter_settings'] ) && is_array( $form_data['markdown_exporter_settings'] ) ) {
            $settings = $form_data['markdown_exporter_settings'];
        } else {
            wp_send_json_error( __( 'Invalid form data.', 'markdown-exporter' ) );
        }

        // Sanitize and validate form data.
        $options = $this->sanitize_settings( $settings );

        // Determine post types to export.
        if ( ! empty( $options['post_type'] ) ) {
            $post_types = $options['post_type'];
            $logs[] = sprintf(
                esc_html__( 'Selected post types: %s.', 'markdown-exporter' ),
                implode( ', ', $post_types )
            );
        } else {
            // If no post types are selected, fetch all public post types.
            $post_types = get_post_types( [ 'public' => true ], 'names' );
            $logs[] = esc_html__( 'No post types selected. Exporting all public post types.', 'markdown-exporter' );
        }

        // Prepare query arguments.
        $args = [
            'post_type'      => $post_types,
            'posts_per_page' => -1,
            'author'         => ! empty( $options['author'] ) ? intval( $options['author'] ) : '',
            'post_status'    => ! empty( $options['post_status'] ) ? sanitize_text_field( $options['post_status'] ) : 'publish',
            'date_query'     => [],
        ];

        if ( ! empty( $options['date_from'] ) || ! empty( $options['date_to'] ) ) {
            $args['date_query'][] = [
                'after'     => ! empty( $options['date_from'] ) ? $options['date_from'] : '1970-01-01',
                'before'    => ! empty( $options['date_to'] ) ? $options['date_to'] : date( 'Y-m-d' ),
                'inclusive' => true,
            ];
        }

        // Fetch posts.
        $query = new WP_Query( $args );
        $post_count = $query->found_posts;

        if ( ! $query->have_posts() ) {
            wp_send_json_error( __( 'No posts found for export.', 'markdown-exporter' ) );
        }

        $logs[] = sprintf( __( 'Found %d items to export.', 'markdown-exporter' ), $post_count );

        // Initialize ZIP archive.
        if ( ! class_exists( 'ZipArchive' ) ) {
            wp_send_json_error( __( 'ZipArchive class is not available on this server.', 'markdown-exporter' ) );
        }

        $zip        = new ZipArchive();
        $site_name  = sanitize_title( get_bloginfo( 'name' ) );
        $zip_name   = $site_name . '_markdown_export_' . date( 'Y-m-d_H-i-s' ) . '.zip';
        $upload_dir = wp_upload_dir();
        $zip_path   = trailingslashit( $upload_dir['path'] ) . $zip_name;

        if ( $zip->open( $zip_path, ZipArchive::CREATE ) !== true ) {
            wp_send_json_error( __( 'Failed to create ZIP file.', 'markdown-exporter' ) );
        }

        $logs[] = esc_html__( 'Initialized ZIP archive.', 'markdown-exporter' );

        $exported_count = 0;

        // Process each post.
        foreach ( $query->posts as $post ) {
            $post_id         = $post->ID;
            $post_type       = get_post_type( $post_id );
            $post_type_obj   = get_post_type_object( $post_type );
            $post_type_label = $post_type_obj ? $post_type_obj->labels->singular_name : $post_type;

            $logs[] = sprintf( __( 'Exporting %s: "%s"', 'markdown-exporter' ), strtolower( $post_type_label ), get_the_title( $post_id ) );

            $post_content = $this->generate_markdown_content( $post_id );

            // Sanitize file path using post slug.
            $post_slug           = sanitize_file_name( $post->post_name );
            $post_type_sanitized = sanitize_file_name( $post_type );
            $title_sanitized     = $post_slug;

            // Ensure title is not empty.
            if ( empty( $title_sanitized ) ) {
                $title_sanitized = 'untitled-' . $post_id;
            }

            $file_path = "{$post_type_sanitized}/{$post_slug}/{$title_sanitized}.md";

            // Add file to ZIP.
            if ( $zip->addFromString( $file_path, $post_content ) ) {
                $exported_count++;
            } else {
                $logs[] = sprintf( __( 'Failed to add "%s" to ZIP.', 'markdown-exporter' ), $file_path );
            }
        }

        // Close ZIP archive.
        $zip->close();

        $logs[] = esc_html__( 'Finalized ZIP archive.', 'markdown-exporter' );

        // Construct download URL.
        $download_url = trailingslashit( $upload_dir['url'] ) . $zip_name;

        // Calculate total time taken.
        $end_time   = microtime( true );
        $time_taken = round( $end_time - $start_time, 2 );

        // Prepare statistics.
        $stats = [
            'total_exported' => $exported_count,
            'total_time'     => $time_taken,
        ];

        // Log statistics.
        $logs[] = sprintf( esc_html__( 'Export completed: %d items exported in %s seconds.', 'markdown-exporter' ), $stats['total_exported'], $stats['total_time'] );

        // Send success response with logs and statistics.
        wp_send_json_success( [
            'progress' => 100,
            'status'   => esc_html__( 'Export completed successfully.', 'markdown-exporter' ),
            'download' => $download_url,
            'logs'     => $logs,
            'stats'    => $stats,
        ] );

        // Reset post data.
        wp_reset_postdata();
    }

    /**
     * Generates Markdown content for a given post, including all post_meta.
     *
     * @param int $post_id The post ID.
     * @return string The formatted Markdown content with front matter.
     */
    private function generate_markdown_content( $post_id ) {
        $post = get_post( $post_id );

        // Initialize front matter array
        $front_matter = [
            'title'  => $post->post_title,
            'date'   => get_the_date( 'Y-m-d', $post_id ),
            'author' => get_the_author_meta( 'display_name', $post->post_author ),
        ];

        // Fetch all post_meta including ACF and Pods.
        $all_meta = get_post_meta( $post_id );

        foreach ( $all_meta as $key => $values ) {
            // Remove keys starting with an underscore if you want to exclude them
            // @TODO - Add option/filter to uncomment the following line to exclude hidden meta fields
            // if ( strpos( $key, '_' ) === 0 ) continue;

            // Sanitize key.
            $sanitized_key = sanitize_text_field( $key );

            // If multiple values exist, store them as an array.
            if ( count( $values ) > 1 ) {
                // Sanitize each value.
                $sanitized_values = array_map( 'maybe_unserialize', $values );
                $front_matter[ $sanitized_key ] = $sanitized_values;
            } else {
                // Single value.
                $sanitized_value = maybe_unserialize( $values[0] );
                $front_matter[ $sanitized_key ] = $sanitized_value;
            }
        }

        // Convert front matter array to YAML manually.
        $yaml_front_matter = $this->array_to_yaml( $front_matter );

        // Convert post content to Markdown.
        $markdown_content = $this->convert_content_to_markdown( $post->post_content );

        // Prepare the final Markdown with front matter.
        $markdown  = "---\n";
        $markdown .= $yaml_front_matter;
        $markdown .= "---\n\n";
        $markdown .= $markdown_content;

        return $markdown;
    }

    /**
     * Converts an associative array to a YAML-formatted string.
     *
     * @param array $array  The associative array to convert.
     * @param int   $indent The current indentation level.
     * 
     * @since  1.0.0
     * @return string The YAML-formatted string.
     */
    private function array_to_yaml( $array, $indent = 0 ) {
        $yaml        = '';
        $indentation = str_repeat( '  ', $indent );

        foreach ( $array as $key => $value ) {
            if ( is_object( $value ) ) {
                // Skip meta values that are encoded as complicated objects
                continue;
            }
            
            // Replace spaces with underscores in keys to avoid YAML issues.
            $key = str_replace( ' ', '_', $key );

            if ( is_array( $value ) ) {
                // Check if associative array.
                if ( $this->is_assoc( $value ) ) {
                    $yaml .= "{$indentation}{$key}:\n";
                    $yaml .= $this->array_to_yaml( $value, $indent + 1 );
                } else {
                    $yaml .= "{$indentation}{$key}:\n";
                    foreach ( $value as $item ) {
                        if ( is_array( $item ) ) {
                            $yaml .= "{$indentation}  -\n";
                            $yaml .= $this->array_to_yaml( $item, $indent + 2 );
                        } else {
                            $yaml .= "{$indentation}  - " . $this->escape_yaml_string( $item ) . "\n";
                        }
                    }
                }
            } else {
                $yaml .= "{$indentation}{$key}: " . $this->escape_yaml_string( $value ) . "\n";
            }
        }

        return $yaml;
    }

    /**
     * Determines if an array is associative.
     *
     * @param array $arr The array to check.
     * 
     * @since  1.0.0
     * @return bool True if associative, false otherwise.
     */
    private function is_assoc( $arr ) {
        if ( [] === $arr ) return false;
        return array_keys( $arr ) !== range( 0, count( $arr ) - 1 );
    }

    /**
     * Escapes a string for safe inclusion in YAML.
     *
     * @param string $string The string to escape.
     * 
     * @since  1.0.0
     * @return string The escaped string.
     */
    private function escape_yaml_string( $string ) {
        // Ensure $string is actually a string.
        if ( ! is_string( $string ) ) {
            // Convert to string.
            $string = (string) $string;
        }

        // If the string contains special characters or starts with a number, enclose it in quotes.
        if ( preg_match( '/[:\-{}\[\],&*#?|\<>=!%@`]/', $string ) || strpos( $string, ' ' ) !== false || is_numeric( $string[0] ) ) {
            // Escape double quotes and backslashes.
            $escaped = addcslashes( $string, "\"\\" );
            return '"' . $escaped . '"';
        }
        return $string;
    }

    /**
     * Converts post content from HTML to Markdown.
     * You can enhance this method by integrating a Markdown library like Parsedown.
     *
     * @param string $content The post content in HTML.
     * 
     * @since  1.0.0
     * @return string The content converted to Markdown.
     */
    private function convert_content_to_markdown( $content ) {
        // Optionally, use Parsedown if available.
        if ( file_exists( plugin_dir_path( __FILE__ ) . 'assets/lib/Parsedown.php' ) ) {
            require_once plugin_dir_path( __FILE__ ) . 'assets/lib/Parsedown.php';
            $parsedown = new Parsedown();
            return $parsedown->text( $content );
        }

        // Fallback: Basic conversion.
        return wpautop( wp_strip_all_tags( $content ) );
    }
}

new Markdown_Exporter();
