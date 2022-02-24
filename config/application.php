<?php
/**
 * Your base production configuration goes in this file. Environment-specific
 * overrides go in their respective config/environments/{{WP_ENV}}.php file.
 *
 * A good default policy is to deviate from the production config as little as
 * possible. Try to define as much of your configuration in this file as you
 * can.
 */

use Roots\WPConfig\Config;
use function Env\env;

/**
 * Directory containing all of the site's files
 *
 * @var string
 */
$root_dir = dirname(__DIR__);

/**
 * Document Root
 *
 * @var string
 */
$webroot_dir = $root_dir . '/web';

/**
 * Use Dotenv to set required environment variables and load .env file in root
 * .env.local will override .env if it exists
 */
$env_files = file_exists($root_dir . '/.env.local')
    ? ['.env', '.env.local']
    : ['.env'];

$dotenv = Dotenv\Dotenv::createUnsafeImmutable($root_dir, $env_files, false);
if (file_exists($root_dir . '/.env')) {
    $dotenv->load();
    $dotenv->required(['WP_HOME', 'WP_SITEURL']);
    if (!env('DATABASE_URL')) {
        $dotenv->required(['DB_NAME', 'DB_USER', 'DB_PASSWORD']);
    }
}

/**
 * Set up our global environment constant and load its config first
 * Default: production
 */
define('WP_ENV', env('WP_ENV') ?: 'production');

/**
 * URLs
 */
Config::define('WP_HOME', env('WP_HOME'));
Config::define('WP_SITEURL', env('WP_SITEURL'));

/**
 * Custom Content Directory
 */
Config::define('CONTENT_DIR', '/app');
Config::define('WP_CONTENT_DIR', $webroot_dir . Config::get('CONTENT_DIR'));
Config::define('WP_CONTENT_URL', Config::get('WP_HOME') . Config::get('CONTENT_DIR'));

/**
 * DB settings
 */
Config::define('DB_NAME', env('DB_NAME'));
Config::define('DB_USER', env('DB_USER'));
Config::define('DB_PASSWORD', env('DB_PASSWORD'));
Config::define('DB_HOST', env('DB_HOST') ?: 'localhost');
Config::define('DB_CHARSET', 'utf8mb4');
Config::define('DB_COLLATE', '');
$table_prefix = env('DB_PREFIX') ?: 'wp_';

if (env('DATABASE_URL')) {
    $dsn = (object) parse_url(env('DATABASE_URL'));

    Config::define('DB_NAME', substr($dsn->path, 1));
    Config::define('DB_USER', $dsn->user);
    Config::define('DB_PASSWORD', isset($dsn->pass) ? $dsn->pass : null);
    Config::define('DB_HOST', isset($dsn->port) ? "{$dsn->host}:{$dsn->port}" : $dsn->host);
}

/**
 * Authentication Unique Keys and Salts
 */
Config::define('AUTH_KEY', env('AUTH_KEY'));
Config::define('SECURE_AUTH_KEY', env('SECURE_AUTH_KEY'));
Config::define('LOGGED_IN_KEY', env('LOGGED_IN_KEY'));
Config::define('NONCE_KEY', env('NONCE_KEY'));
Config::define('AUTH_SALT', env('AUTH_SALT'));
Config::define('SECURE_AUTH_SALT', env('SECURE_AUTH_SALT'));
Config::define('LOGGED_IN_SALT', env('LOGGED_IN_SALT'));
Config::define('NONCE_SALT', env('NONCE_SALT'));

/**
 * Custom Settings
 */
Config::define('AUTOMATIC_UPDATER_DISABLED', true);
Config::define('DISABLE_WP_CRON', env('DISABLE_WP_CRON') ?: false);
// Disable the plugin and theme file editor in the admin
Config::define('DISALLOW_FILE_EDIT', true);
// Disable plugin and theme updates and installation from the admin
Config::define('DISALLOW_FILE_MODS', true);
// Limit the number of post revisions that Wordpress stores (true (default WP): store every revision)
Config::define('WP_POST_REVISIONS', env('WP_POST_REVISIONS') ?: true);

/**
 * S3 Assets 
 * amazon-s3-and-cloudfront-pro
 * Docs: https://deliciousbrains.com/wp-offload-media/doc/settings-constants/
 */
define( 'AS3CF_SETTINGS', serialize( array(
    'provider' => env('PROVIDER') || 'aws',
    'access-key-id' => env('S3_ACCESS_KEY_ID'),
    'secret-access-key' => env('S3_SECRET_ACCESS_KEY'),
    'bucket' => env('S3_BUCKET'),
    'copy-to-s3' => env('COPY_TO_S3') || true,
    'use-yearmonth-folders' => true,
    'object-versioning' => true,
    'serve-from-s3' => env('SERVE_FROM_S3') || true,  
    'force-https' => true,
    'remove-local-file' => env('REMOVE_LOCAL_FILE') || true, // Remove the local file version once offloaded to bucket
) ) );

/**
 * SMTP Configuration
 * Set the following constants in wp-config.php
 * These should be added somewhere BEFORE the
 * constant ABSPATH is defined.
 */
Config::define( 'WP_SMTP_HOST', env('SMTP_HOST') );    // The hostname of the mail server | 'smtp.example.com'
Config::define( 'WP_SMTP_PORT', env('SMTP_PORT') );    // SMTP port number - likely to be 25, 465 or 587 | '25'
Config::define( 'WP_SMTP_USER', env('SMTP_USER') );    // Username to use for SMTP authentication | 'user@example.com' 
Config::define( 'WP_SMTP_PASS', env('SMTP_PASS') );    // Password to use for SMTP authentication | 'smtp password'
Config::define( 'WP_SMTP_SECURE', env('SMTP_SECURE') );// Encryption system to use - ssl or tls | 'tls'
Config::define( 'WP_SMTP_AUTH', env('SMTP_AUTH') );    // Use SMTP authentication (true|false) | true
Config::define( 'WP_SMTP_FROM', env('SMTP_FROM') );    // SMTP From email address | 'website@example.com'
Config::define( 'WP_SMTP_NAME', env('SMTP_NAME') );    // SMTP From name | 'e.g Website Name'
Config::define( 'WP_SMTP_DEBUG', env('SMTP_DEBUG') );  // for debugging purposes only set to 1 or 2 | 0

/**
 * Debugging Settings
 */
Config::define('WP_DEBUG_DISPLAY', false);
Config::define('WP_DEBUG_LOG', false);
Config::define('SCRIPT_DEBUG', false);
ini_set('display_errors', '0');

/**
 * Custom Configurations
 */
define( 'WP_DEFAULT_THEME', env('WP_DEFAULT_THEME') ?: 'headless' );

/**
 * Allow WordPress to detect HTTPS when used behind a reverse proxy or a load balancer
 * See https://codex.wordpress.org/Function_Reference/is_ssl#Notes
 */
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

$env_config = __DIR__ . '/environments/' . WP_ENV . '.php';

if (file_exists($env_config)) {
    require_once $env_config;
}

Config::apply();

/**
 * Bootstrap WordPress
 */
if (!defined('ABSPATH')) {
    define('ABSPATH', $webroot_dir . '/wp/');
}
