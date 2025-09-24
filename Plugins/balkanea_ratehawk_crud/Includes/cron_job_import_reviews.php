<?php
// database_importer.php
// ===============================================
// WordPress Hotel Reviews Importer (Multi-language)
// ===============================================

echo "Starting importer...\n";

// Load WordPress
$path = realpath(__DIR__ . '/../../../../');
require_once $path . '/wp-load.php';

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
// Check if hotel exists in DB
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
// Check if hotel aggregated review exists
// -----------------------------
function getHotelReviewStatus($external_hid) {
    global $wpdb, $prefix;
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$prefix}hotel_reviews WHERE hid_id = %s",
        $external_hid
    ));
    return $count > 0 ? 'exists' : 'new';
}

// -----------------------------
// Insert or update hotel aggregated review
// -----------------------------
function insertOrUpdateHotelReview($external_hid, $hotel_data) {
    global $wpdb, $prefix;

    if (!hotelExists($external_hid)) {
        return ['status' => 'skipped', 'reason' => 'hotel_not_in_db'];
    }

    $current_status = getHotelReviewStatus($external_hid);
    $detailed = $hotel_data['detailed_ratings'] ?? [];
    $last_review_date = null;

    if (!empty($hotel_data['reviews'])) {
        $dates = array_column($hotel_data['reviews'], 'created');
        $last_review_date = max($dates);
    }

    $data = [
        'overall_rating'  => $hotel_data['rating'] ?? null,
        'cleanness_rating'=> $detailed['cleanness'] ?? null,
        'location_rating' => $detailed['location'] ?? null,
        'price_rating'    => $detailed['price'] ?? null,
        'services_rating' => $detailed['services'] ?? null,
        'room_rating'     => $detailed['room'] ?? null,
        'meal_rating'     => $detailed['meal'] ?? null,
        'wifi_rating'     => $detailed['wifi'] ?? null,
        'hygiene_rating'  => $detailed['hygiene'] ?? null,
        'total_reviews'   => count($hotel_data['reviews']),
        'last_review_date'=> $last_review_date
    ];

    if ($current_status === 'exists') {
        $updated = $wpdb->update(
            "{$prefix}hotel_reviews",
            $data,
            ['hid_id' => $external_hid]
        );
        if ($updated === false) {
            return ['status' => 'skipped', 'reason' => 'update_failed', 'error' => $wpdb->last_error];
        }
        return ['status' => 'updated'];
    } else {
        $data['hid_id'] = $external_hid;
        $inserted = $wpdb->insert("{$prefix}hotel_reviews", $data);
        if (!$inserted) {
            return ['status' => 'skipped', 'reason' => 'insert_failed', 'error' => $wpdb->last_error];
        }
        return ['status' => 'inserted'];
    }
}

// -----------------------------
// Check if individual review exists
// -----------------------------
function reviewExists($review_id) {
    global $wpdb, $prefix;
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$prefix}reviews WHERE review_id = %s",
        $review_id
    ));
    return $count > 0 ? 'exists' : 'new';
}

// -----------------------------
// Insert or update individual review
// -----------------------------
function insertOrUpdateReview($external_hid, $review) {
    global $wpdb, $prefix;

    $current_status = reviewExists($review['id']);
    $detailed = $review['detailed'] ?? [];
    $images_json = !empty($review['images']) ? json_encode($review['images']) : null;

    $data = [
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

    if ($current_status === 'exists') {
        $updated = $wpdb->update(
            "{$prefix}reviews",
            $data,
            ['review_id' => $review['id']]
        );
        if ($updated === false) {
            return ['status' => 'skipped', 'reason' => 'update_failed', 'error' => $wpdb->last_error];
        }
        return ['status' => 'updated'];
    } else {
        $data['review_id'] = $review['id'];
        $inserted = $wpdb->insert("{$prefix}reviews", $data);
        if (!$inserted) {
            return ['status' => 'skipped', 'reason' => 'insert_failed', 'error' => $wpdb->last_error];
        }
        return ['status' => 'inserted'];
    }
}

// -----------------------------
// Process a single JSON file
// -----------------------------
function processJsonFile($filename) {
    global $reviews_path;

    $full_path = $reviews_path . $filename;
    if (!file_exists($full_path)) {
        logMessage("File does not exist: $full_path", 'ERROR', true);
        return;
    }

    logMessage("Reading file: $full_path");
    $content = file_get_contents($full_path);
    $data = json_decode($content, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        logMessage("JSON decode error in $filename: " . json_last_error_msg(), 'ERROR', true);
        return;
    }

    // Detect language from filename
    $language = 'unknown';
    if (preg_match('/_(ar|bg|cs|da|de|el|en|es|fi|fr|he|hu|it|ja|kk|ko|nl|no|pl|pt|pt_PT|ro|ru|sq|sr|sv|th|tr|uk|vi|zh_CN|zh_TW)\./', $filename, $matches)) {
        $language = $matches[1];
    }

    foreach ($data as $hotel_slug => $hotel_data) {
        $external_hid = $hotel_data['hid'] ?? null;
        if (!$external_hid) {
            logMessage("[$language] No hid found for hotel slug $hotel_slug, skipping.", 'WARNING', true);
            continue;
        }

        // Only process hotels that exist
        if (!hotelExists($external_hid)) {
            logMessage("[$language] Hotel $external_hid not found in DB, skipping hotel and reviews.", 'WARNING', true);
            continue;
        }

        // Update or insert aggregated hotel review
        $hotel_result = insertOrUpdateHotelReview($external_hid, $hotel_data);
        logMessage("[$language] Hotel $external_hid aggregated status: {$hotel_result['status']}" . 
            (isset($hotel_result['error']) ? " | Error: {$hotel_result['error']}" : ''));

        // Process individual reviews
        if (!empty($hotel_data['reviews'])) {
            foreach ($hotel_data['reviews'] as $review) {
                $review_result = insertOrUpdateReview($external_hid, $review);
                logMessage("[$language] Review {$review['id']} for hotel $external_hid: {$review_result['status']}" .
                    (isset($review_result['error']) ? " | Error: {$review_result['error']}" : ''));
            }
        }
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
