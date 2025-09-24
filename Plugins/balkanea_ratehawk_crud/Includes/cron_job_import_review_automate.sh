#!/bin/bash

# Clean any carriage returns from the script itself first
sed -i 's/\r$//' "$0" 2>/dev/null

# Define paths
CRUD_DATA_DIR="/home/balkanea/public_html/CRUD_Data/reviews"
SCRIPTS_DIR="/home/balkanea/public_html/wp-content/plugins/balkanea_ratehawk_crud/Includes"
PHP_BIN="/usr/local/bin/ea-php82"

# List of languages to process
LANGUAGES=("ar" "bg" "cs" "da" "de" "el" "en" "es" "fi" "fr" "he" "hu" "it" "ja" "kk" "ko" "nl" "no" "pl" "pt" "pt_PT" "ro" "ru" "sq" "sr" "sv" "th" "tr" "uk" "vi" "zh_CN" "zh_TW")

# Change to CRUD data directory
cd "$CRUD_DATA_DIR" || exit 1

echo "$(date): Starting review processing workflow for all languages..."

# Function to convert JSONL to JSON
convert_jsonl_to_json() {
    local LANGUAGE=$1
    local TYPE=$2
    
    local JSONL_FILE
    local JSON_FILE
    
    if [ "$TYPE" = "incremental" ]; then
        JSONL_FILE="review_incremental_${LANGUAGE}.jsonl"
        JSON_FILE="review_incremental_${LANGUAGE}.json"
    else
        JSONL_FILE="reviews_master_${LANGUAGE}.jsonl"
        JSON_FILE="reviews_master_${LANGUAGE}.json"
    fi
    
    if [ -f "$JSONL_FILE" ]; then
        echo "$(date): Converting $JSONL_FILE to $JSON_FILE for $LANGUAGE..."
        echo '[' > "$JSON_FILE"
        sed 's/$/,/' "$JSONL_FILE" | sed '$ s/,$//' >> "$JSON_FILE"
        echo ']' >> "$JSON_FILE"
        echo "$(date): Conversion completed for $LANGUAGE"
    else
        echo "$(date): WARNING: JSONL file not found for conversion: $JSONL_FILE"
    fi
}

# Step 1: Run incremental download script
echo "$(date): Executing incremental download for all languages..."
if [ -f "$SCRIPTS_DIR/download_incremental_dump.sh" ]; then
    bash "$SCRIPTS_DIR/download_incremental_dump.sh"
    INCREMENTAL_STATUS=$?
    
    if [ $INCREMENTAL_STATUS -eq 0 ]; then
        echo "$(date): Incremental download completed successfully."
        
        # Process incremental files for each language
        for LANGUAGE in "${LANGUAGES[@]}"; do
            INCREMENTAL_JSONL="review_incremental_${LANGUAGE}.jsonl"
            INCREMENTAL_JSON="review_incremental_${LANGUAGE}.json"
            
            # Convert JSONL to JSON
            convert_jsonl_to_json "$LANGUAGE" "incremental"
            
            if [ -f "$INCREMENTAL_JSON" ]; then
                echo "$(date): Processing incremental file for $LANGUAGE: $INCREMENTAL_JSON"
                $PHP_BIN "$SCRIPTS_DIR/cron_job_import_reviews1.php" "$INCREMENTAL_JSON"
                echo "$(date): Incremental processing completed for $LANGUAGE."
            else
                echo "$(date): WARNING: Incremental JSON file not found for $LANGUAGE: $INCREMENTAL_JSON"
                echo "$(date): Checking if ZST file exists instead..."
                INCREMENTAL_ZST="$CRUD_DATA_DIR/review_incremental_${LANGUAGE}.json.zst"
                if [ -f "$INCREMENTAL_ZST" ]; then
                    echo "$(date): Found ZST file for $LANGUAGE, but JSON is missing. Decompression may have failed."
                fi
            fi
        done
    else
        echo "$(date): ERROR: Incremental download failed with status: $INCREMENTAL_STATUS"
    fi
else
    echo "$(date): ERROR: Incremental script not found: $SCRIPTS_DIR/download_incremental_dump.sh"
    exit 1
fi

# Step 2: Run master download script
echo "$(date): Executing master download for all languages..."
if [ -f "$SCRIPTS_DIR/download_master_review.sh" ]; then
    bash "$SCRIPTS_DIR/download_master_review.sh"
    MASTER_STATUS=$?
    
    if [ $MASTER_STATUS -eq 0 ]; then
        echo "$(date): Master download completed successfully."
        
        # Process master files for each language
        for LANGUAGE in "${LANGUAGES[@]}"; do
            MASTER_JSONL="reviews_master_${LANGUAGE}.jsonl"
            MASTER_JSON="reviews_master_${LANGUAGE}.json"
            
            # Convert JSONL to JSON
            convert_jsonl_to_json "$LANGUAGE" "master"
            
            if [ -f "$MASTER_JSON" ]; then
                echo "$(date): Processing master file for $LANGUAGE: $MASTER_JSON"
                $PHP_BIN "$SCRIPTS_DIR/cron_job_import_reviews1.php" "$MASTER_JSON"
                echo "$(date): Master processing completed for $LANGUAGE."
            else
                echo "$(date): WARNING: Master JSON file not found for $LANGUAGE: $MASTER_JSON"
                echo "$(date): Checking if ZST file exists instead..."
                MASTER_ZST="$CRUD_DATA_DIR/reviews_master_${LANGUAGE}.json.zst"
                if [ -f "$MASTER_ZST" ]; then
                    echo "$(date): Found ZST file for $LANGUAGE, but JSON is missing. Decompression may have failed."
                fi
            fi
        done
    else
        echo "$(date): ERROR: Master download failed with status: $MASTER_STATUS"
    fi
else
    echo "$(date): ERROR: Master script not found: $SCRIPTS_DIR/download_master_review.sh"
    exit 1
fi

echo "$(date): Review processing workflow completed for all languages."