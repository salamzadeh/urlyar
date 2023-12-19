=== URLYar URL Shortner ===
Contributors: Salamzadeh
Donate link: http://urlyar.ir/donate
Tags: urlyar, url-shortener, short url, url, shortlink, shorten, shortener, qr, qr code,
Requires at least: 3.0.1
Tested up to: 6.4.0
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
This plugin allows you to generate shortlinks for post/pages using URLYar URL Shorteners
== Description ==


[URLYar URL Shortener](http://salamzadeh.net/plugins/urlyar/ "URLYar URL Shortener") allows you to generate shortlinks for post/pages using URLYar URL Shorteners, with a few additional features.


**Features:**
* Language file as 100% translation now available at wordpress.org
* Localization - Persian translation added (100% complete)
* QR Code Support (using Google Chart API Or URLYar QR API)
* Nice ID links with QR Code (i.e. http://your_site/123.qr)
* Automatic generation of a Short URL/Shortlinks
* *Cached Shortlink* - thus generated only once.
* Choose to generate shortlinks using permalinks or the posts ID (e.g. http://your_site/index.php?p=123).
* Relatively extensive shortlink support
* *Action Hooks available* for other plugins to utilize generated shortlinks (From Ver 3.0 Onwards)
* Nice ID links - http://your_site/123 instead of http://your_site/index.php?p=123
* Shortcode support: Place [urlyar_shortlink] in your article where you want to display the shortened url.
* Append a link to short URL below your post content
* Append a link to short URL below your page content
* Append a link to short URL below your woocommerce product content




**Available Template Tags**

On-demand shortening function:

`<?php urlyar_shorturl('http://www.wpnegar.com', 'urlyar'); ?>`

To show the generated links::

`<?php urlyar_show_shorturl($post); ?>`

Or if WordPress 3.0:

`<?php the_shortlink(); ?>`

http://codex.wordpress.org/Function_Reference/the_shortlink


**Available hooks and filters**

*  urlyar_use_shortlink (Action Hook)
*  urlyar_filter_shortlink (Filter)


**Future Versions and on:**

*  More services/features can be added upon request (https://salamzadeh.net/plugins/urlyar)

**Support via:**

*  http://wordpress.org/tags/url-shortener
*  Contact me via my website ( https://salamzadeh.net/contact/ )

== Installation ==

1. Upload files to your `/wp-content/plugins/` directory (preserve sub-directory structure if applicable)
1. Activate the plugin through the 'Plugins' menu in WordPress

Or

1. Within your WordPress admin, go to plugins > add new
1. Search for "URLYar URL Shortener".
1. Click Install Now for the plugin named "URLYar URL Shortener"

== Frequently Asked Questions ==
= What's Service Provider URL =
Supported service provider is urlyar.ir
you can register and use this services always free from (http://urlyar.ir/?lang=en) url
URLYar is Free and Always Will Be

== Screenshots ==

https://salamzadeh.net/plugins/urlyar

== Changelog ==

Expanded list can be found at: http://salamzadeh.net/plugins/urlyar/release-history
= 1.1.0 =
* Connect to new version API's
* Update to support new version of Wordpress

= 1.0.5 =
* Language file as 100% translation now available at wordpress.org
* Localization - Persian translation added (100% complete)
* Woocommerce Products support

= 1.0 =
* First Public Release
* Added simple validation to options page

= 0.5 =
* Initial Private release.
* supports URLYar

== Upgrade Notice ==

For those upgrading from a version prior to 1.0, please check your settings as Version 1.0 has options code that was re-written.

Read More: http://salamzadeh.net/plugins/urlyar/upgrade-notes
