<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// === Step 0: Load settings from export_settings.php ===
$settings_file = __DIR__ . '/export_settings.php';
if (!file_exists($settings_file)) die("Error: export_settings.php not found\n");

// Include settings directly
$settings = include $settings_file;
if (!is_array($settings)) die("Error: export_settings.php must return an array\n");

if (empty($settings['plugin_active']) || empty($settings['key_id']) || empty($settings['api_key'])) {
    die("Error: plugin not active or key_id/api_key missing\n");
}

$KEY_ID = $settings['key_id'];
$API_KEY = $settings['api_key'];

echo "Using KEY_ID: $KEY_ID\nAPI_KEY: $API_KEY\n";

// === Step 1: Define paths and URLs ===
$URL       = "https://api.worldota.net/api/b2b/v3/hotel/info/incremental_dump/";
$ZST_FILE  = "/home/balkanea/public_html/CRUD_Data/extracts/feed_en_v3.json.zst";
$JSON_FILE = "/home/balkanea/public_html/CRUD_Data/extracts/feed_en_v3.jsonl";
$JQ_PATH   = "/home/balkanea/public_html/CRUD_Data/extracts/jq";       // path to jq
$ZSTD_PATH = "/home/balkanea/public_html/CRUD_Data/zstd-1.5.7/zstd";   // path to zstd

// === Step 2: API call to get download URL ===
$ch = curl_init($URL);
curl_setopt($ch, CURLOPT_USERPWD, "$KEY_ID:$API_KEY");
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['language' => 'en']));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FAILONERROR, true);

$response = curl_exec($ch);
if (curl_errno($ch)) die("Error: curl failed: " . curl_error($ch) . "\n");
curl_close($ch);

$responseData = json_decode($response, true);
if (!$responseData || !isset($responseData['data']['url'], $responseData['data']['last_update'])) {
    die("Error: Download URL or last_update missing in response\n");
}

$DOWNLOAD_URL = $responseData['data']['url'];
$LAST_UPDATE  = $responseData['data']['last_update'];

echo "Download URL: $DOWNLOAD_URL\nLast update: $LAST_UPDATE\n";

// === Step 3: Check if file exists and compare dates ===
if (file_exists($ZST_FILE)) {
    $fileDate = gmdate("Y-m-d\TH:i:s\Z", filemtime($ZST_FILE));
    if ($fileDate >= $LAST_UPDATE) {
        echo "File $ZST_FILE is up to date (last_update: $LAST_UPDATE)\n";
    }
}

// === Step 4: Download the .zst file ===
$fp = fopen($ZST_FILE, 'w');
if (!$fp) die("Cannot open $ZST_FILE for writing\n");

$ch = curl_init($DOWNLOAD_URL);
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_USERPWD, "$KEY_ID:$API_KEY");
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_FAILONERROR, true);

curl_exec($ch);
if (curl_errno($ch)) {
    fclose($fp);
    die("Error: Failed to download file: " . curl_error($ch) . "\n");
}
curl_close($ch);
fclose($fp);

if (!file_exists($ZST_FILE) || filesize($ZST_FILE) === 0) die("Error: Downloaded file is empty\n");
echo "Downloaded file successfully: $ZST_FILE\n";

// === Step 5: Decompress .zst file ===
exec(escapeshellcmd($ZSTD_PATH) . " -d " . escapeshellarg($ZST_FILE) . " -o " . escapeshellarg($JSON_FILE), $output, $return_var);
if ($return_var !== 0) die("Error: Failed to decompress $ZST_FILE\n");
echo "Decompressed file successfully: $JSON_FILE\n";

// === Step 6: Determine country codes ===
$countryCodes = $argv[1] ?? null;
if ($countryCodes) {
    $countryCodes = explode(',', $countryCodes);
} else {
    $countryCodes = [
        "MK","BG","GR","RS","AL","TR","HR","ME","RO","XK","SI","HN","ES",
        "MT","PT","NL","BE","CY","EE","LV","LT","MD","BY","LU","NO","SK",
        "IE","HU","CZ","DK","AT","CH","PL","DE","GB","FR","IT","UA","FI","SE"
    ];
}

// === Step 7: Extract hotels per country using jq ===
foreach ($countryCodes as $code) {
    echo "[".date('Y-m-d H:i:s')."] Starting extraction for country code: $code\n";
    $outputFile = "/home/balkanea/public_html/CRUD_Data/extracts/extracted_{$code}_region.jsonl";

    // Run jq
    $cmd = escapeshellcmd($JQ_PATH) . " -c --arg cc " . escapeshellarg($code) . " 'select(.region.country_code == \$cc)' " . escapeshellarg($JSON_FILE) . " > " . escapeshellarg($outputFile);
    exec($cmd);

    $count = file_exists($outputFile) ? count(file($outputFile)) : 0;
    echo "[".date('Y-m-d H:i:s')."] Finished extraction for $code. Extracted $count hotels to $outputFile\n";
}

echo "All done.\n";
