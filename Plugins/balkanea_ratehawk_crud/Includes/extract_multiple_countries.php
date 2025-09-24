<?php
$inputFile =  __DIR__ .'/../../../../CRUD_Data/partner_feed_en_v3.jsonl';
$outputDir = __DIR__ . '/../../../../CRUD_Data/extracts';

// ✅ Get country codes from CLI arguments
if ($argc < 2) {
    die("❌ Please provide at least one country code as an argument (e.g. MK or MK,PK,GR)\n");
}

// Parse country codes and normalize to uppercase
$argCountries = explode(',', $argv[1]);
$allowedCountries = array_map('strtoupper', array_map('trim', $argCountries));

if (!file_exists($outputDir)) {
    mkdir($outputDir, 0777, true);
}

$handles = []; // File handles for each country code
$fh = fopen($inputFile, 'r');

if (!$fh) {
    die("❌ Could not open $inputFile\n");
}

while (($line = fgets($fh)) !== false) {
    $json = json_decode($line, true);

    if (!isset($json['region']['country_code'])) {
        continue; // Skip if no country_code
    }

    $code = strtoupper($json['region']['country_code']);

    // ✅ Skip if not in allowed list
    if (!in_array($code, $allowedCountries)) {
        continue;
    }

    // Create file handle if not already opened
    if (!isset($handles[$code])) {
        $filePath = "$outputDir/extracted_{$code}_region.jsonl";
        $handles[$code] = fopen($filePath, 'w');
    }

    fwrite($handles[$code], trim($line) . "\n");
}

fclose($fh);

// Close all output files
foreach ($handles as $handle) {
    fclose($handle);
}

echo "✅ Done. Extracted files for: " . implode(', ', array_keys($handles)) . "\n";
?>
