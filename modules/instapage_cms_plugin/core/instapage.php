<?php

/*
Plugin Name: Instapage Plugin
Description: The best way for WordPress to seamlessly publish landing pages as a natural extension of your website.
Version: 3.2.14
Requires PHP: 5.2.4
Plugin URI: https://instapage.com/
Author: Instapage
Author URI: https://instapage.com/
License: GPLv2
*/

define('INSTAPAGE_PLUGIN_PATH', dirname(__FILE__));
define('INSTAPAGE_PLUGIN_FILE', __FILE__);
define('INSTAPAGE_SUPPORT_EMAIL', 'help@instapage.com');
define('MINIUM_PHP_VERSION', '5.2.4');

/**
 * @var array $consts Holds constant names to be defined with their default values - if not already defined or found in `$_ENV`
 */
$consts = array(
  'INSTAPAGE_ENTERPRISE_ENDPOINT' => 'http://pageserve.co',
  'INSTAPAGE_PROXY_ENDPOINT' => 'http://app.instapage.com',
  'INSTAPAGE_APP_ENDPOINT' => 'http://app.instapage.com/api/plugin'
);

foreach ($consts as $key => $value) {
  if (!defined($key)) {
    define($key, (isset($_ENV[$key]) && !empty($_ENV[$key])) ? $_ENV[$key] : $value);
  }
}

require_once(INSTAPAGE_PLUGIN_PATH . '/connectors/InstapageCmsPluginConnector.php');
require_once(INSTAPAGE_PLUGIN_PATH . '/InstapageCmsPluginHelper.php');
require_once(INSTAPAGE_PLUGIN_PATH . '/models/InstapageCmsPluginDBModel.php');
require_once(INSTAPAGE_PLUGIN_PATH . '/models/InstapageCmsPluginAPIModel.php');
require_once(INSTAPAGE_PLUGIN_PATH . '/models/InstapageCmsPluginPageModel.php');
require_once(INSTAPAGE_PLUGIN_PATH . '/models/InstapageCmsPluginServicesModel.php');
require_once(INSTAPAGE_PLUGIN_PATH . '/models/InstapageCmsPluginDebugLogModel.php');
require_once(INSTAPAGE_PLUGIN_PATH . '/models/InstapageCmsPluginViewModel.php');
require_once(INSTAPAGE_PLUGIN_PATH . '/models/InstapageCmsPluginSubaccountModel.php');
require_once(INSTAPAGE_PLUGIN_PATH . '/InstapageCmsPluginAjaxController.php');

InstapageCmsPluginConnector::initPlugin();
