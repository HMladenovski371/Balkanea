<?php

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null) {
        echo "[SHUTDOWN][" . date('Y-m-d H:i:s') . "] Fatal error: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line'] . "\n";
    }
    echo "[SHUTDOWN][" . date('Y-m-d H:i:s') . "] Peak memory usage: " . memory_get_peak_usage(true) . "\n";
});

$country = $argv[1] ?? null;
if (!$country) {
    echo "ERROR: Country code must be provided as argument.\n";
    exit(1);
}

// ===========================================
// WordPress CLI / Short Init Setup
// ===========================================
define('SHORTINIT', true);
define('WP_CLI', true);
$_SERVER['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
define('WP_USE_THEMES', false);
define('DOING_CRON', true);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ===========================================
// Paths
// ===========================================
$path = realpath(__DIR__ . '/../../../../'); // WordPress root
$pluginPath = $path . '/wp-content/plugins/balkanea_ratehawk_crud';

// Autoload all model files
foreach (glob($pluginPath . '/Models/*.php') as $file) {
    require_once $file;
}

// Include log class
require_once $pluginPath . '/Includes/Log.php';

// Load WordPress
require_once $path . '/wp-load.php';
global $wpdb;
$wpdb->show_errors();

// Initialize log
$log = new Log($country);

// ===========================================
// Helper Functions
// ===========================================

function readJsonl(string $filePath): Generator
{
    $handle = fopen($filePath, 'r');
    if (!$handle) throw new RuntimeException("Unable to open file: $filePath");

    while (!feof($handle)) {
        $line = fgets($handle);
        if ($line === false) break;
        $line = trim($line);
        if ($line === '') continue;
        yield $line;
    }
    fclose($handle);
}

function fastLineCount(string $filePath): int
{
    $file = new SplFileObject($filePath, 'r');
    $lines = 0;
    while (!$file->eof()) {
        $file->current();
        $file->next();
        $lines++;
    }
    return $lines;
}

// ===========================================
// Main Processing
// ===========================================
try {
    $filePath = $path . "/CRUD_Data/extracts/extracted_" . strtoupper($country) . "_region.jsonl";
    if (!file_exists($filePath)) {
        echo "ERROR: File not found: $filePath\n";
        $log->info("ERROR: File not found: $filePath");
        exit(1);
    }

    $totalHotelsInFile = fastLineCount($filePath);
    echo "Total hotels in file: $totalHotelsInFile\n";
    $log->info("Total hotels in file: $totalHotelsInFile");

    $existingHotelsCount = 0;
    $processedCount = 0;
    $startTime = microtime(true);

    $batchSize = 500; // Number of hotels per DB query batch
    $batchIds = [];

    foreach (readJsonl($filePath) as $line) {
        $processedCount++;
        $hotel = json_decode($line, true);
        if (!$hotel) {
            $log->info("Failed to decode hotel JSON: " . json_last_error_msg());
            continue;
        }

        $hotel_name = $hotel['id'] ?? '';
        if (!$hotel_name || strlen($hotel_name) > 199) continue;

        $batchIds[] = $hotel_name;

        // Process batch when full
        if (count($batchIds) >= $batchSize) {
            $placeholders = implode(',', array_fill(0, count($batchIds), '%s'));
            $query = $wpdb->prepare("SELECT post_name FROM {$wpdb->prefix}posts WHERE post_name IN ($placeholders)", ...$batchIds);
            $existingPosts = $wpdb->get_col($query);
            $existingHotelsCount += count($existingPosts);
            $batchIds = []; // reset batch
        }

        if ($processedCount % 1000 === 0) {
            echo "Processed $processedCount/$totalHotelsInFile hotels...\n";
        }
    }

    // Process remaining hotels in last batch
    if (count($batchIds) > 0) {
        $placeholders = implode(',', array_fill(0, count($batchIds), '%s'));
        $query = $wpdb->prepare("SELECT post_name FROM {$wpdb->prefix}posts WHERE post_name IN ($placeholders)", ...$batchIds);
        $existingPosts = $wpdb->get_col($query);
        $existingHotelsCount += count($existingPosts);
    }

    $totalTime = microtime(true) - $startTime;

    echo "\n\n==============================================";
    echo "\nHOTEL COUNT SUMMARY FOR COUNTRY: " . strtoupper($country);
    echo "\n==============================================";
    echo "\nTotal hotels in file: " . $totalHotelsInFile;
    echo "\nHotels already in database: " . $existingHotelsCount;
    echo "\nNew hotels (not in database): " . ($totalHotelsInFile - $existingHotelsCount);
    echo "\nProcessing time: " . number_format($totalTime, 2) . " seconds";
    echo "\nAverage time per hotel: " . number_format($totalTime / max(1, $totalHotelsInFile), 4) . " seconds";
    echo "\n==============================================\n";

    $log->info("COUNT SUMMARY - Total in file: $totalHotelsInFile, Already in DB: $existingHotelsCount, New hotels: " . ($totalHotelsInFile - $existingHotelsCount) . ", Processing time: " . number_format($totalTime, 2) . " seconds");

} catch (\Exception $ex) {
    echo "CRITICAL ERROR: " . $ex->getMessage() . "\n";
    $log->info("CRITICAL ERROR: " . $ex->getMessage());
    $log->info("Error stack trace: " . $ex->getTraceAsString());
    exit(1);
}
