<?php

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null) {
        echo("[SHUTDOWN][" . date('Y-m-d H:i:s') . "] Fatal error: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line']);
    }
    echo("[SHUTDOWN][" . date('Y-m-d H:i:s') . "] Peak memory usage: " . memory_get_peak_usage(true));
});

if (!isset($argv[1])) {
    die("Usage: php crone_job_import.php <country> [rr]\n");
}
$country = $argv[1];
$restart = isset($argv[2]) && $argv[2] === 'rr';

//$regions = [$region]; //make an array

define('SHORTINIT', true);
define('WP_CLI', true);
$_SERVER['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
define('WP_USE_THEMES', false); // or true, depending on context
define('DOING_CRON', true); // Helps WP know it's a cron job

use Models\Amenity;
use Models\HotelRoom;
use Models\ImageInserter;
use Models\PostMetaValues;
use Models\PostsHotel;
use Models\PostsRoom;
use Models\ProcessAmenity;
use Models\St_Hotel;
use Models\LocationNested;
use Models\LocationRelationship;
use Models\CurrencyModel;
use data\HotelFlag;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$path = realpath(__DIR__ . '/../../../../');

require_once __DIR__ . '/Log.php';
require_once __DIR__ . '/../Models/Amenity.php';
require_once __DIR__ . '/../Models/ProcessAmenity.php';
require_once __DIR__ . '/../Models/PostsHotel.php';
require_once __DIR__ . '/../Models/PostsRoom.php';
require_once __DIR__ . '/../Models/HotelRoom.php';
require_once __DIR__ . '/../Models/PostMetaValues.php';
require_once __DIR__ . '/../Models/St_Hotel.php';
require_once __DIR__ . '/../Models/ImageInsert.php';
require_once __DIR__ . '/../Models/RoomAvailability.php';
require_once __DIR__ . '/../Models/LocationRelationship.php';
require_once __DIR__ . '/../Models/LocationNested.php';
require_once __DIR__ . '/../Models/CurrencyModel.php';
require_once __DIR__ . '/./data.php';
require_once __DIR__ . '/./track_data.php';
require_once __DIR__ . '/./HotelFlag.php';
require_once __DIR__ . '/./HttpRequests.php';

//wp
require_once $path . '/wp-load.php';
$config = include __DIR__ . '/config.php';
global $wpdb;
$wpdb->show_errors();
$prefix = $wpdb->prefix;

//log
$log = new Log($country);

// Progress tracking file
$progressFile = __DIR__ . "/../../../../CRUD_Data/progress/progress_{$country}.txt";

// Create progress directory if it doesn't exist
$progressDir = dirname($progressFile);
if (!is_dir($progressDir)) {
    mkdir($progressDir, 0755, true);
}

// Get last processed hotel ID or start from beginning
$lastProcessedHotelId = null;
if (!$restart && file_exists($progressFile)) {
    $lastProcessedHotelId = trim(file_get_contents($progressFile));
    
    // Validate the ID from progress file
    if (empty($lastProcessedHotelId)) {
        $log->info("Progress file exists but is empty, starting from beginning");
        $lastProcessedHotelId = null;
    } elseif (!is_numeric($lastProcessedHotelId)) {
        $log->info("Progress file contains invalid ID: '$lastProcessedHotelId', starting from beginning");
        $lastProcessedHotelId = null;
    } else {
        $log->info("Resuming from hotel ID: {$lastProcessedHotelId}");
    }
} else {
    $log->info("Starting from the beginning (restart mode)");
}

//pull RateHawek INFO from BalkaneaPlugin

// Include plugin.php if needed
if ( ! function_exists( 'is_plugin_active' ) ) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// Check if the Balkanea plugin is active and get settings
$keyId = null;
$apiKey = null;

function getBalkaneaSettings() {
    $exportSettingsFile = __DIR__ . '/export_settings.php';
    if (!file_exists($exportSettingsFile)) {
        error_log("export_settings.php not found!");
        return ['plugin_active' => false];
    }

    // Capture any output so echo/header не ќе го наруши логиката
    ob_start();
    $data = require $exportSettingsFile;
    ob_end_clean(); // discard any echo output

    if (!is_array($data)) {
        return ['plugin_active' => false];
    }

    return $data;
}

$data = getBalkaneaSettings();

if (!empty($data['plugin_active'])) {
    $keyId = $data['key_id'];
    $apiKey = $data['api_key'];
    error_log("Key ID: $keyId\nAPI Key: $apiKey\n");
} else {
    error_log("Plugin not active or failed to fetch settings.\n");
}

//auth
//$keyId = $config['api_key'];
//$apiKey = $config['api_password'];
$credentials = base64_encode($keyId . ':' . $apiKey);
$headers = array(
    'Authorization: Basic ' . $credentials,
    'Content-Type: application/json'
);
//logToRegionFile("Authentication successful");


function clearWpDb()
{
}

function logToRegionFile($message, $currentRegion = 'global', $logLevel = 'INFO')
{
    error_log("Logging to $currentRegion");
    $logDate = date('Y-m-d H:i:s');
    $logMessage = "[$logDate] [$logLevel] $message" . PHP_EOL;

    $logFilePath = __DIR__ . "/../../../../CRUD_Data/logs/{$currentRegion}_logs.log";

    if (!is_dir(__DIR__ . '/../../../../CRUD_Data/logs')) {
        mkdir(__DIR__ . '/../../../../CRUD_Data/logs', 0755, true);
    }

    file_put_contents($logFilePath, $logMessage, FILE_APPEND);
}

// Funkcija za logiranje na hoteli koi ne postojat vo baza
function logNewHotelHid($hid, $country = 'global') {
    $logDir = __DIR__ . "/../../../../CRUD_Data/logs";
    echo $logDir;
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $logFile = $logDir . "/new_hotels_{$country}.log";
    $logMessage = "[" . date('Y-m-d H:i:s') . "] " . $hid . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

try {

     $regionTotal = 0;
     
     $filePath = '/home/balkanea/public_html/CRUD_Data/extracts/extracted_'.strtoupper($country).'_region.jsonl';
     
     // Check if file exists and is readable
     if (!file_exists($filePath)) {
         $log->info("File not found: $filePath");
         die("File not found: $filePath\n");
     }

     if (!is_readable($filePath)) {
         $log->info("File is not readable: $filePath");
         die("File is not readable: $filePath\n");
     }

    /* echo $filePath;
     $totalHotelsInRegion = fastLineCount($filePath);
     echo 'res: ' . $totalHotelsInRegion;
     
     $log->info("Starting to process JSONL file: $filePath");
     $log->info("Total hotels in file: $totalHotelsInRegion");
     
     $i = 0;
     $skipUntilFound = $lastProcessedHotelId !== null;
     $foundLastHotel = false;
     $processedCount = 0;
     $skippedCount = 0;

     $log->info("Resume status - Skip until found: " . ($skipUntilFound ? "YES" : "NO"));
     $log->info("Last processed hotel ID: " . ($lastProcessedHotelId ?: "NONE"));

     foreach (readJsonl($filePath) as $hotel_response) {
         try {
             $i++;
             $hotel = json_decode($hotel_response, true);
             if (!$hotel) {
                 $log->info("Failed to decode hotel JSON: " . json_last_error_msg());
                 $skippedCount++;
                 continue;
             }
             
             $currentHotelId = isset($hotel['hid']) ? $hotel['hid'] : 'MISSING_HID';
             $log->info("Processing line $i: Hotel ID = $currentHotelId, Name = " . ($hotel['name'] ?? 'UNKNOWN'));
            echo "Hotel ID $currentHotelId BUT last processed is $lastProcessedHotelId\n";
             // Skip hotels until we find the last processed one
             if ($skipUntilFound && !$foundLastHotel) {
                 $log->info("Checking if $currentHotelId matches last processed $lastProcessedHotelId");
                 
                 if ($currentHotelId === $lastProcessedHotelId) {
                     $foundLastHotel = true;
                     $log->info("FOUND LAST HOTEL! Continuing from next hotel.");
                     // Continue to process THIS hotel (don't skip it)
                 } else {
                     $log->info("Skipping hotel $currentHotelId (looking for $lastProcessedHotelId)");
                     $skippedCount++;
                     continue; // skip until we find the last processed
                 }
             }*/
             echo $filePath;
$totalHotelsInRegion = fastLineCount($filePath);
echo 'res: ' . $totalHotelsInRegion;

$log->info("Starting to process JSONL file: $filePath");
$log->info("Total hotels in file: $totalHotelsInRegion");

$i = 0;
$skipUntilFound = $lastProcessedHotelId !== null;
$foundLastHotel = false;
$processedCount = 0;
$skippedCount = 0;

// Convert last processed hotel ID to int to ensure proper comparison
$lastProcessedHotelId = $lastProcessedHotelId !== null ? (int)$lastProcessedHotelId : null;
//$lastProcessedHotelId=10458340;
$log->info("Resume status - Skip until found: " . ($skipUntilFound ? "YES" : "NO"));
$log->info("Last processed hotel ID: " . ($lastProcessedHotelId ?: "NONE"));

foreach (readJsonl($filePath) as $hotel_response) {
    try {
        $i++;
        $hotel = json_decode($hotel_response, true);
        if (!$hotel) {
            $log->info("Failed to decode hotel JSON: " . json_last_error_msg());
            $skippedCount++;
            continue;
        }
        
        // Convert current hotel ID to int for comparison
        $currentHotelId = isset($hotel['hid']) ? (int)$hotel['hid'] : null;
        $log->info("Processing line $i: Hotel ID = $currentHotelId, Name = " . ($hotel['name'] ?? 'UNKNOWN'));
        echo "Hotel ID $currentHotelId BUT last processed is $lastProcessedHotelId\n";

        // Skip hotels until we find the last processed one
        if ($skipUntilFound && !$foundLastHotel) {
            $log->info("Checking if $currentHotelId matches last processed $lastProcessedHotelId");
            
            if ($currentHotelId === $lastProcessedHotelId) {
                $foundLastHotel = true;
                $log->info("FOUND LAST HOTEL! Continuing from next hotel.");
                // Optionally continue to skip processing this exact hotel
                continue; // comment this line if you want to process the last hotel again
            } else {
                $log->info("Skipping hotel $currentHotelId (looking for $lastProcessedHotelId)");
                $skippedCount++;
                continue; // skip until we find the last processed
            }
        }
             
             
             
             
             
             // If we reach this point, we should process the hotel
             $log->info("PROCESSING HOTEL: $currentHotelId");
             
             // Update progress file
             file_put_contents($progressFile, $currentHotelId);
             
             $singleHotelStart = microtime(true);
             $log->info("_____________________________________________________________");
             $log->info("Processing hotel with id: {$currentHotelId}");
             $log->info("Processing hotel : $i/$totalHotelsInRegion");
             echo "\nProcessing hotel with id: {$hotel['hid']}";
             echo "\nProcessing hotel : $i/$totalHotelsInRegion";
             $posts_hotel = new PostsHotel($wpdb, $log);
             $hotel_name = $hotel["id"];
             if(strlen($hotel_name) > 199){
                 echo "\nHotel name was to Long and is skipped: ".$hotel_name;
                 $log->info("Hotel name was to Long and is skipped: ".$hotel_name);
                 $skippedCount++;
                 continue;
             }
             $current_country_code = $hotel['region']['country_code'];

             $log->info("Processing hotel: " . $hotel_name);

             $posts_hotel->post_name = $hotel_name;

             // Clear any pending result sets before checking if hotel exists
             clearWpDb();

             $post_content = '';
             $post_excerpt = '';
             $post_title = $hotel['name'];
             $address = $hotel['address'];
             $star_rating = $hotel['star_rating'];
             $latitude = $hotel['latitude'];
             $longitude = $hotel['longitude'];
             $post_id_name = $hotel['id'];
             $img_urls = '';
             $hotel_location = $hotel['region'];
             $hotel_country_code = $hotel['region'];
             $metapolicy_struct = json_encode($hotel['metapolicy_struct']);

             echo "\n{$hotel['id']}";

             $location_nested = new LocationNested($wpdb);
             $log->info("Country code: " . $current_country_code);
             $location_nested->location_country = $hotel_country_code['country_code'];

             // Execute query and store result before proceeding
             $parent_location_exists_json = $location_nested->parentLocationExists();
             // Make sure to consume the result
             clearWpDb();

             $parent_location_exists = json_decode($parent_location_exists_json);
             $parent_location = $parent_location_exists->ID;
             $parent_location_id = $parent_location_exists->location_id;

             if ($parent_location) {
                 $log->info("Location found in DB $parent_location");
                 $location_nested->parent_id = $parent_location;
             } else {
                 $location_nested->parent_id = $parent_location;
                 $log->info("No parent location found $parent_location");
             }

             $location_nested->location_id = $hotel_location['id'];
             $location_nested->name = $hotel_location['name'];
             $location_nested->language = 'en';
             $location_nested->status = 'publish';

             // Create location and ensure the result is consumed
             $location_result = $location_nested->create();
             clearWpDb();

             foreach ($hotel['description_struct'] as $content) {
                 if ($content['title'] == 'Location') {
                     foreach ($content['paragraphs'] as $paragraph) {
                         $post_excerpt .= $paragraph . "\n\n";
                     }
                 } else {
                     foreach ($content['paragraphs'] as $paragraph) {
                         $post_content .= $paragraph . "\n\n";
                     }
                 }
             }

             $posts_hotel->post_content = $post_content;
             $posts_hotel->post_title = $hotel['name'];
             $posts_hotel->post_excerpt = $post_excerpt;
             $posts_hotel->post_status = 'publish';
             $posts_hotel->post_password = '';
             $posts_hotel->post_name = $post_id_name;
             $posts_hotel->to_ping = '';
             $posts_hotel->pinged = '';
             $posts_hotel->post_content_filtered = '';
             $posts_hotel->guid = '';
             $posts_hotel->post_mime_type = '';

             // Get post and ensure result is consumed
             $post_id = $posts_hotel->get();
             clearWpDb();

             $amenities_model = new ProcessAmenity($wpdb, $log);
             $location_relationships = new LocationRelationship($wpdb, $log);

             if ($post_id) {
                 echo "\nHotel found in DB";

                 $posts_hotel->id = $post_id;
                 $updated_id = $posts_hotel->update();
                 clearWpDb();

                 if ($hotel['amenity_groups'] != null && count($hotel['amenity_groups']) > 0) {
                     $log->info("Processing amenities for hotel");
                     $amenities_model->amenities = $hotel['amenity_groups'][0];
                     $amenities_model->post_id = $updated_id;
                     $amenities = $amenities_model->getAmenities();
                     $log->info("Finished processing amenities");
                     clearWpDb();
                 } else {
                     $log->info("No amenities found for hotel");
                 }

                 $location_relationships->post_id = $post_id;
                 $location_relationships->location_from = array($parent_location_id, $hotel_location['id']);
                 $location_relationships->location_to = 0;
                 $location_relationships->post_type = 'st_hotel';
                 $location_relationships->location_type = 'multi_location';

                 $location_result = $location_relationships->insertLocationRelationship();
                 clearWpDb();
             } else {
                 echo "\nHotel not found in DB";
                 logNewHotelHid($hotel['hid'], $country);
                 $post_id = $posts_hotel->create();
                 clearWpDb();

                 if (is_array($hotel['amenity_groups']) && count($hotel['amenity_groups']) > 0) {
                     $amenities_model->amenities = $hotel['amenity_groups'][0];
                 } else {
                     $amenities_model->amenities = array();
                 }

                 $amenities_model->post_id = $post_id;

                 $amenities = $amenities_model->getAmenities();
                 clearWpDb();

                 $location_relationships->post_id = $post_id;
                 $location_relationships->location_from = array($parent_location_id, $hotel_location['id']);
                 $location_relationships->location_to = 0;
                 $location_relationships->post_type = 'st_hotel';
                 $location_relationships->location_type = 'multi_location';

                 $location_result = $location_relationships->insertLocationRelationship();
                 clearWpDb();
             }

             $prices = array();

             $price_avg = 0;
             $price_min = 0;
             $room_id;

             $posts_room = new PostsRoom($wpdb, $log);
             $post_meta = new PostMetaValues($wpdb, $log);
             $post_images = new ImageInserter($wpdb, $log);

             try {
                 $counter = 0;

                 foreach ($hotel['room_groups'] as $room) {
                     // Clear any existing result sets
                     clearWpDb();

                     $posts_room->post_title = $room['name'];
                     $posts_room->post_content = $post_content;
                     $posts_room->post_excerpt = $post_excerpt;
                     $posts_room->post_status = 'publish';
                     $posts_room->post_password = '';
                     $posts_room->post_name = reliable_sanitize_title($post_id_name . '-' . $room['name']);
                     $posts_room->to_ping = '';
                     $posts_room->pinged = '';
                     $posts_room->post_content_filtered = '';
                     $posts_room->post_parent = $post_id;
                     $posts_room->guid = '';
                     $posts_room->post_mime_type = '';

                     $posts_room_exsists = $posts_room->get();
                     clearWpDb();

                     $post_images->hotel = $room;
                     $post_images->directory_url = ''; //$hotel['id'] . '/' . str_replace(' ', '-', $room['name_struct']['main_name']);
                     $post_images->post_title = $hotel['name'] . ' - ' . $room['name'];
                     $post_images->post_id_name = $post_id_name;
                     $post_images->provider = 'RateHawk';
                     $post_images->default_image = $room['images'];

                     $post_meta->meta_values = array(
                        /* 'rate_review' => 0,
                         'min_price' => $price_min,
                         'meta_value' => 'classic-editor',
                         '_edit_lock' => '1720094804:14',
                         '_edit_last' => 14,
                         'discount_type' => 'percent',
                         'room_parent' => $post_id,
                         'number_room' => $room['room_group_id'],
                         'st_booking_option_type' => 'instant',
                         'st_custom_layout' => 3,
                         'disable_adult_name' => 'off',
                         'disable_children_name' => 'on',
                         'price_by_per_person' => 'off',
                         'allow_full_day' => 'on',
                         'price' => 0,
                         'discount_type_no_day' => 'percent',
                         'extra_price_unit' => 'perday',
                         'adult_number' => 9,
                         'children_number' => 9,
                         'st_room_external_booking' => 'off',
                         'default_state' => 'available',
                         'st_allow_cancel' => 'off',
                         'st_cancel_percent' => 0,
                         'is_meta_payment_gateway_st_submit_form' => 'on',
                         'is_meta_payment_gateway_vina_stripe' => 'on',
                         'multi_location' => '_' . $hotel_location['id'] . '_,_' . $parent_location_id . '_',
                         '_yoast_wpseo_primary_room_type' => 66,
                         '_yoast_wpseo_primary_room-facilities' => 43,
                         '_yoast_wpseo_focuskw' => $room['name_struct']['main_name'],
                         '_yoast_wpseo_metadesc' => $room['name_struct']['main_name'] . ' in ' . $hotel['name'],
                         '_yoast_wpseo_linkdex' => 40,
                         '_yoast_wpseo_content_score' => 90,
                         '_yoast_wpseo_estimated-reading-time-minutes' => null,
                         'bed_number' => $room['rg_ext']['capacity'] == 0 ? 1 : $room['rg_ext']['capacity'],
                         'id_location' => '',
                         'location_id' => '',
                         '_thumbnail_id' => 0,
                         'gallery' => '',
                         'address' => $address,
                         '_wp_old_slug' => $hotel['id'] . '-' . str_replace(
                                 ' ',
                                 '_',
                                 $room['name_struct']['main_name']
                             ),*/
                     );

                     if ($posts_room_exsists) {
                         $post_room_id = $posts_room_exsists->ID;
                         $posts_room->id = $post_room_id;
                         $posts_room->id = $post_room_id;
                         $post_meta->post_id = $post_room_id;
                         $post_images->post_id = $post_room_id;

                         $post_image_array_ids = $post_images->insertImages('room');
                         clearWpDb();

                       /*  $post_meta->meta_values['_thumbnail_id'] = $post_image_array_ids != null ? explode(
                             ",",
                             $post_image_array_ids
                         )[0] : '';*/
                         /*$post_meta->meta_values['gallery'] = $post_image_array_ids ?? '';*/

                         $update_result = $posts_room->update();
                         clearWpDb();

                         $meta_update_result = $post_meta->update();
                         clearWpDb();
                     } else {
                         $post_room_id = $posts_room->create();
                         clearWpDb();

                         $post_meta->post_id = $post_room_id;
                         $post_images->post_id = $post_room_id;

                         $post_image_array_ids = $post_images->insertImages('room');
                         clearWpDb();

                         /*$post_meta->meta_values['_thumbnail_id'] = $post_image_array_ids != null ? explode(
                             ",",
                             $post_image_array_ids
                         )[0] : '';
                         $post_meta->meta_values['gallery'] = $post_image_array_ids ?? '';*/

                         $meta_create_result = $post_meta->create();
                         clearWpDb();
                     }

                     $amenities_model->amenities = $room['room_amenities'];
                     $amenities_model->post_id = $post_room_id;

                     $amenities = $amenities_model->getRoomAmenities();
                     clearWpDb();

                     $hotel_room_model = new HotelRoom($wpdb, $log);

                     $hotel_room_model->post_id = $post_room_id;
                     $hotel_room_model->room_parent = $post_id;
                     $hotel_room_model->multi_location = '_' . $hotel_location['id'] . '_,_' . $parent_location_id . '_';
                     $hotel_room_model->id_location = '';
                     $hotel_room_model->address = $address;
                     $hotel_room_model->allow_full_day = 'on';
                     $hotel_room_model->price = 0;
                     $hotel_room_model->number_room = $room['rg_ext']['capacity'] == 0 ? 1 : $room['rg_ext']['capacity'];
                     $hotel_room_model->discount_rate = '';
                     $hotel_room_model->adult_number = 2;
                     $hotel_room_model->child_number = 0;
                     $hotel_room_model->status = 'draft';
                     $hotel_room_model->adult_price = 0;
                     $hotel_room_model->child_price = 0;
                     $hotel_room_model->main_name = $room['name_struct']['main_name'];
                     $hotel_room_model->main_name_slug = reliable_sanitize_title($room['name_struct']['main_name']);

                     $room_id = $post_room_id;

                     $hotel_room = $hotel_room_model->get();
                     clearWpDb();

                     if ($hotel_room) {
                         $hotel_room_update = $hotel_room_model->update();
                         clearWpDb();
                     } else {
                         $hotel_room_create = $hotel_room_model->create();
                         clearWpDb();
                     }
                 }

                 if ($wpdb->last_error) {
                     $log->info("wpdb last error after processing rooms: " . $wpdb->last_error);
                 } else {
                     $log->info("All rooms processed successfully");
                 }
             } catch (Exception $e) {
                 $log->info("Caught exception in rooms processing: " . $e->getMessage() . " - " . $e->getTraceAsString());
             }

             //ST_Hotel
             $st_hotel = new St_Hotel($wpdb, $log);

             $st_hotel->post_id = (int)$post_id;
             $st_hotel->address = $address;
             $st_hotel->rate_review = 0;
             $st_hotel->hotel_star = $star_rating;
             $st_hotel->price_avg = $price_avg;
             $st_hotel->min_price = $price_min;
             $st_hotel->map_lat = $latitude;
             $st_hotel->map_lng = $longitude;
             $st_hotel->external_hid = $hotel['hid'];
             $st_hotel->external_id = $hotel['id'];
             $st_hotel->check_in_time = substr($hotel['check_in_time'], 0, -3);
             $st_hotel->check_out_time = substr($hotel['check_out_time'], 0, -3);
             $st_hotel->policy_struct = json_encode($hotel['policy_struct']);
             $st_hotel->metapolicy_struct = $metapolicy_struct;

             if ($st_hotel->get()) {
                 $log->info("Update ST hotel table: $post_id");
                 $st_hotel_update = $st_hotel->update();
                 clearWpDb();
             } else {
                 $st_hotel_create = $st_hotel->create();
                 clearWpDb();
             }

             $post_image_array_ids = '';

             $post_images->hotel = $hotel;
             $post_images->directory_url = $hotel['id'];
             $post_images->post_title = $post_title . ' - ' . $hotel['hid'];
             $post_images->post_id_name = $post_id_name;
             $post_images->post_id = $post_id;
             $post_images->provider = 'RateHawk';
             $post_images->default_image = $hotel['images'];

             $post_meta = new PostMetaValues($wpdb, $log);
             $post_meta->post_id = $post_id;

             $post_image_array_ids = $post_images->insertImages('hotel');
             clearWpDb();

             $post_meta->meta_values = array(
                 /*'rate_review' => 0,
                 'price_avg' => $price_avg,
                 'min_price' => $price_min,
                 'meta_value' => 'classic-editor',
                 '_edit_lock' => '1720094804:14',
                 '_edit_last' => 14,
                 '_tve_js_modules_gutenberg' => 'a:0:{}',
                 'st_google_map' => 'a:4:{s:3:"lat";s:' . strlen(
                         $latitude
                     ) . ':"' . $latitude . '";s:3:"lng";s:' . strlen(
                         $longitude
                     ) . ':"' . $longitude . '";s:4:"zoom";s:2:"13";s:4:"type";s:0:"";}',
                 'multi_location' => '_' . $hotel_location['id'] . '_,_' . $parent_location_id . '_',
                 'address' => $address,
                 'is_featured' => 'off',
                 'st_hotel_external_booking' => 'off',
                 'hotel_star' => $star_rating,
                 'is_auto_caculate' => 'on',
                 'allow_full_day' => 'on',
                 'check_in_time' => substr($hotel['check_in_time'], 0, -3), //todo to be removed
                 'check_out_time' => substr($hotel['check_out_time'], 0, -3),//todo to be removed
                 'hotel_booking_period' => 0,
                 'min_book_room' => 0,
                 'id_location' => '',
                 'location_id' => '',
                 'map_lat' => $latitude,
                 'map_lng' => $longitude,
                 'map_zoom' => 13,
                 'map_type' => '',
                 '_yoast_wpseo_primary_hotel-theme' => 27,
                 '_yoast_wpseo_primary_hotel-facilities' => 425,
                 '_yoast_wpseo_focuskw' => $post_title,
                 '_yoast_wpseo_metadesc' => $post_excerpt,
                 '_yoast_wpseo_linkdex' => 71,
                 '_yoast_wpseo_content_score' => 60,
                 '_yoast_wpseo_estimated-reading-time-minutes' => null,
                 'hotel_layout_style' => 5,
                 'hotel_policy' => 'a:1:{i:0;a:2:{s:5:"title";s:0:"";s:18:"policy_description";s:' . strlen(
                         $hotel['metapolicy_extra_info'] ?? ''
                     ) . ':"' . $hotel['metapolicy_extra_info'] ?? '' . '";}}', //todo to be removed it is used in st_hotels
                 '_thumbnail_id' => $post_image_array_ids != null ? explode(",", $post_image_array_ids)[0] : '',
                 'gallery' => $post_image_array_ids ?? '',
                 '_wp_old_date' => date('YYYY-mm-dd'),
                 'provider' => 'RateHawk',
                 'metapolicy_struct' => $metapolicy_struct //todo to be removed it is used in st_hotels*/
             );

             $meta_exists = $post_meta->getAll();
             clearWpDb();

             if ($meta_exists) {
                 $hotel_meta_update = $post_meta->update();
                 clearWpDb();
             } else {
                 $hotel_meta_create = $post_meta->create();
                 clearWpDb();
             }

             $singleHotelEnd = microtime(true);

             $singleHotelTotal = $singleHotelEnd - $singleHotelStart;

             $regionTotal += $singleHotelTotal;

             $log->info("Completed processing hotel: " . $hotel_name . " in " . number_format($singleHotelTotal, 4));
             $processedCount++;

             // Final flush of any pending results
             clearWpDb();
             //sleep(15);

         } catch (\Exception $ex) {
             $log->info("CRITICAL ERROR: " . $ex->getMessage());
             $log->info("Error stack trace: " . $ex->getTraceAsString());
             // Make sure to flush any pending results even on error
             clearWpDb();
         }
     }
     
     $log->info("Processing completed. Total: $i, Processed: $processedCount, Skipped: $skippedCount");

     // Clean up progress file when done
     if (file_exists($progressFile)&&($i+1)===$totalHotelsInRegion) {
         unlink($progressFile);
         $log->info("Completed all hotels, progress file removed");
     }
     
     echo 'SUCESS';
     $log->info(
         "Successfully processed all hotels in Region: " . $country . " in " . number_format(
             $regionTotal,
             4
         )
     );
} catch (\Exception $ex) {
    $log->info("CRITICAL ERROR: " . $ex->getMessage());
    $log->info("Error stack trace: " . $ex->getTraceAsString());
    // Make sure to flush any pending results even on fatal error
    if (isset($wpdb) && $wpdb) {
        clearWpDb();
    }
}
function readJsonl(string $filePath): Generator {
    $log = new Log('file_reader');
    $log->info("Attempting to open file: $filePath");
    
    $handle = fopen($filePath, 'r');
    if (!$handle) {
        $error = error_get_last();
        $log->info("Unable to open file: $filePath. Error: " . $error['message']);
        throw new RuntimeException("Unable to open file: $filePath. Error: " . $error['message']);
    }

    $lineCount = 0;
    while (!feof($handle)) {
        $line = fgets($handle);
        if ($line === false) {
            break;
        }

        $line = trim($line);
        if ($line === '') {
            continue;
        }
        
        $lineCount++;
        if ($lineCount % 1000 === 0) {
            $log->info("Read $lineCount lines from file");
        }
        
        yield $line;
    }

    fclose($handle);
    $log->info("Finished reading file. Total lines: $lineCount");
}
function fastLineCount(string $filePath): int {
    $file = new SplFileObject($filePath, 'r');
    $lines = 0;

    while (!$file->eof()) {
        $file->current();  // forces reading line
        $file->next();
        $lines++;
    }

    return $lines;
}
function reliable_sanitize_title($string) {

    $char_map = [
        '°' => '', '–' => '-', '—' => '-', '…' => '...',
        '“' => '"', '”' => '"', '‘' => "'", '’' => "'",
        '©' => '(c)', '®' => '(r)', '€' => 'euro', '£' => 'pound',
        'Č' => 'C', 'č' => 'c', 'Ć' => 'C', 'ć' => 'c',
        'Ž' => 'Z', 'ž' => 'z', 'Đ' => 'Dj', 'đ' => 'dj',
        'Š' => 'S', 'š' => 's',
    ];

    $string = strtr($string, $char_map);

    if (function_exists('iconv')) {
        try {
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
            if ($converted !== false) {
                $string = $converted;
            } else {
                $string = preg_replace('/[^\x20-\x7E]/', '', $string);
            }
        } catch (Throwable $e) {
            $string = preg_replace('/[^\x20-\x7E]/', '', $string);
        }
    } else {
        $string = preg_replace('/[^\x20-\x7E]/', '', $string);
    }

    try {
        $sanitized = sanitize_title($string);
        if (!is_string($sanitized)) {
            
            $sanitized = preg_replace('/[^a-zA-Z0-9\-]/', '-', strtolower($string));
        }
    } catch (Throwable $e) {
   
        $sanitized = preg_replace('/[^a-zA-Z0-9\-]/', '-', strtolower($string));
    }
    $sanitized = mb_substr($sanitized, 0, 199, 'UTF-8');

    return $sanitized;
}

?>