#!/bin/bash

# Clean any carriage returns from the script itself first
sed -i 's/\r$//' "$0" 2>/dev/null

# Step 0: Extract settings from PHP script
PHP_BIN="/usr/local/bin/ea-php82"
EXPORT_SETTINGS="/home/balkanea/public_html/wp-content/plugins/balkanea_ratehawk_crud/Includes/export_settings.php"
CRUD_DATA_DIR="/home/balkanea/public_html/CRUD_Data"

# Clean directory path (remove CR/LF and extra spaces)
CRUD_DATA_DIR=$(echo "$CRUD_DATA_DIR" | tr -d '\r\n' | xargs)

# Remove any old wrong folders like CRUD_Data$'\r'
rm -rf *$'\r'* 

# Make sure directories exist
mkdir -p "$CRUD_DATA_DIR"
mkdir -p "$CRUD_DATA_DIR/reviews"  # Create reviews subdirectory

settings=$($PHP_BIN $EXPORT_SETTINGS 2>/dev/null)
settings=$(echo "$settings" | tr -d '\r')

KEY_ID=$(echo "$settings" | tr -d '\n ' | grep -oP '"key_id":"\K[^"]+')
API_KEY=$(echo "$settings" | tr -d '\n ' | grep -oP '"api_key":"\K[^"]+')

# Force override for testing
KEY_ID="11492"
API_KEY="7b899b07-227e-463c-a58c-553c0cd7c37c"

echo "Starting incremental reviews download process for all languages..."
echo "Working directory: $CRUD_DATA_DIR"

# Define paths and URLs
REVIEWS_URL="https://api.worldota.net/api/b2b/v3/hotel/incremental_reviews/dump/"
JQ_PATH="$(echo "$CRUD_DATA_DIR/extracts/jq" | tr -d '\r')"
ZSTD_PATH="$(echo "$CRUD_DATA_DIR/zstd-1.5.7/zstd" | tr -d '\r')"

# List of languages to process
LANGUAGES=("ar" "bg" "cs" "da" "de" "el" "en" "es" "fi" "fr" "he" "hu" "it" "ja" "kk" "ko" "nl" "no" "pl" "pt" "pt_PT" "ro" "ru" "sq" "sr" "sv" "th" "tr" "uk" "vi" "zh_CN" "zh_TW")

