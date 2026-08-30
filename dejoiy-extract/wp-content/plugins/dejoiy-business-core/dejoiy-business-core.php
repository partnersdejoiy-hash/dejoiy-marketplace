<?php
/**
 * Plugin Name: DEJOIY Business Core REST & Intelligence Engine
 * Plugin URI: https://vendors.dejoiy.tech
 * Description: Enterprise Core Intelligence Layer for DEJOIY Business OS (DBOS). Powers Business DNA, Business Graph, Workflow Engine, Multi-Store Organization OS, App Marketplace, Developer SDK, Integration Hub, Webhook Engine, Identity Engine, Trust Score, Session Intelligence, Audit Log, Fraud Detection, SRE Observability, High Availability Queue, Circuit Breaker, Vendor IQ, 4-Level BI, Goal Tracking, Notification Engine, Activity Stream, Feature Flags, Timeline, and Plugin Registry.
 * Version: 12.0.0
 * Author: DEJOIY Engineering Team
 * Text Domain: dbos-core
 * Requires PHP: 7.4
 * Requires at least: 5.8
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('DBOS_CORE_VERSION', '12.0.0');
define('DBOS_CORE_PATH', plugin_dir_path(__FILE__));
define('DBOS_CORE_URL', plugin_dir_url(__FILE__));

// AUTOLOAD CLASSES
spl_autoload_register(function ($class) {
    $prefix = 'Dbos\\Core\\';
    $base_dir = DBOS_CORE_PATH . 'src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// INITIALIZE REST ENGINE BOOTSTRAP
add_action('rest_api_init', function () {
    if (class_exists('Dbos\\Core\\Rest\\RestBootstrap')) {
        \Dbos\Core\Rest\RestBootstrap::init();
    }
});

// HANDLE CORS
add_action('init', function () {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, X-WP-Nonce");
});
