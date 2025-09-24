#!/bin/bash

# Exit immediately if a command exits with a non-zero status.
set -e

# --- Pridobivanje nastavitev ---
settings=$(php ./export_settings.php)

# Extract key_id value
KEY_ID=$(echo "$settings" | grep -oP '"key_id"\s*:\s*"\K[^"]+')

# Extract api_key value
API_KEY=$(echo "$settings" | grep -oP '"api_key"\s*:\s*"\K[^"]+')

echo "KEY_ID: $KEY_ID"
echo "API_KEY: $API_KEY"

# --- Definicije poti in URL-jev ---
URL="https://api.worldota.net/api/b2b/v3/hotel/info/incremental_dump/"
OUTPUT_FILE="incremental_dump.json"
JQ_PATH="/home/balkanea/public_html/CRUD_Data/extracts/jq"
ZSTD_PATH="/home/balkanea/public_html/CRUD_Data/zstd-1.5.7/zstd"
ZST_FILE="/home/balkanea/public_html/CRUD_Data/extracts/feed_en_v3.json.zst"
JSON_FILE="/home/balkanea/public_html/CRUD_Data/extracts/feed_en_v3.jsonl"

# --- Korak 1: Klic API-ja ---
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Zagon klica API-ja..."
API_RESPONSE=$(curl --user "${KEY_ID}:${API_KEY}" \
     --header "Content-Type: application/json" \
     --data '{"language": "en"}' \
     "${URL}")
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Klic API-ja končan."

# --- Korak 2: Pridobivanje URL-ja za prenos in datuma zadnje posodobitve ---
DOWNLOAD_URL=$(echo "$API_RESPONSE" | "$JQ_PATH" -r '.data.url')
LAST_UPDATE=$(echo "$API_RESPONSE" | "$JQ_PATH" -r '.data.last_update')

if [ -z "$DOWNLOAD_URL" ] || [ "$DOWNLOAD_URL" == "null" ]; then
    echo "Napaka: URL za prenos ni bil najden v odgovoru!" >&2
    exit 1
fi

if [ -z "$LAST_UPDATE" ] || [ "$LAST_UPDATE" == "null" ]; then
    echo "Napaka: 'last_update' ni bil najden v odgovoru!" >&2
    exit 1
fi

echo "URL za prenos: $DOWNLOAD_URL"
echo "Zadnja posodobitev: $LAST_UPDATE"

# --- Korak 3: Preverjanje, ali datoteka obstaja in primerjava datumov ---
if [ -f "$ZST_FILE" ]; then
    FILE_DATE=$(date -u -r "$ZST_FILE" +"%Y-%m-%dT%H:%M:%SZ")
    if [[ "$FILE_DATE" > "$LAST_UPDATE" ]] || [[ "$FILE_DATE" == "$LAST_UPDATE" ]]; then
        echo "Datoteka $ZST_FILE je posodobljena (ujema se z last_update: $LAST_UPDATE)."
    else
        echo "Na voljo je novejša datoteka. Začenja se prenos."
        # --- Korak 4: Prenos datoteke ---
        curl -o "$ZST_FILE" "$DOWNLOAD_URL"
    fi
else
    echo "Lokalna datoteka ne obstaja. Začenja se prenos."
    # --- Korak 4: Prenos datoteke ---
    curl -o "$ZST_FILE" "$DOWNLOAD_URL"
fi

# --- Korak 5: Dekompresija datoteke .zst ---
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Začetek dekompresije datoteke $ZST_FILE..."
"$ZSTD_PATH" -d -f "$ZST_FILE" -o "$JSON_FILE"
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Datoteka je bila uspešno dekomprimirana v: $JSON_FILE"

# --- Korak 6: Obdelava kod držav iz argumentov ukazne vrstice ---
if [ -z "$1" ]; then
    echo "Napaka: Prosimo, podajte vsaj eno kodo države kot argument." >&2
    echo "Primer (ena koda): $0 MK" >&2
    echo "Primer (več kod): $0 MK,PK,DK" >&2
    exit 1
fi

# Zamenjajte vejice z presledki, da ustvarite seznam za zanko for
# Primer: "MK,PK,DK" postane "MK PK DK"
COUNTRY_CODES_ARG=$(echo "$1" | tr ',' ' ')

# --- Korak 7: Filtriranje hotelov po podanih kodah držav ---
for code in $COUNTRY_CODES_ARG; do
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] Začetek ekstrakcije za kodo države: $code"

    output_file="/home/balkanea/public_html/CRUD_Data/extracts/extracted_${code}_region.jsonl"

    "$JQ_PATH" -c --arg cc "$code" 'select(.region.country_code == $cc)' "$JSON_FILE" > "$output_file"
    
    count=$(wc -l < "$output_file")

    echo "[$(date '+%Y-%m-%d %H:%M:%S')] Končana ekstrakcija za $code. Ekstrahiranih $count hotelov v $output_file"
done

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Skripta je končala z izvajanjem."