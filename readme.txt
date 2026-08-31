=== Plugin Name ===
Contributors: Broadstreet
Tags: broadstreet,local,publishers,hyperlocal,independent,news,business,directory
Requires at least: 3.0
Tested up to: 6.9
Stable tag: 1.53.3

Integrate Broadstreet adserving power into your site.

== Description ==

For [Broadstreet Ad Manager](https://broadstreetads.com/) users.

Integrate Broadstreet's Ad Manager for Hyperlocal News, Magazine, and Niche
Publishers into your Broadstreet site.

* Install Broadstreet configuration with best practices automatically
* Drop zones into widget areas or via shortcode
* Place zones in-story ad-hoc or after certain paragraphs
* Automatically send category names as keywords to the adserver
* Restrict ads from appearing on certain pages or categories

**How to:**

1. Install the plugin
2. Go to Settings->Broadstreet
3. Enter your Access Token and confirm that it's valid (we'll check automatically)
4. Go to Appearance->Widgets, and use the new 'Broadstreet Ad Zone' widget

To learn more about Broadstreet, and how it can help you as a local publisher,
send an email to [frontdesk@broadstreetads.com](frontdesk@broadstreetads.com).

**Developer Hooks**

The plugin provides filters for developers to customize behavior:

`broadstreet_ad_keywords` - Filter the keywords sent to the ad server for targeting

Example usage:

```php
add_filter( 'broadstreet_ad_keywords', function( $keywords ) {
    // Add logged-in status
    if ( is_user_logged_in() ) {
        $keywords[] = 'user_logged_in';
    }

    // Add custom taxonomy terms
    if ( is_singular() ) {
        $terms = get_the_terms( get_the_ID(), 'my_custom_taxonomy' );
        if ( $terms && ! is_wp_error( $terms ) ) {
            foreach ( $terms as $term ) {
                $keywords[] = $term->name;
            }
        }
    }

    return $keywords;
} );
```

**How can I report security bugs?**

You can report security bugs through the Patchstack Vulnerability Disclosure
Program. The Patchstack team help validate, triage and handle any security
vulnerabilities.

[Report a security vulnerability.](https://patchstack.com/database/vdp/broadstreet)

== Fix Log ==

* 1.2.3: Fixed image upload bug affecting minority of WP installations
* 1.8.1: Fixed asset base URL making the settings page ugly
* 1.8.1: Fixed excerpt filter (special thanks Justin)
