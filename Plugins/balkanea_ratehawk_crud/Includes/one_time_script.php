<?php
// import_hotel_amenities_optimized.php
// ===============================================
// OPTIMIZED Script for importing amenities from JSONL files
// ===============================================

echo "Starting OPTIMIZED hotel amenities import...\n";

// Load WordPress
$path = realpath(__DIR__ . '/../../../../');
require_once $path . '/wp-load.php';

global $wpdb;
$wpdb->show_errors();
$prefix = $wpdb->prefix;

// Configuration
$base_path = '/home/balkanea/public_html/CRUD_Data/';
$hotels_path = $base_path . 'hotels/';
$log_path = $base_path . 'logs/';
$log_file = $log_path . 'amenitiesImport_optimized.log';
$status_file = $log_path . 'amenitiesImportStatus_optimized.json';

// Performance settings
define('BATCH_SIZE', 50);
define('MEMORY_LIMIT', '512M');
define('MAX_EXECUTION_TIME', 3600);
ini_set('memory_limit', MEMORY_LIMIT);
set_time_limit(MAX_EXECUTION_TIME);

// Create logs dir if missing
if (!file_exists($log_path) && !mkdir($log_path, 0755, true)) {
    die("Cannot create log directory: $log_path\n");
}

// -----------------------------
// Enhanced Status Management
// -----------------------------
class ImportStatusManager {
    private $status_file;
    private $status;
    
    public function __construct($status_file) {
        $this->status_file = $status_file;
        $this->loadStatus();
    }
    
    private function loadStatus() {
        if (!file_exists($this->status_file)) {
            $this->status = [
                'processed_files' => [],
                'last_country' => null,
                'last_file' => null,
                'last_position' => 0,
                'total_processed' => 0,
                'start_time' => date('Y-m-d H:i:s'),
                'stats' => [
                    'hotels_found' => 0,
                    'hotels_processed' => 0,
                    'rooms_processed' => 0,
                    'amenities_created' => 0,
                    'groups_created' => 0
                ]
            ];
        } else {
            $this->status = json_decode(file_get_contents($this->status_file), true);
        }
    }
    
    public function saveStatus() {
        $this->status['last_save'] = date('Y-m-d H:i:s');
        file_put_contents($this->status_file, json_encode($this->status, JSON_PRETTY_PRINT));
    }
    
    public function getStatus() {
        return $this->status;
    }
    
    public function updateStatus($updates) {
        $this->status = array_merge($this->status, $updates);
    }
    
    public function incrementStat($stat_name, $value = 1) {
        if (isset($this->status['stats'][$stat_name])) {
            $this->status['stats'][$stat_name] += $value;
        }
    }
}

// -----------------------------
// Enhanced Logging with performance tracking
// -----------------------------
class PerformanceLogger {
    private $log_file;
    private $start_time;
    private $memory_peak;
    
    public function __construct($log_file) {
        $this->log_file = $log_file;
        $this->start_time = microtime(true);
        $this->memory_peak = memory_get_usage();
    }
    
    public function log($message, $level = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $memory_usage = round(memory_get_usage(true) / 1024 / 1024, 2);
        $log_entry = "[$timestamp] [$level] [Mem: {$memory_usage}MB] $message" . PHP_EOL;
        
        file_put_contents($this->log_file, $log_entry, FILE_APPEND | LOCK_EX);
        echo $log_entry;
    }
    
    public function logPerformance($processed_count) {
        $execution_time = round(microtime(true) - $this->start_time, 2);
        $memory_peak = round(memory_get_peak_usage(true) / 1024 / 1024, 2);
        $rate = $execution_time > 0 ? round($processed_count / $execution_time, 2) : 0;
        
        $this->log("PERFORMANCE: $processed_count items in {$execution_time}s ($rate/sec), Peak memory: {$memory_peak}MB");
    }
}

// -----------------------------
// Database Cache Manager
// -----------------------------
class DatabaseCache {
    private $wpdb;
    private $prefix;
    private $cache = [];
    
