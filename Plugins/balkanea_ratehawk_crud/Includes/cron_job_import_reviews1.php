<?php
// cron_job_import_reviews.php
// ===============================================
// WordPress Hotel Reviews Importer (Optimized + Duplicate Safe + Auto Hotel Insert)
// ===============================================

echo "Starting importer...\n";

// Load WordPress
$path = realpath(__DIR__ . '/../../../../');
require_once $path . '/wp-load.php';

// Composer autoload for JsonMachine
require_once __DIR__ . '/vendor/autoload.php';
use JsonMachine\JsonMachine;

global $wpdb;
$wpdb->show_errors();
$prefix = $wpdb->prefix;

// Paths
$base_path    = '/home/balkanea/public_html/CRUD_Data/';
$reviews_path = $base_path . 'reviews/';
$log_path     = $base_path . 'logs/';
$log_file     = $log_path . 'reviewImport.log';
$skipped_file = $log_path . 'skipped.log';

// Create logs dir if missing
if (!file_exists($log_path)) {
    if (!mkdir($log_path, 0755, true)) {
        die("Cannot create log directory: $log_path\n");
    }
}

// -----------------------------
// Logging function
// -----------------------------
function logMessage($message, $level = 'INFO', $skipped = false) {
    global $log_file, $skipped_file;
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] [$level] $message" . PHP_EOL;

    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    echo $log_entry;

    if ($skipped) {
        file_put_contents($skipped_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
}

// -----------------------------
// Check if hotel exists
// -----------------------------
function hotelExists($external_hid) {
    global $wpdb, $prefix;
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$prefix}st_hotel WHERE external_hid = %s",
        $external_hid
    ));
    return $count > 0;
}

// -----------------------------
// Insert hotel if not exists
// -----------------------------
function insertHotelIfNotExists($hotel_data) {
    global $wpdb, $prefix;
    $external_hid = $hotel_data['hid'] ?? null;
    if (!$external_hid) return false;

    if (hotelExists($external_hid)) return true;

    $data = [
        'external_hid' => $external_hid,
        'post_title'   => $hotel_data['name'] ?? 'Unknown Hotel',
        'post_status'  => 'publish',
        'post_type'    => 'st_hotel',
        'post_date'    => current_time('mysql'),
        'post_date_gmt'=> gmdate('Y-m-d H:i:s')
    ];

    $inserted = $wpdb->insert("{$prefix}st_hotel", $data);
    return $inserted !== false;
}

// -----------------------------
// Insert or update hotel aggregated review
// -----------------------------
function insertOrUpdateHotelReview($external_hid, $hotel_data, $review_count, $last_review_date) {
    global $wpdb, $prefix;
    $detailed = $hotel_data['detailed_ratings'] ?? [];

    $existing = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM {$prefix}hotel_reviews WHERE hid_id = %s", $external_hid),
        ARRAY_A
    );

    $data = [
        'total_reviews'   => $review_count,
        'last_review_date'=> $last_review_date
    ];

    $shouldUpdateRatings = true;
    if ($existing && $existing['last_review_date']) {
        $shouldUpdateRatings = strtotime($last_review_date) > strtotime($existing['last_review_date']);
    }

    if ($shouldUpdateRatings) {
        $data = array_merge($data, [
            'overall_rating'  => $hotel_data['rating'] ?? null,
            'cleanness_rating'=> $detailed['cleanness'] ?? null,
            'location_rating' => $detailed['location'] ?? null,
            'price_rating'    => $detailed['price'] ?? null,
            'services_rating' => $detailed['services'] ?? null,
            'room_rating'     => $detailed['room'] ?? null,
            'meal_rating'     => $detailed['meal'] ?? null,
            'wifi_rating'     => $detailed['wifi'] ?? null,
            'hygiene_rating'  => $detailed['hygiene'] ?? null
        ]);
    }

    if ($existing) {
        $updated = $wpdb->update("{$prefix}hotel_reviews", $data, ['hid_id' => $external_hid]);
        return $updated === false
            ? ['status' => 'skipped', 'reason' => 'update_failed', 'error' => $wpdb->last_error]
            : ['status' => 'updated'];
    } else {
        $data['hid_id'] = $external_hid;
        $inserted = $wpdb->insert("{$prefix}hotel_reviews", $data);
        return !$inserted
            ? ['status' => 'skipped', 'reason' => 'insert_failed', 'error' => $wpdb->last_error]
            : ['status' => 'inserted'];
    }
}

