=== Glue for Yoast SEO & AMP ===
Contributors: joostdevalk
Tags: AMP, SEO
Requires at least: 4.8
Tested up to: 4.9.5
Stable tag: 0.4.3
Requires PHP: 5.2.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin makes sure the default WordPress AMP plugin uses the proper Yoast SEO metadata and allows modification of the AMP page design.

== Description ==

Google is currently working on the "Accelerated Mobile Pages" or AMP project. The [WordPress AMP plugin](https://wordpress.org/plugins/amp/) provides the technical implementation of the AMP specifications. This makes sure that your pages contain valid AMP code.

The Yoast SEO: AMP Glue plugin integrates [Yoast SEO](https://wordpress.org/plugins/wordpress-seo/) into your AMP pages. This makes sure your meta-data is implemented correctly.

Next to the technical SEO improvements, the Yoast SEO: AMP Glue plugin also provides a simple way to customize your AMP pages.
It adds rudimental styling in the form of colors and link styles, so your AMP pages can maintain the feeling your main pages have.
To change your AMP page design, go to SEO -> AMP, and look at the design tab.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/yoast-seo-amp` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Go to SEO -> AMP to change your design and enable custom post types.
1. You're done.

== Screenshots ==

1. Example AMP page, design changed with this plugin.
2. Post type support in the plugin.
3. Design settings in the plugin.

== Changelog ==

= 0.4.3 =
* Bugfixes:
    * Fixes a fatal error in combination with `AMP for WordPress` version 0.7.0. Props [Ryan Kienstra](https://github.com/kienstra).

= 0.4.2 =
* Bugfixes:
    * Reverts the canonical removal.

= 0.4.1 =
* Bugfixes:
    * Fix styling of design tab.

= 0.4.0 =
* Bugfixes:
    * Removed page from post-type list to avoid unwanted canonical link.

* Enhancements:
    * Removed canonical feature because it is being handled by the AMP plugin.
    * Removed sanitizations which are already being done by the AMP plugin.
    * Added a check for Monster Insights analytics implementation and disables our implementation if present.
    * Added class selector implementation for AMP 0.4.x compatibility.

= 0.3.3 =
* Bugfixes:
    * Fixes bug where AMP was only activated for the first post type in the list.
    * Made sure that the function is not declared multiple times.

= 0.3.2 =
* Bugfixes:
    * Fixed underline setting that wasn't working.
    * Added screenshots to plugin page.

= 0.3.1 =
* Bugfixes:
    * Fixed bug where featured image wouldn't be used properly anymore.
    * Fixed bug where CSS in Extra CSS field could be wrongly escaped.
    * Fixed bug where wrong hook was used to `add_post_type_support`, causing integration issues.
    * Fixed bug where post type settings wouldn't save properly.
* Enhancement:
    * Added some more escaping to color picker functionality.
    * Made sure no notice is thrown on frontend when post type setting isn't available.

= 0.3 =
* Split the plugin into several classes.
* Added a settings page, found under SEO -> AMP
* This new settings page has:
    * A post types settings tab;
    * A design settings tab;
    * An analytics integration tab.
* Added sanitization functions that further clean up AMP output to make sure more pages pass validation.
* Added a default image (settable on the design tab) to use when a post has no image. This because the image in the JSON+LD output is required by Google.
* The plugin now automatically enables AMP GA tracking when GA by Yoast is enabled, but also allows you to add custom tracking.

= 0.1 =
* Initial version.
