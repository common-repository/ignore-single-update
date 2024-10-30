=== Ignore Or Disable Plugin Update ===
Contributors: Jeffinho2016,jfgmedia
Tags: ignore, disable, update, manage, updates
Requires at least: 5.0
Tested up to: 6.6
Stable tag: 1.2.1
Requires PHP: 7.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Allows to ignore a single plugin update for a certain number of days, or until its next version.

== Description ==

There are cases where we might not want to update a plugin right away.
<ul>
<li>It can be a major version jump, possibly with edge-case bugs or deprecated features.</li>
<li>It can be a minor version jump, which doesn't always justify a diff to make sure custom hooks still work.</li>
<li>It can just be because we want to wait a few days, to let other people confirm that everything works correctly (or to check that nobody is screaming "broken site" in the support forum).</li>
</ul>
<p>Whatever the reason, it could be helpful to temporarily hide these updates. Unlike other update management plugins, Ignore Or Disable Plugin Update Update works on a version-per-version basis.</p>
<p>Ignore Or Disable Plugin Update adds an "Ignore update" link in the WP Plugins listing page, and one the WP Updates page.</p>
<p>You will be able to:</p>
<ul>
<li>Ignore a plugin update for a chosen amount of days</li>
<li>Ignore a plugin update until the next version</li>
<li>Permanently ignore all future updates for any plugin</li>
<li>Unignore plugin updates at any time by going in the "Plugins"->"Ignored Updates" WP menu</li>
<li>Control admin notifications</li>
<li>[Premium] Automatically delay the apparition of plugin updates</li>
<li>[Premium] Integrate WordFence to get security warnings on your installed plugins (WordFence plugin and plan not required)</li>
<li>[Premium] See warnings from the "Plugins" and "Updates" pages</li>
<li>[Premium] Automatically unignore vulnerable versions</li>
<li>[Premium] Prevent ignoring vulnerable versions</li>
<li>[Premium] Get informed in real time by email</li>
</ul>

= Multisite =
The free version of the plugin is **not** compatible with multisite. For multisite compatibility, you will need the Premium Business Plan.

= Plugin auto-updates =
Our plugin will respect your plugin auto-update settings. You won't be able to ignore specific plugin versions if auto-updates are activated.

= Third Party Services =
We use the services of [Freemius,Inc](https://freemius.com/) as a Merchant of Record to handle payment, licensing, and billing information.
Their privacy policy can be consulted [here](https://freemius.com/privacy/).

== Installation ==

1. Visit the Plugins page within your dashboard and select "Add New"
2. Search for "Ignore Or Disable Plugin Update"
3. Click "Install"

== Screenshots ==

1. The "Ignore update" link on the Plugin listing page
2. The "Ignore update" link on the Updates page
3. The prompt asking for a number of days
4. The "Ignored Updates" management page

== Upgrade Notice ==
Not available at the moment

== Frequently Asked Questions ==

= Will this plugin slow down my site? =

It will have no impact on site speed whatsoever. The plugin only launches for users that have the ability to update plugins.

== Changelog ==

= 1.2.1 =
* Fix: Regression that was preventing the "Stop Ignoring" buttons from working properly

= 1.2 =
* New: Free 30-day trial of Premium Supporter features
* Fix: Clean up list of ignored updates when a plugin was removed from the WP repository
* Improvement: Better escaping of html attributes in accordance to WP coding standards



= 1.1.7 =
* Fix: PHP warning when a plugin had an ignore update, but then the plugin was deleted
* [Premium] Fix: In some edge case, Autopilot would not display the correct update type
* Freemius SDK updated to 2.8.1

= 1.1.6 =
* Fix: The wrong ignored count could be shown when manually updating a plugin via FTP
* Freemius SDK updated to 2.7.4

= 1.1.5 =
* Fix: In some edge case, the list of ignored plugin updates would not display the good ignored plugin version
* Tested up to WordPress 6.6
* Minimum PHP version bumped to 7.2
* Freemius SDK updated to 2.7.3


= 1.1.3 =
* Freemius SDK updated to 2.7.0

= 1.1.2 =
* Improvement: Some strings were rephrased to improve their translatability

= 1.1.1 =
* Fix: Typos in some translatable strings

= 1.1 =
* Fix: some translatable strings that couldn't be parsed by WP
* Added JFG Media as author and contributor
* Tested up to 6.4.2

= 1.0 =
* Initial Release