#!/bin/bash

# Step 0: Extract settings from PHP script
PHP_BIN="/usr/local/bin/ea-php82"
EXPORT_SETTINGS="/home/balkanea/public_html/wp-content/plugins/balkanea_ratehawk_crud/Includes/export_settings.php"
DATA_DIR="/home/balkanea/public_html/CRUD_Data/extracts"

settings=$($PHP_BIN $EXPORT_SETTINGS 2>/dev/null)

KEY_ID=$(echo "$settings" | tr -d '\r\n ' | grep -oP '"key_id":"\K[^"]+')
API_KEY=$(echo "$settings" | tr -d '\r\n ' | grep -oP '"api_key":"\K[^"]+')

echo "KEY_ID: $KEY_ID"
echo "API_KEY: $API_KEY"

# Define paths and URLs
REVIEWS_URL="https://api.worldota.net/api/b2b/v3/hotel/incremental_reviews/dump/"
JQ_PATH="/home/balkanea/public_html/CRUD_Data/extracts/jq"
ZSTD_PATH="/home/balkanea/public_html/CRUD_Data/zstd-1.5.7/zstd"
REVIEWS_ZST_FILE="/home/balkanea/public_html/CRUD_Data/extracts/reviews_feed_en_v3.json.zst"
REVIEWS_JSON_FILE="/home/balkanea/public_html/CRUD_Data/extracts/reviews_feed_en_v3.jsonl"

# Step 1: Make the API call for reviews
echo "Making API call to get reviews dump..."
API_RESPONSE=$(curl --user "${KEY_ID}:${API_KEY}" \
     --header "Content-Type: application/json" \
     --data '{"language": "en"}' \
     "${REVIEWS_URL}")

if [ $? -ne 0 ]; then
    echo "Error: curl command failed!" >&2
    exit 1
fi

# Step 2: Extract download URL and last_update for reviews
DOWNLOAD_URL=$(echo "$API_RESPONSE" | "$JQ_PATH" -r '.data.url')
LAST_UPDATE=$(echo "$API_RESPONSE" | "$JQ_PATH" -r '.data.last_update')

if [ -z "$DOWNLOAD_URL" ] || [ "$DOWNLOAD_URL" == "null" ]; then
    echo "Error: Download URL not found in response!" >&2
    echo "API Response: $API_RESPONSE" >&2
    exit 1
fi

if [ -z "$LAST_UPDATE" ] || [ "$LAST_UPDATE" == "null" ]; then
    echo "Error: last_update not found in response!" >&2
    exit 1
fi

echo "Download URL: $DOWNLOAD_URL"
echo "Last Update: $LAST_UPDATE"

# Step 3: Check if reviews file exists and compare dates
DOWNLOAD_NEEDED=1
if [ -f "$REVIEWS_ZST_FILE" ]; then
    FILE_DATE=$(date -u -r "$REVIEWS_ZST_FILE" +"%Y-%m-%dT%H:%M:%SZ")
    if [ "$FILE_DATE" = "$LAST_UPDATE" ] || [ "$FILE_DATE" \> "$LAST_UPDATE" ]; then
        echo "File $REVIEWS_ZST_FILE is up to date (matches last_update: $LAST_UPDATE). Skipping download."
        DOWNLOAD_NEEDED=0
    else
        echo "File is outdated. Current file date: $FILE_DATE, API last update: $LAST_UPDATE"
    fi
fi

# Step 4: Download the reviews file if needed
if [ $DOWNLOAD_NEEDED -eq 1 ]; then
    echo "Downloading reviews file..."
    curl -o "$REVIEWS_ZST_FILE" "$DOWNLOAD_URL"
    if [ $? -ne 0 ]; then
        echo "Error: Failed to download the reviews file from $DOWNLOAD_URL" >&2
        exit 1
    fi
    echo "Reviews file downloaded successfully: $REVIEWS_ZST_FILE"
    
    # Update the file timestamp to match the last_update
    LAST_UPDATE_TIMESTAMP=$(date -d "$LAST_UPDATE" +"%s")
    touch -d "@$LAST_UPDATE_TIMESTAMP" "$REVIEWS_ZST_FILE"
fi

# Step 5: Decompress the .zst file
if [ -f "$REVIEWS_JSON_FILE" ]; then
    rm -f "$REVIEWS_JSON_FILE"