# Function to process incremental reviews for a single language
process_language() {
    local LANGUAGE=$1
    
    echo "Processing language: $LANGUAGE"
    
    # Define language-specific file paths
    local REVIEWS_ZST_FILE="$(echo "$CRUD_DATA_DIR/reviews/reviews_master_${LANGUAGE}.json.zst" | tr -d '\r')"
    local REVIEWS_JSONL_FILE="$(echo "$CRUD_DATA_DIR/reviews/reviews_master_${LANGUAGE}.jsonl" | tr -d '\r')"
    local REVIEWS_JSON_FILE="$(echo "$CRUD_DATA_DIR/reviews/reviews_master_${LANGUAGE}.json" | tr -d '\r')"
    
    # Step 1: Make the API call for reviews dump with specific language
    echo "Making API call to get master reviews dump for $LANGUAGE..."
    API_RESPONSE=$(curl --user "${KEY_ID}:${API_KEY}" \
         --header "Content-Type: application/json" \
         --data "{\"language\": \"${LANGUAGE}\"}" \
         "${REVIEWS_URL}")

    if [ $? -ne 0 ]; then
        echo "Error: curl command failed for language $LANGUAGE!" >&2
        return 1
    fi

    API_RESPONSE=$(echo "$API_RESPONSE" | tr -d '\r')

    # Step 2: Extract download URL and last_update
    DOWNLOAD_URL=$(echo "$API_RESPONSE" | "$JQ_PATH" -r '.data.url')
    LAST_UPDATE=$(echo "$API_RESPONSE" | "$JQ_PATH" -r '.data.last_update')

    if [ -z "$DOWNLOAD_URL" ] || [ "$DOWNLOAD_URL" = "null" ]; then
        echo "Error: Download URL not found in response for language $LANGUAGE!" >&2
        echo "API Response: $API_RESPONSE" >&2
        return 1
    fi

    echo "Download URL for $LANGUAGE: $DOWNLOAD_URL"
    echo "Last Update for $LANGUAGE: $LAST_UPDATE"

    # Step 3: Check if we need to download
    DOWNLOAD_NEEDED=1
    if [ -f "$REVIEWS_ZST_FILE" ]; then
        FILE_DATE=$(date -u -r "$REVIEWS_ZST_FILE" +"%Y-%m-%dT%H:%M:%SZ")
        if [ "$FILE_DATE" = "$LAST_UPDATE" ] || [ "$FILE_DATE" \> "$LAST_UPDATE" ]; then
            echo "File $REVIEWS_ZST_FILE is up to date. Skipping download for $LANGUAGE."
            DOWNLOAD_NEEDED=0
        else
            echo "File is outdated for $LANGUAGE. Current: $FILE_DATE, API: $LAST_UPDATE"
        fi
    fi

    # Step 4: Download if needed
    if [ $DOWNLOAD_NEEDED -eq 1 ]; then
        echo "Downloading master reviews file for $LANGUAGE..."
        curl -o "$REVIEWS_ZST_FILE" "$DOWNLOAD_URL"
        if [ $? -ne 0 ]; then
            echo "Error: Failed to download from $DOWNLOAD_URL for language $LANGUAGE" >&2
            return 1
        fi
        echo "Master reviews file downloaded for $LANGUAGE: $REVIEWS_ZST_FILE"
        
        # Update timestamp
        LAST_UPDATE_TIMESTAMP=$(date -d "$LAST_UPDATE" +"%s" 2>/dev/null)
        if [ -n "$LAST_UPDATE_TIMESTAMP" ]; then
            touch -d "@$LAST_UPDATE_TIMESTAMP" "$REVIEWS_ZST_FILE"
        fi
    fi

    # Step 5: Decompress .zst to JSONL
    echo "Decompressing to JSONL format for $LANGUAGE..."
    if [ -f "$REVIEWS_JSONL_FILE" ]; then
        rm -f "$REVIEWS_JSONL_FILE"
    fi

    "$ZSTD_PATH" -d "$REVIEWS_ZST_FILE" -o "$REVIEWS_JSONL_FILE"
    if [ $? -ne 0 ]; then
        echo "Error: Failed to decompress $REVIEWS_ZST_FILE for language $LANGUAGE" >&2
        return 1
    fi

    # Step 6: Convert JSONL to JSON format
    echo "Converting JSONL to JSON format for $LANGUAGE..."
    if [ -f "$REVIEWS_JSON_FILE" ]; then
        rm -f "$REVIEWS_JSON_FILE"
    fi

    # Check if JSONL file is empty
    if [ ! -s "$REVIEWS_JSONL_FILE" ]; then
        echo '[]' > "$REVIEWS_JSON_FILE"
        echo "Empty JSONL file, created empty JSON array"
    else
        # Convert using jq if available (most reliable)
        if command -v jq >/dev/null 2>&1; then
            jq -s '.' "$REVIEWS_JSONL_FILE" > "$REVIEWS_JSON_FILE"
        else
            # IMPROVED Fallback method - handles trailing commas properly
            echo '[' > "$REVIEWS_JSON_FILE"
            
            # Count total lines
            total_lines=$(wc -l < "$REVIEWS_JSONL_FILE")
            current_line=1
            
            # Process each line
            while IFS= read -r line; do
                # Remove any trailing whitespace and commas
                clean_line=$(echo "$line" | sed 's/,[[:space:]]*$//' | sed 's/^[[:space:]]*//' | sed 's/[[:space:]]*$//')
                
                # Skip empty lines
                if [ -z "$clean_line" ]; then
                    continue
                fi
                
                # Add comma only if it's not the last line
                if [ "$current_line" -lt "$total_lines" ]; then
                    echo "$clean_line," >> "$REVIEWS_JSON_FILE"
                else
                    echo "$clean_line" >> "$REVIEWS_JSON_FILE"
                fi
                
                current_line=$((current_line + 1))
            done < "$REVIEWS_JSONL_FILE"
            
            echo ']' >> "$REVIEWS_JSON_FILE"
        fi
    fi

    # Step 7: Clean and validate the JSON file
    echo "Cleaning and validating JSON file for $LANGUAGE..."
    if [ -f "$REVIEWS_JSON_FILE" ]; then
        # Remove any double commas and trailing commas before closing braces/brackets
        sed -i 's/,,/,/g' "$REVIEWS_JSON_FILE"  # Remove double commas
        sed -i 's/,\([[:space:]]*\)}/\1}/g' "$REVIEWS_JSON_FILE"  # Remove trailing commas before }
        sed -i 's/,\([[:space:]]*\)]/\1]/g' "$REVIEWS_JSON_FILE"  # Remove trailing commas before ]
        
        # Validate JSON syntax if jq is available
        if command -v jq >/dev/null 2>&1; then
            if jq empty "$REVIEWS_JSON_FILE" 2>/dev/null; then
                echo "JSON file is valid after cleaning"
            else
                echo "WARNING: JSON file may still be invalid after cleaning"
                # Try to fix with jq if possible
                if jq '.' "$REVIEWS_JSON_FILE" > "${REVIEWS_JSON_FILE}.tmp" 2>/dev/null; then
                    mv "${REVIEWS_JSON_FILE}.tmp" "$REVIEWS_JSON_FILE"
                    echo "JSON file repaired using jq"
                fi
            fi
        fi
        
        FILE_SIZE=$(du -h "$REVIEWS_JSON_FILE" | cut -f1)
        echo "JSON file created successfully for $LANGUAGE!"
        echo "Location: $REVIEWS_JSON_FILE"
        echo "Size: $FILE_SIZE"
        
                # Delete JSONL and ZST files after successful JSON creation
        if [ -f "$REVIEWS_JSON_FILE" ]; then
            if [ -f "$REVIEWS_JSONL_FILE" ]; then
                rm -f "$REVIEWS_JSONL_FILE"
                echo "Deleted JSONL file: $REVIEWS_JSONL_FILE"
            fi
            if [ -f "$REVIEWS_ZST_FILE" ]; then
                rm -f "$REVIEWS_ZST_FILE"
                echo "Deleted ZST file: $REVIEWS_ZST_FILE"
            fi
        fi
                
        
        
    else
        echo "ERROR: JSON file was not created"
        return 1
    fi
    
    # Clean up JSONL file (optional)
    # rm -f "$REVIEWS_JSONL_FILE"
    
    return 0
}

# Process all languages
for LANGUAGE in "${LANGUAGES[@]}"; do
    process_language "$LANGUAGE"
    
    if [ $? -ne 0 ]; then
        echo "Warning: Failed to process language $LANGUAGE, continuing with next language..."
    fi
    
    echo "----------------------------------------"
done

echo "Process completed! JSON files generated for all languages in CRUD_Data/reviews folder."