    public function __construct($wpdb, $prefix) {
        $this->wpdb = $wpdb;
        $this->prefix = $prefix;
    }
    
    // Cache for hotels
    public function getHotelId($external_hid) {
        if (!isset($this->cache['hotels'][$external_hid])) {
            $this->cache['hotels'][$external_hid] = $this->wpdb->get_var($this->wpdb->prepare(
                "SELECT p.ID FROM {$this->prefix}posts p 
                 INNER JOIN {$this->prefix}st_hotel sh ON p.ID = sh.post_id 
                 WHERE sh.external_hid = %s AND p.post_type = 'st_hotel'",
                $external_hid
            ));
        }
        return $this->cache['hotels'][$external_hid];
    }
    
    // Batch cache for rooms (dramatically improves performance)
    public function cacheRoomsForHotel($hotel_post_id) {
        if (!isset($this->cache['rooms'][$hotel_post_id])) {
            $rooms = $this->wpdb->get_results($this->wpdb->prepare(
                "SELECT hr.id as hotel_room_id, hr.post_id, hr.main_name, p.post_title 
                 FROM {$this->prefix}hotel_room hr 
                 INNER JOIN {$this->prefix}posts p ON hr.post_id = p.ID 
                 WHERE hr.room_parent = %d AND p.post_type = 'hotel_room'",
                $hotel_post_id
            ));
            
            $this->cache['rooms'][$hotel_post_id] = [];
            foreach ($rooms as $room) {
                $key = $this->normalizeRoomName($room->main_name ?: $room->post_title);
                $this->cache['rooms'][$hotel_post_id][$key] = $room;
            }
        }
    }
    
    public function findRoom($hotel_post_id, $room_name) {
        $this->cacheRoomsForHotel($hotel_post_id);
        $normalized_name = $this->normalizeRoomName($room_name);
        
        return $this->cache['rooms'][$hotel_post_id][$normalized_name] ?? null;
    }
    
    private function normalizeRoomName($name) {
        return trim(mb_strtolower($name));
    }
    
    // Cache for amenities and groups
    public function cacheAmenitiesAndGroups() {
        if (!isset($this->cache['amenities'])) {
            // Cache all existing amenities
            $amenities = $this->wpdb->get_results("SELECT id, slug, name, is_free FROM {$this->prefix}amenities");
            $this->cache['amenities'] = [];
            foreach ($amenities as $amenity) {
                $this->cache['amenities'][$amenity->slug] = $amenity;
            }
            
            // Cache all existing groups
            $groups = $this->wpdb->get_results("SELECT id, slug, name FROM {$this->prefix}amenity_groups");
            $this->cache['groups'] = [];
            foreach ($groups as $group) {
                $this->cache['groups'][$group->slug] = $group;
            }
        }
    }
    
    public function getCachedAmenity($slug) {
        return $this->cache['amenities'][$slug] ?? null;
    }
    
    public function getCachedGroup($slug) {
        return $this->cache['groups'][$slug] ?? null;
    }
    
    public function addToCache($type, $key, $value) {
        $this->cache[$type][$key] = $value;
    }
}

// -----------------------------
// Batch Processing Manager
// -----------------------------
class BatchProcessor {
    private $wpdb;
    private $prefix;
    private $logger;
    private $cache;
    private $batch_data = [];
    
    public function __construct($wpdb, $prefix, $logger, $cache) {
        $this->wpdb = $wpdb;
        $this->prefix = $prefix;
        $this->logger = $logger;
        $this->cache = $cache;
    }
    
    public function addAmenity($amenity_name, $is_free = true) {
        $slug = sanitize_title($amenity_name);
        
        if (!isset($this->batch_data['amenities'][$slug])) {
            $this->batch_data['amenities'][$slug] = [
                'name' => $amenity_name,
                'slug' => $slug,
                'is_free' => $is_free
            ];
        }
    }
    
    public function addGroup($group_name) {
        $slug = sanitize_title($group_name);
        
        if (!isset($this->batch_data['groups'][$slug])) {
            $this->batch_data['groups'][$slug] = [
                'name' => $group_name,
                'slug' => $slug
            ];
        }
    }
    