fi

echo "Decompressing reviews file..."
"$ZSTD_PATH" -d "$REVIEWS_ZST_FILE" -o "$REVIEWS_JSON_FILE"
if [ $? -ne 0 ]; then
    echo "Error: Failed to decompress $REVIEWS_ZST_FILE" >&2
    exit 1
fi

echo "Reviews file decompressed successfully: $REVIEWS_JSON_FILE"

# Step 6: Get hotel IDs from database
echo "Getting hotel IDs from database..."
DB_HOST="localhost"
DB_USER="balkanea_wp"
DB_PASS="your_password"  # Replace with your actual DB password
DB_NAME="balkanea_wp"

HOTEL_IDS_FILE="/home/balkanea/public_html/CRUD_Data/extracts/hotel_ids.txt"

# Query to get all hotel IDs from your database
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS -D $DB_NAME -e "
SELECT hotel_id FROM ratehawk_hotels;
" --skip-column-names > "$HOTEL_IDS_FILE"

if [ $? -ne 0 ]; then
    echo "Error: Failed to get hotel IDs from database!" >&2
    exit 1
fi

HOTEL_COUNT=$(wc -l < "$HOTEL_IDS_FILE")
echo "Found $HOTEL_COUNT hotel IDs in database"

# Step 7: Filter reviews for hotels that exist in database
echo "Filtering reviews for hotels in database..."

FILTERED_REVIEWS_FILE="/home/balkanea/public_html/CRUD_Data/extracts/filtered_reviews.jsonl"

# Create a pattern file for jq
echo "Creating filter pattern..."
PATTERN_FILE=$(mktemp)
while read -r hotel_id; do
    if [ -n "$hotel_id" ]; then
        echo "\"$hotel_id\"" >> "$PATTERN_FILE"
    fi
done < "$HOTEL_IDS_FILE"

# Filter reviews using jq
echo "Filtering reviews..."
"$JQ_PATH" -c --slurpfile hotels "$PATTERN_FILE" '
    . as $review |
    $hotels[] as $hotel_ids |
    select(.hotel_id as $hid | $hotel_ids | index($hid) != null)
' "$REVIEWS_JSON_FILE" > "$FILTERED_REVIEWS_FILE"

# Clean up
rm -f "$PATTERN_FILE"

REVIEW_COUNT=$(wc -l < "$FILTERED_REVIEWS_FILE")
echo "Filtered $REVIEW_COUNT reviews for hotels in database"

# Step 8: Split reviews by hotel ID for easier processing
echo "Splitting reviews by hotel ID..."

REVIEWS_BY_HOTEL_DIR="/home/balkanea/public_html/CRUD_Data/extracts/reviews_by_hotel"
mkdir -p "$REVIEWS_BY_HOTEL_DIR"

# Initialize counter
counter=0

# Process each review
while IFS= read -r review; do
    if [ -n "$review" ]; then
        hotel_id=$(echo "$review" | "$JQ_PATH" -r '.hotel_id')
        if [ -n "$hotel_id" ] && [ "$hotel_id" != "null" ]; then
            echo "$review" >> "$REVIEWS_BY_HOTEL_DIR/${hotel_id}.jsonl"
            ((counter++))
            
            # Print progress every 1000 reviews
            if (( counter % 1000 == 0 )); then
                echo "Processed $counter reviews..."
            fi
        fi
    fi
done < "$FILTERED_REVIEWS_FILE"

echo "Total reviews processed: $counter"
echo "Reviews have been split by hotel ID in directory: $REVIEWS_BY_HOTEL_DIR"

# Step 9: Create a summary file
SUMMARY_FILE="/home/balkanea/public_html/CRUD_Data/extracts/reviews_summary.json"
echo "Creating summary file..."

# Count reviews per hotel
find "$REVIEWS_BY_HOTEL_DIR" -name "*.jsonl" -exec sh -c '
    echo "{ \"hotel_id\": \"$(basename {} .jsonl)\", \"review_count\": $(wc -l < {}) }"
' \; | "$JQ_PATH" -s '.' > "$SUMMARY_FILE"

echo "Summary file created: $SUMMARY_FILE"

echo "Reviews download and processing completed successfully!"