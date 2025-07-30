=== Signalfire Auto Featured ===
Contributors: signalfire
Tags: featured image, auto, automatic, image, post
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Automatically sets the first image in post content as the featured image if none is already set.

== Description ==

Signalfire Auto Featured is a lightweight WordPress plugin that automatically sets featured images for your posts. When you publish a post without a featured image, the plugin will scan the post content for the first image and set it as the featured image.

**Key Features:**

* Automatically detects and sets featured images from post content
* Configurable post type support (posts, pages, custom post types)
* Fallback image option when no image is found in content
* Works with WordPress media library images
* Clean and simple settings interface
* Multisite compatible
* Translation ready

**How it works:**

1. When a post is saved/published, the plugin checks if a featured image is already set
2. If no featured image exists, it scans the post content for images
3. The first image found is automatically set as the featured image
4. If no images are found and a fallback image is configured, the fallback image is used
5. The plugin only processes enabled post types as configured in settings

**Perfect for:**

* Blogs with lots of image content
* News sites and magazines
* Content migration scenarios
* Automated content workflows
* Sites where authors frequently forget to set featured images

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/signalfire-auto-featured` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->Auto Featured screen to configure the plugin
4. Select which post types should have auto featured images enabled
5. Optionally set a fallback image to use when no image is found in post content

== Frequently Asked Questions ==

= Does this plugin work with custom post types? =

Yes! You can enable auto featured image functionality for any public post type through the plugin settings.

= Will this override existing featured images? =

No, the plugin only sets featured images for posts that don't already have one. Existing featured images are never changed.

= What image formats are supported? =

The plugin works with any image format supported by WordPress (JPEG, PNG, GIF, WebP, etc.) that's uploaded to the media library.

= Does this work with images from external sources? =

The plugin works best with images uploaded to your WordPress media library. External images may not be detected depending on how they're embedded.

= Can I disable the plugin for specific posts? =

The plugin only processes post types that are enabled in the settings. You can disable it per post type, but not per individual post.

= Is this plugin compatible with page builders? =

The plugin scans HTML content for `<img>` tags, so it should work with most page builders that output standard HTML image tags.

== Screenshots ==

1. Plugin settings page showing post type selection and fallback image options
2. Example of automatic featured image detection in post editor

== Changelog ==

= 1.0.0 =
* Initial release
* Automatic featured image detection from post content
* Configurable post type support
* Fallback image functionality
* WordPress Settings API integration
* Translation ready
* Multisite support

== Upgrade Notice ==

= 1.0.0 =
Initial release of Signalfire Auto Featured plugin.

== Developer Information ==

This plugin follows WordPress coding standards and best practices:

* Secure coding practices with proper sanitization and escaping
* Translation ready with complete .pot file
* Follows WordPress Plugin Directory guidelines
* Clean uninstall process
* Multisite compatible

**Hooks and Filters:**

The plugin currently doesn't provide custom hooks, but this may be added in future versions based on user feedback.

**Support:**

For support questions, please use the WordPress.org support forums for this plugin.