// -----------------------------
// Insert individual review if not exists
// -----------------------------
function insertReviewIfNotExists($external_hid, $review) {
    global $wpdb, $prefix;

    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$prefix}reviews WHERE review_id = %s",
        $review['id']
    ));
    if ($exists) return ['status' => 'exists'];

    $detailed = $review['detailed'] ?? [];
    $images_json = !empty($review['images']) ? json_encode($review['images']) : null;

    $data = [
        'review_id'       => $review['id'],
        'hid_id'          => $external_hid,
        'review_plus'     => $review['review_plus'] ?? null,
        'review_minus'    => $review['review_minus'] ?? null,
        'created_date'    => $review['created'] ?? null,
        'author'          => $review['author'] ?? null,
        'adults_count'    => $review['adults'] ?? 1,
        'children_count'  => $review['children'] ?? 0,
        'room_name'       => $review['room_name'] ?? null,
        'nights_count'    => $review['nights'] ?? null,
        'overall_rating'  => $review['rating'] ?? null,
        'cleanness_rating'=> $detailed['cleanness'] ?? null,
        'location_rating' => $detailed['location'] ?? null,
        'price_rating'    => $detailed['price'] ?? null,
        'services_rating' => $detailed['services'] ?? null,
        'room_rating'     => $detailed['room'] ?? null,
        'meal_rating'     => $detailed['meal'] ?? null,
        'wifi_quality'    => $detailed['wifi'] ?? 'unspecified',
        'hygiene_quality' => $detailed['hygiene'] ?? 'unspecified',
        'traveller_type'  => $review['traveller_type'] ?? 'unspecified',
        'trip_type'       => $review['trip_type'] ?? 'unspecified',
        'images'          => $images_json
    ];

    $inserted = $wpdb->insert("{$prefix}reviews", $data);
    return !$inserted
        ? ['status' => 'skipped', 'reason' => 'insert_failed', 'error' => $wpdb->last_error]
        : ['status' => 'inserted'];
}

// -----------------------------
// Process a single JSON file
// -----------------------------
function processJsonFile($filename) {
    global $reviews_path, $wpdb, $prefix;

    $full_path = $reviews_path . $filename;
    if (!file_exists($full_path)) {
        logMessage("File does not exist: $full_path", 'ERROR', true);
        return;
    }

    logMessage("Streaming file: $full_path");

    try {
        $jsonStream = JsonMachine::fromFile($full_path);

        foreach ($jsonStream as $hotel_slug => $hotel_data) {
            $external_hid = $hotel_data['hid'] ?? null;
            if (!$external_hid) {
                logMessage("No hid for hotel $hotel_slug, skipping", 'WARNING', true);
                continue;
            }

            // -----------------------------
            // Insert hotel if not exists
            // -----------------------------
            if (!hotelExists($external_hid)) {
                $hotelInserted = insertHotelIfNotExists($hotel_data);
                if (!$hotelInserted) {
                    logMessage("Failed to insert hotel $external_hid, skipping.", 'ERROR', true);
                    continue;
                }
                logMessage("Hotel $external_hid inserted.");
            }

            // -----------------------------
            // Insert room reviews first
            // -----------------------------
            if (!empty($hotel_data['reviews'])) {
                foreach ($hotel_data['reviews'] as $review) {
                    $result = insertReviewIfNotExists($external_hid, $review);
                    logMessage("Review {$review['id']} for hotel $external_hid: {$result['status']}" .
                        (isset($result['error']) ? " | Error: {$result['error']}" : ''));
                }
            }

            // -----------------------------
            // Recalculate total reviews & last review date from DB
            // -----------------------------
            $review_count = $wpdb->get_var(
                $wpdb->prepare("SELECT COUNT(*) FROM {$prefix}reviews WHERE hid_id = %s", $external_hid)
            );
            $last_review_date = $wpdb->get_var(
                $wpdb->prepare("SELECT MAX(created_date) FROM {$prefix}reviews WHERE hid_id = %s", $external_hid)
            );

            // -----------------------------
            // Insert or update hotel review aggregate
            // -----------------------------
            $hotel_result = insertOrUpdateHotelReview($external_hid, $hotel_data, $review_count, $last_review_date);
            logMessage("Hotel $external_hid aggregated status: {$hotel_result['status']}" .
                (isset($hotel_result['error']) ? " | Error: {$hotel_result['error']}" : ''));
        }

    } catch (Exception $e) {
        logMessage("Error reading $filename: " . $e->getMessage(), 'ERROR', true);
    }
}

// -----------------------------
// Process all JSON files in folder
// -----------------------------
function processJsonDirectory($dir) {
    $files = glob($dir . '*.json');
    if (!$files) {
        logMessage("No JSON files found in $dir", 'WARNING');
        return;
    }

    foreach ($files as $file) {
        processJsonFile(basename($file));
    }
}

// -----------------------------
// MAIN
// -----------------------------
if ($argc == 2 && $argv[1] === '--all') {
    processJsonDirectory($GLOBALS['reviews_path']);
} elseif ($argc == 2) {
    $json_file = $argv[1];
    processJsonFile($json_file);
} else {
    logMessage("Usage: php " . $argv[0] . " <json_file> OR php " . $argv[0] . " --all", 'ERROR');
    die("Usage: php " . $argv[0] . " <json_file> OR php " . $argv[0] . " --all\n");
}

logMessage("Script finished successfully!");
