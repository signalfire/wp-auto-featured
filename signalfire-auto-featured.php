<?php
/**
 * Plugin Name: Signalfire Auto Featured
 * Plugin URI: https://wordpress.org/plugins/signalfire-auto-featured/
 * Description: Automatically sets the first image in post content as the featured image if none is already set.
 * Version: 1.0.0
 * Author: Signalfire
 * Author URI: https://signalfire.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: signalfire-auto-featured
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 * Network: true
 */

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SAF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SAF_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('SAF_VERSION', '1.0.0');

class SignalfireAutoFeatured {
    
    private $option_name = 'saf_settings';
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('save_post', array($this, 'auto_set_featured_image'), 10, 2);
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'settings_init'));
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Plugin initialization
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('signalfire-auto-featured', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function auto_set_featured_image($post_id, $post) {
        // Skip if this is an autosave or revision
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }
        
        // Check if post type is enabled
        $settings = get_option($this->option_name, array());
        $enabled_post_types = isset($settings['enabled_post_types']) ? $settings['enabled_post_types'] : array();
        
        if (!in_array($post->post_type, $enabled_post_types)) {
            return;
        }
        
        // Skip if featured image is already set
        if (has_post_thumbnail($post_id)) {
            return;
        }
        
        // Extract first image from post content
        $image_id = $this->extract_first_image($post->post_content);
        
        if ($image_id) {
            set_post_thumbnail($post_id, $image_id);
        } else {
            // Use fallback image if no image found
            $fallback_image_id = isset($settings['fallback_image']) ? $settings['fallback_image'] : '';
            if ($fallback_image_id) {
                set_post_thumbnail($post_id, $fallback_image_id);
            }
        }
    }
    
    private function extract_first_image($content) {
        // Parse content for images
        preg_match_all('/<img[^>]+>/i', $content, $matches);
        
        if (empty($matches[0])) {
            return false;
        }
        
        foreach ($matches[0] as $img) {
            // Extract src attribute
            if (preg_match('/src=["\']([^"\']+)["\']/', $img, $src_match)) {
                $image_url = $src_match[1];
                
                // Try to get attachment ID from URL
                $attachment_id = attachment_url_to_postid($image_url);
                if ($attachment_id) {
                    return $attachment_id;
                }
                
            }
            
            // Check for wp-image-{id} class
            if (preg_match('/wp-image-(\d+)/', $img, $class_match)) {
                return intval($class_match[1]);
            }
        }
        
        return false;
    }
    
    
    
    public function add_admin_menu() {
        add_options_page(
            __('Auto Featured Settings', 'signalfire-auto-featured'),
            __('Auto Featured', 'signalfire-auto-featured'),
            'manage_options',
            'signalfire-auto-featured',
            array($this, 'settings_page')
        );
    }
    
    public function settings_init() {
        register_setting('saf_settings_group', $this->option_name, array($this, 'sanitize_settings'));
        
        add_settings_section(
            'saf_main_section',
            __('Auto Featured Image Settings', 'signalfire-auto-featured'),
            array($this, 'main_section_callback'),
            'signalfire-auto-featured'
        );
        
        add_settings_field(
            'enabled_post_types',
            __('Enable for Post Types', 'signalfire-auto-featured'),
            array($this, 'enabled_post_types_callback'),
            'signalfire-auto-featured',
            'saf_main_section'
        );
        
        add_settings_field(
            'fallback_image',
            __('Fallback Image', 'signalfire-auto-featured'),
            array($this, 'fallback_image_callback'),
            'signalfire-auto-featured',
            'saf_main_section'
        );
    }
    
    public function main_section_callback() {
        echo '<p>' . esc_html__('Configure the auto featured image functionality.', 'signalfire-auto-featured') . '</p>';
    }
    
    public function enabled_post_types_callback() {
        $settings = get_option($this->option_name, array());
        $enabled_post_types = isset($settings['enabled_post_types']) ? $settings['enabled_post_types'] : array();
        $post_types = get_post_types(array('public' => true), 'objects');
        
        echo '<fieldset>';
        foreach ($post_types as $post_type) {
            printf(
                '<label><input type="checkbox" name="%s[enabled_post_types][]" value="%s" %s> %s</label><br>',
                esc_attr($this->option_name),
                esc_attr($post_type->name),
                checked(in_array($post_type->name, $enabled_post_types), true, false),
                esc_html($post_type->label)
            );
        }
        echo '</fieldset>';
        echo '<p class="description">' . esc_html__('Select which post types should have auto featured images enabled.', 'signalfire-auto-featured') . '</p>';
    }
    
    public function fallback_image_callback() {
        $settings = get_option($this->option_name, array());
        $fallback_image_id = isset($settings['fallback_image']) ? $settings['fallback_image'] : '';
        
        echo '<div class="saf-image-upload">';
        echo '<input type="hidden" name="' . esc_attr($this->option_name) . '[fallback_image]" id="fallback_image_id" value="' . esc_attr($fallback_image_id) . '">';
        echo '<div id="fallback_image_preview">';
        
        if ($fallback_image_id) {
            if (wp_get_attachment_image($fallback_image_id, 'medium')) {
                echo wp_get_attachment_image($fallback_image_id, 'medium', false, array('style' => 'max-width: 300px; height: auto;'));
                echo '<br><button type="button" class="button" id="remove_fallback_image">' . esc_html__('Remove Image', 'signalfire-auto-featured') . '</button>';
            }
        }
        
        echo '</div>';
        echo '<button type="button" class="button" id="upload_fallback_image">' . esc_html__('Select Fallback Image', 'signalfire-auto-featured') . '</button>';
        echo '</div>';
        echo '<p class="description">' . esc_html__('This image will be used as the featured image when no image is found in the post content.', 'signalfire-auto-featured') . '</p>';
        
        // Add media uploader script
        $this->add_media_uploader_script();
    }
    
    private function add_media_uploader_script() {
        ?>
        <script>
        jQuery(document).ready(function($) {
            var mediaUploader;
            
            $('#upload_fallback_image').click(function(e) {
                e.preventDefault();
                
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                
                mediaUploader = wp.media({
                    title: '<?php echo esc_js(__('Select Fallback Image', 'signalfire-auto-featured')); ?>',
                    button: {
                        text: '<?php echo esc_js(__('Select Image', 'signalfire-auto-featured')); ?>'
                    },
                    multiple: false
                });
                
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#fallback_image_id').val(attachment.id);
                    
                    // Reload the page to show the image using wp_get_attachment_image()
                    location.reload();
                });
                
                mediaUploader.open();
            });
            
            $(document).on('click', '#remove_fallback_image', function() {
                $('#fallback_image_id').val('');
                $('#fallback_image_preview').html('');
            });
        });
        </script>
        <?php
    }
    
    public function sanitize_settings($input) {
        // Verify nonce
        $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';
        if (!$nonce || !wp_verify_nonce($nonce, 'saf_settings_group-options')) {
            wp_die(esc_html__('Security check failed.', 'signalfire-auto-featured'));
        }
        
        $sanitized = array();
        
        if (isset($input['enabled_post_types']) && is_array($input['enabled_post_types'])) {
            $sanitized['enabled_post_types'] = array_map('sanitize_key', $input['enabled_post_types']);
        } else {
            $sanitized['enabled_post_types'] = array();
        }
        
        if (isset($input['fallback_image'])) {
            $sanitized['fallback_image'] = absint($input['fallback_image']);
        }
        
        return $sanitized;
    }
    
    public function settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'signalfire-auto-featured'));
        }
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Auto Featured Image Settings', 'signalfire-auto-featured'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('saf_settings_group');
                do_settings_sections('signalfire-auto-featured');
                submit_button();
                ?>
            </form>
        </div>
        <?php
        
        // Enqueue media uploader
        wp_enqueue_media();
    }
    
    public function activate() {
        // Set default options
        $default_options = array(
            'enabled_post_types' => array('post'),
            'fallback_image' => ''
        );
        
        add_option($this->option_name, $default_options);
    }
    
    public function deactivate() {
        // Cleanup if needed
    }
}

// Initialize the plugin
new SignalfireAutoFeatured();