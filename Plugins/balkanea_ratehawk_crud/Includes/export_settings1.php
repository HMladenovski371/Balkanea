<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/../../../../wp-load.php';

    if (!function_exists('is_plugin_active')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $response = [
        'key_id' => '',
        'api_key' => '',
        'plugin_active' => false,
    ];

    if (is_plugin_active('balkanea/balkanea.php')) {
        $balkanea_settings = get_option('balkanea_settings');

        if (!empty($balkanea_settings['key_id']) && !empty($balkanea_settings['api_key'])) {
            $response = [
                'key_id' => $balkanea_settings['key_id'],
                'api_key' => $balkanea_settings['api_key'],
                'plugin_active' => true,
            ];
        } else {
            $response['error'] = 'Plugin active, but no API settings found';
        }
    } else {
        $response['error'] = 'Plugin not active';
    }

    return $response; // <--- Враќа array, без echo

} catch (Throwable $e) {
    error_log("Error in export_settings.php: " . $e->getMessage());

    return [
        'error' => true,
        'message' => $e->getMessage(),
    ];
}