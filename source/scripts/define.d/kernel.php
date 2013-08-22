<?php
/**
 * This script was automatically generated. Instead of modifying it directly,
 * the best practice is to modify the corresponding <config> element in the
 * Typeframe registry and regenerate this script with the tfadmin.php tool.
 *
 * The primary purpose of this script is to document the constants defined in
 * the application registry so they are discoverable in IDEs.
 */
 

/**
 * Web Site Name/Title (default: 'Untitled Web Site')
 */
define('TYPEF_TITLE', Typeframe::Registry()->getConfigValue('TYPEF_TITLE'));

/**
 * Default database character set (default: 'utf8')
 */
define('TYPEF_DEFAULT_CHARSET', Typeframe::Registry()->getConfigValue('TYPEF_DEFAULT_CHARSET'));

/**
 * Default database collation (default: 'utf8_unicode_ci')
 */
define('TYPEF_DEFAULT_COLLATION', Typeframe::Registry()->getConfigValue('TYPEF_DEFAULT_COLLATION'));

/**
 * Default date format (default: 'n-j-Y')
 */
define('TYPEF_DEFAULT_DATE_FORMAT', Typeframe::Registry()->getConfigValue('TYPEF_DEFAULT_DATE_FORMAT'));

/**
 * Default date/time format (default: 'n-j-Y g:i A')
 */
define('TYPEF_DEFAULT_DATE_TIME_FORMAT', Typeframe::Registry()->getConfigValue('TYPEF_DEFAULT_DATE_TIME_FORMAT'));

/**
 * Default date/time format (with seconds) (default: 'n-j-Y g:i:s A')
 */
define('TYPEF_DEFAULT_DATE_TIME_FORMAT_WITH_SECONDS', Typeframe::Registry()->getConfigValue('TYPEF_DEFAULT_DATE_TIME_FORMAT_WITH_SECONDS'));

/**
 * Time Zone (leave blank to use server setting) (default: '')
 */
define('TYPEF_TIMEZONE', Typeframe::Registry()->getConfigValue('TYPEF_TIMEZONE'));

/**
 * Default Editor (will try CKEditor if blank) (default: '')
 */
define('TYPEF_DEFAULT_EDITOR', Typeframe::Registry()->getConfigValue('TYPEF_DEFAULT_EDITOR'));

/**
 * Default Redirect Behavior (default: '1')
 */
define('TYPEF_DEFAULT_REDIRECT_TIME', Typeframe::Registry()->getConfigValue('TYPEF_DEFAULT_REDIRECT_TIME'));

/**
 * Try to use minified javascript versions (default: '1')
 */
define('TYPEF_JS_MINIFIED', Typeframe::Registry()->getConfigValue('TYPEF_JS_MINIFIED'));

/**
 * Trigger errors on legacy class loads (recommended for development only) (default: '0')
 */
define('TYPEF_DEBUG_LEGACY_CLASS_ERRORS', Typeframe::Registry()->getConfigValue('TYPEF_DEBUG_LEGACY_CLASS_ERRORS'));

/**
 * Display debug info on web pages (default: 'none')
 */
define('TYPEF_DEBUG', Typeframe::Registry()->getConfigValue('TYPEF_DEBUG'));

/**
 * Sender's address for automated messages (default: '')
 */
define('TYPEF_MAILER_SENDER', Typeframe::Registry()->getConfigValue('TYPEF_MAILER_SENDER'));

/**
 * Sender's name for automated messages (default: '')
 */
define('TYPEF_MAILER_NAME', Typeframe::Registry()->getConfigValue('TYPEF_MAILER_NAME'));

/**
 * Mailer method (default: 'mail')
 */
define('TYPEF_MAILER_METHOD', Typeframe::Registry()->getConfigValue('TYPEF_MAILER_METHOD'));

/**
 * Host name (for SMTP connection method) (default: '')
 */
define('TYPEF_MAILER_HOST', Typeframe::Registry()->getConfigValue('TYPEF_MAILER_HOST'));

/**
 * Use authentication (for SMTP connection method) (default: '0')
 */
define('TYPEF_MAILER_AUTH', Typeframe::Registry()->getConfigValue('TYPEF_MAILER_AUTH'));

/**
 * User name (for SMTP authentication) (default: '')
 */
define('TYPEF_MAILER_USERNAME', Typeframe::Registry()->getConfigValue('TYPEF_MAILER_USERNAME'));

/**
 * Password (for SMTP authentication) (default: '')
 */
define('TYPEF_MAILER_PASSWORD', Typeframe::Registry()->getConfigValue('TYPEF_MAILER_PASSWORD'));

/**
 * Security (for SMTP authentication) (default: '')
 */
define('TYPEF_MAILER_SECURE', Typeframe::Registry()->getConfigValue('TYPEF_MAILER_SECURE'));

/**
 * Site skin (default: 'default')
 */
define('TYPEF_SITE_SKIN', Typeframe::Registry()->getConfigValue('TYPEF_SITE_SKIN'));

/**
 * Admin skin (default: 'default')
 */
define('TYPEF_ADMIN_SKIN', Typeframe::Registry()->getConfigValue('TYPEF_ADMIN_SKIN'));

/**
 * Enable mobile skins (default: '0')
 */
define('TYPEF_USE_MOBILE_SKINS', Typeframe::Registry()->getConfigValue('TYPEF_USE_MOBILE_SKINS'));

/**
 * Mobile site skin (default: 'default')
 */
define('TYPEF_MOBILE_SITE_SKIN', Typeframe::Registry()->getConfigValue('TYPEF_MOBILE_SITE_SKIN'));

/**
 * Mobile admin skin (default: 'default')
 */
define('TYPEF_MOBILE_ADMIN_SKIN', Typeframe::Registry()->getConfigValue('TYPEF_MOBILE_ADMIN_SKIN'));

/**
 * Try to use LESS versions of registered stylesheets (default: '1')
 */
define('TYPEF_LESS', Typeframe::Registry()->getConfigValue('TYPEF_LESS'));