    public function processBatch() {
        $processed = 0;
        
        // Process groups batch
        if (!empty($this->batch_data['groups'])) {
            $processed += $this->processGroupsBatch();
        }
        
        // Process amenities batch
        if (!empty($this->batch_data['amenities'])) {
            $processed += $this->processAmenitiesBatch();
        }
        
        $this->batch_data = [];
        return $processed;
    }
    
    private function processGroupsBatch() {
        $values = [];
        $slugs = [];
        
        foreach ($this->batch_data['groups'] as $slug => $group) {
            if (!$this->cache->getCachedGroup($slug)) {
                $values[] = $this->wpdb->prepare("(%s, %s, %s)", 
                    $group['name'], $slug, '');
                $slugs[] = $slug;
            }
        }
        
        if (empty($values)) return 0;
        
        $query = "INSERT IGNORE INTO {$this->prefix}amenity_groups (name, slug, description) VALUES " . implode(', ', $values);
        $result = $this->wpdb->query($query);
        
        if ($result !== false) {
            $this->logger->log("Created " . count($values) . " amenity groups in batch");
            
            // Update cache
            $new_groups = $this->wpdb->get_results("SELECT id, slug FROM {$this->prefix}amenity_groups WHERE slug IN ('" . implode("','", $slugs) . "')");
            foreach ($new_groups as $group) {
                $this->cache->addToCache('groups', $group->slug, $group);
            }
            
            return count($values);
        }
        
        return 0;
    }
    
    private function processAmenitiesBatch() {
        $values = [];
        $slugs = [];
        
        foreach ($this->batch_data['amenities'] as $slug => $amenity) {
            if (!$this->cache->getCachedAmenity($slug)) {
                $values[] = $this->wpdb->prepare("(%s, %s, %s, %d, %d)", 
                    $amenity['name'], $slug, '', $amenity['is_free'], 0);
                $slugs[] = $slug;
            }
        }
        
        if (empty($values)) return 0;
        
        $query = "INSERT IGNORE INTO {$this->prefix}amenities (name, slug, description, is_free, popularity) VALUES " . implode(', ', $values);
        $result = $this->wpdb->query($query);
        
        if ($result !== false) {
            $this->logger->log("Created " . count($values) . " amenities in batch");
            
            // Update cache
            $new_amenities = $this->wpdb->get_results("SELECT id, slug, is_free FROM {$this->prefix}amenities WHERE slug IN ('" . implode("','", $slugs) . "')");
            foreach ($new_amenities as $amenity) {
                $this->cache->addToCache('amenities', $amenity->slug, $amenity);
            }
            
            return count($values);
        }
        
        return 0;
    }
}

// -----------------------------
// Optimized JSONL Processor
// -----------------------------
class JsonlProcessor {
    private $wpdb;
    private $prefix;
    private $logger;
    private $status_manager;
    private $cache;
    private $batch_processor;
    
    public function __construct($wpdb, $prefix, $logger, $status_manager, $cache, $batch_processor) {
        $this->wpdb = $wpdb;
        $this->prefix = $prefix;
        $this->logger = $logger;
        $this->status_manager = $status_manager;
        $this->cache = $cache;
        $this->batch_processor = $batch_processor;
    }
    
