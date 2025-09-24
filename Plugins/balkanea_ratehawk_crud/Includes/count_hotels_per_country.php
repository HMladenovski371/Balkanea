<?php
/**
 * Script to count the number of hotels per country from the WordPress database
 */

// Define WordPress path and load WordPress
$path = realpath(__DIR__ . '/../../../../');
define('SHORTINIT', true);
define('WP_USE_THEMES', false);
$_SERVER['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

// Load WordPress
require_once $path . '/wp-load.php';
global $wpdb;

// Log directory
$logDir = '/home/balkanea/public_html/CRUD_Data/logs/';

// Create logs directory if it doesn't exist
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// Function to log messages
function logMessage($message, $logDir) {
    $logFile = $logDir . 'hotel_count.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    echo $logEntry;
}

try {
    logMessage("Starting hotel count per country from database", $logDir);
    
    // Query to count hotels per country
    $query = "
        SELECT 
            pm2.meta_value as country_code,
            COUNT(DISTINCT p.ID) as hotel_count
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'multi_location'
        INNER JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'address'
        WHERE p.post_type = 'st_hotel' 
        AND p.post_status = 'publish'
        GROUP BY country_code
        ORDER BY hotel_count DESC
    ";
    
    $results = $wpdb->get_results($query);
    
    if (empty($results)) {
        logMessage("No hotels found in the database", $logDir);
        echo "No hotels found in the database\n";
    } else {
        $totalHotels = 0;
        $csvData = [['Country Code', 'Hotel Count']];
        
        logMessage("Hotel count by country:", $logDir);
        foreach ($results as $row) {
            // Extract country code from address (assuming it's the last part)
            $addressParts = explode(',', $row->country_code);
            $country = trim(end($addressParts));
            
            logMessage("Country: $country, Hotels: {$row->hotel_count}", $logDir);
            echo "Country: $country, Hotels: {$row->hotel_count}\n";
            
            $csvData[] = [$country, $row->hotel_count];
            $totalHotels += $row->hotel_count;
        }
        
        logMessage("Total hotels across all countries: $totalHotels", $logDir);
        echo "Total hotels across all countries: $totalHotels\n";
        
        // Save results to a CSV file
        $extractDir = '/home/balkanea/public_html/CRUD_Data/extracts/';
        if (!is_dir($extractDir)) {
            mkdir($extractDir, 0755, true);
        }
        
        $csvFile = $extractDir . 'hotel_count_per_country.csv';
        $csvHandle = fopen($csvFile, 'w');
        if ($csvHandle) {
            foreach ($csvData as $row) {
                fputcsv($csvHandle, $row);
            }
            fclose($csvHandle);
            logMessage("Results saved to $csvFile", $logDir);
        } else {
            logMessage("Failed to create CSV file: $csvFile", $logDir);
        }
    }
    
    logMessage("Hotel count completed successfully", $logDir);
    
} catch (Exception $e) {
    $errorMsg = "Error: " . $e->getMessage();
    logMessage($errorMsg, $logDir);
    echo $errorMsg . "\n";
}