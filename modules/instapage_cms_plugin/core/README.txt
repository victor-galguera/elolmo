=== Instapage Wordpress Plugin ===
Contributors: marek@instapage
Tags: landing page, lead generation, a/b testing, Instapage, squeeze page, conversion rate optimization, splash page, WordPress landing page, landing page optimization, lead capture page, mobile app landing page, Facebook landing page, sales page
Requires at least: 3.4
Requires PHP: 5.2.4
Tested up to: 5.4.0
Stable tag: 3.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Instapage plugin - the best way for WordPress to seamlessly publish landing pages as a natural extension of your WordPress blog or website.

== Description ==
Join the thousands of users who have downloaded the Instapage plugin for WordPress to seamlessly publish landing pages as a natural extension of your WordPress blog or website. All you have to do is select the 'Push to WordPress' publishing option within Instapage when you're finished with your landing page. (Click the “Installation” tab for detailed upload instructions)

Instapage is the most powerful landing page platform on the market. Ideal for teams and agencies, Instapage has everything you need to build fully customizable, on-brand landing pages.

Instapage is the only platform that offers unlimited domains.

**No other landing page builder offers this level of precision with full design freedom**

Every promotion deserves a great page. One that you’re confident is pixel perfect, exquisitely composed, professional and polished, whether it’s in desktop or mobile form. Our fully customizable builder is intuitive and powerful, so it’s easy to create on-brand, conversion-friendly landing pages

Design without bounds by selecting from over 5,000 fonts and 33,000,000 images to work with. And, our new alignment, distribution and grouping features, ensure your work is perfect in desktop or mobile versions.

**Over 100+ Landing Page Templates to Get You Started**

**Save valuable time and money with our integrations**
Integrate with the most widely used marketing services, like Salesforce, Zapier, Drupal, Autopilot, MailChimp, Google Analytics, AWeber, GoToWebinar, to name just a few.

**Highest ranked support in the industry**

Join 250,000+ businesses who rely on Instapage.

== Installation ==
1. When publishing your landing page from Instapage, choose **WordPress**.
1. Go to Plugins in your WordPress administration, click **Add New**, and search for “**Instapage Wordpress Plugin**”.
1. Now click at **Install Now** button.
1. Click on **Activate** after upload has completed.
1. Go to Settings and choose **Instapage**.
1. Log into your Instapage account.
1. Now you can **Add a New Page**.
1. **Select a Page** from the dropdown that you want to create a URL for (only pages published to WordPress are available).
1. Create a URL, hit publish, and you're ready to promote your landing page!

**Server requirements:**

* PHP Curl extension
* If you get *"Couldn't connect to host"* error during login process, contact your hosting provider and ask for increasing outgoing connection limits.

== Screenshots ==
1. Plugin's dashboard.
2. Add new page / Edit form.
3. Plugin's settings.
4. Instapage app's dashboard.

== Changelog ==
= 3.2.14 (2020-04-30) =
- Bugfix: Problems with OG and meta tags when using alternative loading system (visible when sharing in social media)
- Bugfix: Rare error when using a specific set of plugins on Installed Plugins listing in WordPress admin
- Increased Tested up to: 5.4.0

= 3.2.13 (2020-01-28) =
- Added Custom experiences/dynamic text replacement alternative loading system

= 3.2.12 (2019-12-18) =
- Changed naming from subaccounts to workspaces
- Increased Tested up to: 5.3.1

= 3.2.11 (2019-11-27) =
- Bugfix: Custom experience display issues on WP Engine hosting
- Bugfix: Removing a PHP notice generated during the landing page display path

= 3.2.10 (2019-06-18) =
- Bugfix: CORS issue on Pantheon hosting

= 3.2.9 (2019-06-18) =
- Bugfix: Not working experiences on Pantheon hosting

= 3.2.8 (2019-04-03) =
- Bugfix: SSL related problems leading to not working landing pages

= 3.2.7 (2019-03-14) =
- Safe endpoints for serving pages
- Better detection of not accessible API
- Adjusted request limit during page loading

= 3.2.6 (2018-07-31) =
- Pageserver cookie type error fix
- Block plugin activation unless plugin requirements are met
- Add plugin requirements in plugin description

= 3.2.5 (2018-06-09) =
- Debug log model PHP compatibility fix

= 3.2.4 (2018-05-22) =
- Display an error message on "couldn't connect to host" error
- Error reporting refactor

= 3.2.3 (2018-04-03) =
- Bugfix: SQL Errors in debug log
- Bugfix: CMS Plugins validaiton: Dots are not prohibited in slugs
- Bugfix: Long URI spreads the div over the screen

= 3.2.2 (2018-02-26) =
- Bugfix: CMS plugin loads the page even if additional dashes are in URL
- Bugfix: Drupal7/8 Double slash in domain URL on Edit page
- Bugfix: Error 500 when Drupal 7/8 is installed in subdirectory
- Bugfix: Typo in account disconnecting info

= 3.2.1 (2017-12-21) =
- Bugfix: Plugin multi-click publish & delete button issue
- CMS plugin slug validator optimization

= 3.1.11 (2017-12-07) =
- Plugin requirements & requirements check added
- CMS version added to debug log
- Bugfix: Drupal module does not load
- Bugfix: Drupal 8 Homepage & 404 Page does not load

= 3.1.10 (2017-09-14) =
- Plugin description updated
- 'Tested up to' field updated

= 3.1.9 (2017-07-13) =
- Fixed a bug with wrong variation serving
- Fixed Drupal JS and CSS aggregation problem

= 3.1.8 (2017-05-31) =
- Updated 'Tested up to' field
- Sending additional request headers to prevent caching
- Alternative method of passing Host parameter added
- Additional warning in diagnostic mode

= 3.1.7 (2017-05-09) =
- Fixed slug validator issue
- Installation improvement
- Fixed inaccurate status code issue
- Fixed problem with detection connected subaccount
- Fixed problem with email input error in login form

= 3.1.6 (2017-02-13) =
- Improved dynamic text replacement

= 3.1.4 (2017-02-01) =
- Fixed javascript errors on plugin's settings page
- Improved plugin installation
- Changed coding style

= 3.1.2 (2016-12-16) =
- Improved signing in into plugin

= 3.1.1 (2016-12-15) =
- Improved installation on Drupal
- Changed workflow for unpublish page via plugin
- Changed page fetching endpoint

= 3.1.0 (2016-11-28) =
- Improved compatibility with plugins use knockout.js
- Improved handling big requests
- Improved displaying 404 page header status
- Changed fetch landing pages

= 3.0.9 (2016-11-14) =
- Fixed problem with broken dashboard
- Fixed problem with not displayed page listing
- Improved auto-migration
- Improved stability

= 3.0.8 (2016-11-08) =
- Improved compatibility with other plugins
- Workaround for displaying plugin's link in sidebar
- Increased max length request limit

= 3.0.7 (2016-11-04) =
- Improved fetching big requests

= 3.0.6 (2016-11-03) =
- Improved slug detection method

= 3.0.5 (2016-11-03) =
- Improved fetching more than 50 pages
- Improved URL parsing
- Improved token validation
- Fixed publish link on dashboard

= 3.0.4 (2016-11-01) =
- unnecessary scripts removed from dashboard

= 3.0.3 (2016-11-01) =
- Compatibility with PHP >= 4.3
- Getting all pages ready for push
- Better parsing URL for homepage and 404 pages

= 3.0.2 (2016-10-31) =
- Cross origin proxy value fix

= 3.0.1 (2016-10-31) =
- PHP 5 compatibility fix
- DB Datetime fix

= 3.0 (2016-10-31) =
Stable version.
