#!/usr/bin/env php
<?php
/**
 * process_hotels_batch.php
 * General script to process hotel data for one or more countries
 */

// ===========================================
// CONFIGURATION
// ===========================================
$BASE_DIR       = "/home/balkanea/public_html/wp-content/plugins/balkanea_ratehawk_crud/Includes";
$DATA_DIR       = "/home/balkanea/public_html/CRUD_Data/extracts";
$DOWNLOAD_SCRIPT = "$BASE_DIR/download_incremental_dump.sh";
$PROCESS_SCRIPT  = "$BASE_DIR/crone_job_import.php";
$PHP_BIN         = "/usr/local/bin/ea-php82";
$LOG_FILE = "/home/balkanea/public_html/CRUD_Data/process_hotels.log";
// ===========================================
// HELPER FUNCTIONS
// ===========================================
function log_message($message) {
    echo "[" . date('Y-m-d H:i:s') . "] $message\n";
    global $LOG_FILE;
    $line = "[" . date('Y-m-d H:i:s') . "] $message\n";
    file_put_contents($LOG_FILE, $line, FILE_APPEND);
}

function execute_command($command) {
    log_message("Executing: $command");
    system($command, $return_code);
    return $return_code;
}

// ===========================================
// PARSE PARAMETERS
// ===========================================
$args = array_slice($argv, 1); // skip script name
if (empty($args)) {
    log_message("ERROR: No country code specified. Use one or more 2-letter codes.");
    exit(1);
}

// Accept comma-separated list as one argument
$regions = [];
foreach ($args as $arg) {
    $parts = explode(',', $arg);
    foreach ($parts as $p) {
        $code = strtoupper(trim($p));
        if (preg_match('/^[A-Z]{2}$/', $code)) {
            $regions[] = $code;
        } else {
            log_message("WARNING: Invalid country code skipped: $p");
        }
    }
}

if (empty($regions)) {
    log_message("ERROR: No valid country codes provided.");
    exit(1);
}

// ===========================================
// MAIN SCRIPT
// ===========================================
log_message("Starting hotel data processing for: " . implode(', ', $regions));

// 1️⃣ Run download script first
$download_result = execute_command($DOWNLOAD_SCRIPT);
if ($download_result !== 0) {
    log_message("ERROR: Download script failed!");
    exit(1);
}
log_message("Download completed successfully.");

// 2️⃣ Run process script per region
foreach ($regions as $region) {
    $input_file = "$DATA_DIR/extracted_{$region}_region.jsonl";
    if (!file_exists($input_file)) {
        log_message("WARNING: Input file not found for region $region: $input_file");
        continue;
    }

    log_message("Processing region $region...");
    $cmd = "$PHP_BIN $PROCESS_SCRIPT $region";
    $result = execute_command($cmd);

    if ($result === 0) {
        log_message("Successfully processed region $region");
    } else {
        log_message("ERROR: Failed to process region $region (Code: $result)");
    }
}

log_message("All specified regions processed successfully.");
exit(0);