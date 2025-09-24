#!/bin/bash

# -------------------------------
# Config
# -------------------------------
URL="https://api.worldota.net/api/b2b/v3/hotel/reviews/dump/"
OUTPUT_FILE="/home/balkanea/public_html/CRUD_Data/incremental_reviews_dump.json"
JQ_PATH="/home/balkanea/public_html/CRUD_Data/extracts/jq"


settings=$(php ./export_settings.php)

# Extract key_id value
KEY_ID=$(echo "$settings" | grep -oP '"key_id"\s*:\s*"\K[^"]+')

# Extract api_key value
API_KEY=$(echo "$settings" | grep -oP '"api_key"\s*:\s*"\K[^"]+')

echo "KEY_ID: $KEY_ID"
echo "API_KEY: $API_KEY"

# -------------------------------
# Step 1: Make the API call
# -------------------------------
API_RESPONSE=$(curl --silent --fail --user "${KEY_ID}:${API_KEY}" \
    --header "Content-Type: application/json" \
    --data '{"language": "en"}' \
    "${URL}")

if [ $? -ne 0 ]; then
    echo "Error: curl command failed!" >&2
    exit 1
fi

# -------------------------------
# Step 2: Extract download URL and last_update
# -------------------------------
DOWNLOAD_URL=$("$JQ_PATH" -r '.data.url' <<< "$API_RESPONSE")
LAST_UPDATE=$("$JQ_PATH" -r '.data.last_update' <<< "$API_RESPONSE")

if [ -z "$DOWNLOAD_URL" ] || [ "$DOWNLOAD_URL" == "null" ]; then
    echo "Error: Download URL not found in API response!" >&2
    exit 1
fi

echo "Downloading JSON from: $DOWNLOAD_URL"
echo "Last update: $LAST_UPDATE"

# -------------------------------
# Step 3: Download the JSON file
# -------------------------------
curl --silent --fail -o "$OUTPUT_FILE" "$DOWNLOAD_URL"

if [ $? -ne 0 ]; then
    echo "Error: Failed to download JSON file!" >&2
    exit 1
fi

echo "Download complete! Saved to: $OUTPUT_FILE"