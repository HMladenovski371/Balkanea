<?php

// Include plugin.php if needed
if ( ! function_exists( 'is_plugin_active' ) ) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// Check if the Balkanea plugin is active
$url = './export_settings.php';

$response = file_get_contents($url);
$data = json_decode($response, true);

$keyId = $apiKey = null;

if (isset($data['plugin_active']) && $data['plugin_active']) {
    $keyId = $data['key_id'];
    $apiKey = $data['api_key'];

     echo "Key ID: $keyId\n";
     echo "API Key: $apiKey\n";
} else {
     echo "Plugin not active or failed to fetch settings.\n";
}

return [
    'api_key'=> $keyId,
    'api_password'=>$apiKey,
'email' => 'admin@balkanea.com'    
];


/*return [
    'api_key' => '7788',                                              // API key for authentication - production: 11492; stage: 7788
    'api_password' => 'e6a79dc0-c452-48e0-828d-d37614165e39',         // API password/secret - production: 7b899b07-227e-463c-a58c-553c0cd7c37c; stage: e6a79dc0-c452-48e0-828d-d37614165e39
    'email' => 'admin@balkanea.com'                                  // Administrator email address
];*/