    public function processFile($filename, $country_code) {
        $full_path = $GLOBALS['hotels_path'] . $filename;
        
        if (!file_exists($full_path)) {
            $this->logger->log("File does not exist: $full_path", 'ERROR');
            return false;
        }
        
        $status = $this->status_manager->getStatus();
        
        // Check if file already processed
        if (in_array($filename, $status['processed_files'])) {
            $this->logger->log("File already processed: $filename", 'INFO');
            return true;
        }
        
        $this->logger->log("Processing JSONL file: $filename");
        
        $file_handle = fopen($full_path, 'r');
        if (!$file_handle) {
            $this->logger->log("Cannot open file: $full_path", 'ERROR');
            return false;
        }
        
        // Skip to last position if resuming
        if ($status['last_file'] === $filename && $status['last_position'] > 0) {
            fseek($file_handle, $status['last_position']);
            $this->logger->log("Resuming from position: {$status['last_position']}");
        }
        
        $line_count = 0;
        $processed_count = 0;
        $batch_count = 0;
        
        while (($line = fgets($file_handle)) !== false) {
            $line_count++;
            $current_position = ftell($file_handle);
            $line = trim($line);
            
            if (empty($line)) continue;
            
            $hotel_data = json_decode($line, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logger->log("Invalid JSON on line $line_count in file: $filename", 'ERROR');
                continue;
            }
            
            if ($this->processHotelData($hotel_data)) {
                $processed_count++;
                $batch_count++;
            }
            
            // Update status and save periodically
            $status_updates = [
                'last_position' => $current_position,
                'last_file' => $filename,
                'last_country' => $country_code
            ];
            $this->status_manager->updateStatus($status_updates);
            
            // Process batch and save status every BATCH_SIZE hotels
            if ($batch_count >= BATCH_SIZE) {
                $this->batch_processor->processBatch();
                $this->status_manager->saveStatus();
                $this->logger->logPerformance($processed_count);
                $batch_count = 0;
            }
        }
        
        fclose($file_handle);
        
        // Process remaining batch items
        if ($batch_count > 0) {
            $this->batch_processor->processBatch();
        }
        
        // Mark file as completed
        $status = $this->status_manager->getStatus();
        $status['processed_files'][] = $filename;
        $status['last_position'] = 0;
        $status['last_file'] = null;
        $this->status_manager->updateStatus($status);
        $this->status_manager->saveStatus();
        
        $this->logger->log("Completed file: $filename - $processed_count hotels processed");
        return true;
    }
    
    private function processHotelData($hotel_data) {
        $hotel_hid = $hotel_data['hid'] ?? null;
        if (!$hotel_hid) {
            $this->logger->log("No HID found in hotel data", 'ERROR');
            return false;
        }
        
        $this->status_manager->incrementStat('hotels_found');
        
        // Check if hotel exists using cache
        $hotel_post_id = $this->cache->getHotelId($hotel_hid);
        if (!$hotel_post_id) {
            $this->logger->log("Hotel $hotel_hid not found in database, skipping", 'WARNING');
            return false;
        }
        
        $this->logger->log("Processing hotel HID: $hotel_hid");
        
        // Pre-cache rooms for this hotel
        $this->cache->cacheRoomsForHotel($hotel_post_id);
        
        // Process hotel amenities
        $amenity_groups = $hotel_data['amenity_groups'] ?? [];
        $hotel_processed = $this->processHotelAmenities($hotel_hid, $amenity_groups);
        
        // Process room amenities
        $room_groups = $hotel_data['room_groups'] ?? [];
        $room_processed = $this->processRoomAmenities($hotel_post_id, $room_groups);
        
        $this->logger->log("Completed hotel $hotel_hid - Hotel amenities: $hotel_processed, Room amenities: $room_processed");
        
        $this->status_manager->incrementStat('hotels_processed');
        $this->status_manager->incrementStat('rooms_processed', $room_processed);
        
        return true;
    }
    
    private function processHotelAmenities($hotel_hid, $amenity_groups) {
        if (empty($amenity_groups)) return 0;
        
        $processed = 0;
        
        foreach ($amenity_groups as $group_data) {
            $group_name = $group_data['group_name'] ?? 'Unknown Group';
            $amenities = $group_data['amenities'] ?? [];
            $non_free_amenities = $group_data['non_free_amenities'] ?? [];
            
            // Add to batch processor
            $this->batch_processor->addGroup($group_name);
            
            // Process amenities
            foreach ($amenities as $amenity_name) {
                $this->batch_processor->addAmenity($amenity_name, true);
            }
            
            foreach ($non_free_amenities as $amenity_name) {
                $this->batch_processor->addAmenity($amenity_name, false);
            }
            
            $processed++;
        }
        
        return $processed;
    }
    
