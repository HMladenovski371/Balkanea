<?php
// ===========================================
// CONFIGURATION
// ===========================================

///home/balkanea/public_html/wp-content/plugins/balkanea_ratehawk_crud/Includes/download_incremental_dump.sh
$BASE_DIR = "/home/balkanea/public_html/wp-content/plugins/balkanea_ratehawk_crud/Includes";
$DATA_DIR = "/home/balkanea/public_html/CRUD_Data/extracts";
$DOWNLOAD_SCRIPT = "$BASE_DIR/download_incremental_dump.sh";
$PHP_SCRIPT = "$BASE_DIR/crone_job_import.php";  // Corrected path
$PHP_BIN = "/usr/local/bin/ea-php82";
$LOG_FILE = "/home/balkanea/public_html/CRUD_Data/logs/cron_hotels.log";  // Log file
$SCROPT_Command='/bin/bash';
// Country/region codes to process
$REGIONS = [
    "MK","BG","GR","RS","AL","TR","HR","ME","RO","XK","SI","HN","ES",
    "MT","PT","NL","BE","CY","EE","LV","LT","MD","BY","LU","NO","SK",
    "IE","HU","CZ","DK","AT","CH","PL","DE","GB","FR","IT","UA","FI","SE"
];

// ===========================================
// FUNCTIONS
// ===========================================
function log_message($message) {
    global $LOG_FILE;
    $line = "[" . date('Y-m-d H:i:s') . "] " . $message . "\n";
    file_put_contents($LOG_FILE, $line, FILE_APPEND);
}

function execute_command($command) {
    log_message("Executing: $command");
    system($command, $return_code);
    return $return_code;
}
function execute_command1($command) {
    global $LOG_FILE;
    log_message("Executing: $command");
    $output = [];
    $return_code = 0;
    exec("$command 2>&1", $output, $return_code); // capture stdout + stderr
    foreach ($output as $line) {
        log_message("OUTPUT: " . $line);
    }
    return $return_code;
}

// ===========================================
// MAIN SCRIPT
// ===========================================
log_message("=== Starting hotel data processing ===");

// Verify PHP script exists
if (!file_exists($PHP_SCRIPT)) {
    log_message("ERROR: PHP processor script not found at: $PHP_SCRIPT");
    exit(1);
}

// 1. Run the download script
//$download_result = execute_command1($SCROPT_Command.' '.$DOWNLOAD_SCRIPT);
$download_result = execute_command1("/bin/bash /home/balkanea/public_html/wp-content/plugins/balkanea_ratehawk_crud/Includes/download_incremental_dump.sh");

if ($download_result !== 0) {
    log_message("ERROR: Download script failed!");
    exit(1);
}

log_message("Download completed successfully");

// 2. Process each region
$errors = 0;

foreach ($REGIONS as $region) {
    $input_file = "$DATA_DIR/extracted_{$region}_region.jsonl";
    
    if (!file_exists($input_file)) {
        log_message("WARNING: Input file not found for region $region: $input_file");
        continue;
    }
    
    log_message("Processing region $region...");
    
    // Execute PHP script for this region
    $command = "$PHP_BIN $PHP_SCRIPT $region";
    $result = execute_command($command);
    
    if ($result === 0) {
        log_message("Successfully processed region $region");
    } else {
        log_message("ERROR: Failed to process region $region (Code: $result)");
        $errors++;
    }
}

if ($errors > 0) {
    log_message("Completed with $errors errors.");
    log_message("=== Job finished with errors ===");
    exit(1);
}

log_message("All regions processed successfully");
log_message("=== Job finished successfully ===");
exit(0);
?>
