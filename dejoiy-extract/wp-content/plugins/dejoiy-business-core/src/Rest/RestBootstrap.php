<?php
namespace Dbos\Core\Rest;

use Dbos\Core\Controllers\SystemController;
use Dbos\Core\Controllers\BusinessController;

class RestBootstrap {
    private static $namespace = 'dbos/v1';

    public static function init() {
        self::register_system_routes();
        self::register_business_routes();
    }

    private static function register_system_routes() {
        $controller = new SystemController();

        // STEP 7: System Health
        register_rest_route(self::$namespace, '/system/health', array(
            'methods' => 'GET',
            'callback' => array($controller, 'getHealth'),
            'permission_callback' => '__return_true'
        ));

        // STEP 8: System Diagnostics
        register_rest_route(self::$namespace, '/system/diagnostics', array(
            'methods' => 'GET',
            'callback' => array($controller, 'getDiagnostics'),
            'permission_callback' => '__return_true'
        ));

        // STEP 9: System Version
        register_rest_route(self::$namespace, '/system/version', array(
            'methods' => 'GET',
            'callback' => array($controller, 'getVersion'),
            'permission_callback' => '__return_true'
        ));

        // STEP 10: System Capabilities
        register_rest_route(self::$namespace, '/system/capabilities', array(
            'methods' => 'GET',
            'callback' => array($controller, 'getCapabilities'),
            'permission_callback' => '__return_true'
        ));
    }

    private static function register_business_routes() {
        $controller = new BusinessController();

        register_rest_route(self::$namespace, '/ping', array(
            'methods' => 'GET',
            'callback' => array($controller, 'getPing'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route(self::$namespace, '/dashboard-metrics', array(
            'methods' => 'GET',
            'callback' => array($controller, 'getDashboardMetrics'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route(self::$namespace, '/products', array(
            'methods' => 'GET',
            'callback' => array($controller, 'getProducts'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route(self::$namespace, '/orders', array(
            'methods' => 'GET',
            'callback' => array($controller, 'getOrders'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route(self::$namespace, '/intelligence/dna', array(
            'methods' => 'GET',
            'callback' => array($controller, 'getBusinessDna'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route(self::$namespace, '/bi/vendor-iq', array(
            'methods' => 'GET',
            'callback' => array($controller, 'getVendorIq'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route(self::$namespace, '/security/trust-score', array(
            'methods' => 'GET',
            'callback' => array($controller, 'getTrustScore'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route(self::$namespace, '/sre/metrics', array(
            'methods' => 'GET',
            'callback' => array($controller, 'getSreMetrics'),
            'permission_callback' => '__return_true'
        ));
    }
}