    private function processRoomAmenities($hotel_post_id, $room_groups) {
        if (empty($room_groups)) return 0;
        
        $processed = 0;
        
        foreach ($room_groups as $room_data) {
            $room_name = $room_data['name'] ?? '';
            $room_amenities = $room_data['room_amenities'] ?? [];
            
            if (empty($room_name)) continue;
            
            // Find room using cache
            $room = $this->cache->findRoom($hotel_post_id, $room_name);
            
            if (!$room) {
                $this->logger->log("Room not found in database: '$room_name' for hotel ID $hotel_post_id", 'WARNING');
                continue;
            }
            
            $this->logger->log("Processing room: {$room->post_title} (ID: {$room->post_id})");
            
            // Process room amenities
            foreach ($room_amenities as $amenity_slug) {
                $amenity_name = ucwords(str_replace('-', ' ', $amenity_slug));
                $this->batch_processor->addAmenity($amenity_name, true);
                $processed++;
            }
        }
        
        return $processed;
    }
}

// -----------------------------
// MAIN EXECUTION - OPTIMIZED
// -----------------------------
try {
    // Initialize components
    $status_manager = new ImportStatusManager($status_file);
    $logger = new PerformanceLogger($log_file);
    $cache = new DatabaseCache($wpdb, $prefix);
    $batch_processor = new BatchProcessor($wpdb, $prefix, $logger, $cache);
    $jsonl_processor = new JsonlProcessor($wpdb, $prefix, $logger, $status_manager, $cache, $batch_processor);
    
    // Pre-cache existing amenities and groups
    $logger->log("Caching existing amenities and groups...");
    $cache->cacheAmenitiesAndGroups();
    
    $logger->log("Starting optimized import process...");
    
    if ($argc == 2 && $argv[1] === '--resume') {
        $status = $status_manager->getStatus();
        if ($status['last_country']) {
            $logger->log("Resuming import for country: {$status['last_country']}");
            // Resume logic would go here
        } else {
            $logger->log("No previous import to resume", 'ERROR');
        }
    } elseif ($argc == 3 && $argv[1] === '--country') {
        $country_code = strtoupper($argv[2]);
        $logger->log("Processing country: $country_code");
        
        $pattern = "extract_{$country_code}_*.jsonl";
        $files = glob($hotels_path . $pattern);
        
        if (!$files) {
            $logger->log("No JSONL files found for country: $country_code", 'WARNING');
            exit(1);
        }
        
        foreach ($files as $file) {
            $filename = basename($file);
            $jsonl_processor->processFile($filename, $country_code);
        }
        
    } elseif ($argc == 2 && $argv[1] === '--all') {
        $countries = ['AL', 'GR', 'MK', 'ME', 'RS', 'BG', 'RO', 'TR', 'HR', 'BA'];
        
        foreach ($countries as $country_code) {
            $logger->log("Processing country: $country_code");
            
            $pattern = "extract_{$country_code}_*.jsonl";
            $files = glob($hotels_path . $pattern);
            
            if ($files) {
                foreach ($files as $file) {
                    $filename = basename($file);
                    $jsonl_processor->processFile($filename, $country_code);
                }
            }
        }
    } else {
        $logger->log("Usage: php " . $argv[0] . " --country <code> OR php " . $argv[0] . " --all OR php " . $argv[0] . " --resume", 'ERROR');
        die("Usage: php " . $argv[0] . " --country <code> OR php " . $argv[0] . " --all OR php " . $argv[0] . " --resume\n");
    }
    
    $status = $status_manager->getStatus();
    $logger->log("IMPORT COMPLETED! Statistics:");
    $logger->log("Hotels found: " . $status['stats']['hotels_found']);
    $logger->log("Hotels processed: " . $status['stats']['hotels_processed']);
    $logger->log("Rooms processed: " . $status['stats']['rooms_processed']);
    $logger->log("Total processed: " . $status['total_processed']);
    
    $logger->logPerformance($status['total_processed']);
    
} catch (Exception $e) {
    $logger->log("Fatal error: " . $e->getMessage(), 'ERROR');
}