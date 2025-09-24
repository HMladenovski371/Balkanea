<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $is_cli = php_sapi_name() === 'cli';

    // Ако е CLI, игнорирај header()
    if ($is_cli) {
        if (!function_exists('header')) {
            function header($str, $replace = true, $http_response_code = null) {
                // ignore
            }
        }
    }

    echo "Script is running\n";

    require_once __DIR__ . '/../../../../wp-load.php';

    if (!function_exists('is_plugin_active')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    // header само ако не е CLI
    if (!$is_cli) {
        header('Content-Type: application/json');
    }

    $response = [
        'key_id' => '',
        'api_key' => '',
        'plugin_active' => false,
    ];

    if (is_plugin_active('balkanea/balkanea.php')) {
        $balkanea_settings = get_option('balkanea_settings');

        $response = [
            'key_id' => $balkanea_settings['key_id'] ?? '',
            'api_key' => $balkanea_settings['api_key'] ?? '',
            'plugin_active' => true,
        ];
    } else {
        error_log("Balkanea Plugin Not Active");
    }

    echo json_encode($response);

} catch (Throwable $e) {
    $errorMessage = "Error in export_settings.php: " . $e->getMessage();
    error_log($errorMessage);
    echo $errorMessage . "\n";

    if (!$is_cli) {
        header('Content-Type: application/json');
    }

    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
    ]);
}
