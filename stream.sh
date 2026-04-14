#!/bin/bash

# ==========================================
# Telegram Video Loop Stream Script (v2.0)
# ==========================================

# Determine Project Root
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$SCRIPT_DIR"

# Binaries - Try to find them in PATH first, fallback to common locations
FFMPEG=$(which ffmpeg || echo "/usr/bin/ffmpeg")
YT_DLP=$(which yt-dlp || which /usr/local/bin/yt-dlp || which /usr/bin/yt-dlp || echo "$HOME/.local/bin/yt-dlp")
NODE="/usr/local/bin/node-stream"
[ ! -f "$NODE" ] && NODE=$(which node || echo "node")

# Arguments
VIDEO_SOURCE="$1"
STREAM_KEY="$2"
STREAM_URL="${3:-rtmps://dc4-1.rtmp.t.me/s/}"

# Default source if none provided
if [ -z "$VIDEO_SOURCE" ]; then
    VIDEO_SOURCE="$PROJECT_ROOT/public/video/live.mp4"
fi

# Function to check if it's a YouTube link
is_youtube() {
    if [[ $1 == *"youtube.com"* ]] || [[ $1 == *"youtu.be"* ]]; then
        return 0
    else
        return 1
    fi
}

# Function to cleanup child processes on exit
cleanup() {
    echo "Stopping stream..."
    if [ ! -z "$FFMPEG_PID" ]; then
        kill $FFMPEG_PID 2>/dev/null
    fi
    exit
}

trap cleanup EXIT SIGINT SIGTERM

# Main Loop
while true
do
    echo "Streaming boshlanmoqda: $VIDEO_SOURCE"

    if is_youtube "$VIDEO_SOURCE"; then
        # YouTube direct URL fetching
        echo "--------------------------------------------------------" >> "$PROJECT_ROOT/storage/logs/stream.log"
        echo "$(date): YouTube URL aniqlanmoqda: $VIDEO_SOURCE" >> "$PROJECT_ROOT/storage/logs/stream.log"
        
        # Clear previous debug log
        echo "$(date): Yangi YouTube qidiruvi boshlandi: $VIDEO_SOURCE" > "$PROJECT_ROOT/storage/logs/youtube_debug.log"
        
        # Optional cookies
        COOKIES_ARG=""
        if [ -f "$PROJECT_ROOT/storage/youtube_cookies.txt" ]; then
            COOKIES_ARG="--cookies $PROJECT_ROOT/storage/youtube_cookies.txt"
        elif [ -f "$PROJECT_ROOT/youtube_cookies.txt" ]; then
            COOKIES_ARG="--cookies $PROJECT_ROOT/youtube_cookies.txt"
        fi

        # Aggressive bypass and client selection
        echo "yt-dlp buyrug'i bajarilmoqda..." >> "$PROJECT_ROOT/storage/logs/youtube_debug.log"
        
        DIRECT_URL=$($YT_DLP -g $COOKIES_ARG --no-cache-dir --no-check-certificate --prefer-free-formats \
            --user-agent "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36" \
            --extractor-args "youtube:player-client=tv,web,mweb" \
            -f "best[height<=720]" "$VIDEO_SOURCE" 2>> "$PROJECT_ROOT/storage/logs/youtube_debug.log")

        if [ $? -ne 0 ] || [ -z "$DIRECT_URL" ]; then
            echo "Xato: YouTube linkidan video manzilini olib bo'lmadi." >> "$PROJECT_ROOT/storage/logs/stream.log"
            echo "Debugging uchun storage/logs/youtube_debug.log ni ko'ring." >> "$PROJECT_ROOT/storage/logs/stream.log"
            sleep 10
            continue
        fi
        
        INPUT_URL="$DIRECT_URL"
        echo "$(date): YouTube URL muvaffaqiyatli olindi: ${INPUT_URL:0:50}..." >> "$PROJECT_ROOT/storage/logs/stream.log"
    elif [[ "$VIDEO_SOURCE" == http* ]]; then
        # Direct URL
        INPUT_URL="$VIDEO_SOURCE"
        echo "$(date): To'g'ridan-to'g'ri URL ishlatilmoqda: $VIDEO_SOURCE" >> "$PROJECT_ROOT/storage/logs/stream.log"
    else
        # Local file
        if [ ! -f "$VIDEO_SOURCE" ]; then
            echo "$(date): Xato: Fayl topilmadi: $VIDEO_SOURCE" >> "$PROJECT_ROOT/storage/logs/stream.log"
            sleep 10
            continue
        fi
        INPUT_URL="$VIDEO_SOURCE"
        echo "$(date): Mahalliy fayl ishlatilmoqda: $VIDEO_SOURCE" >> "$PROJECT_ROOT/storage/logs/stream.log"
    fi

    # FFmpeg Stream
    echo "$(date): FFmpeg boshlanmoqda..." >> "$PROJECT_ROOT/storage/logs/stream.log"
    $FFMPEG -re -i "$INPUT_URL" -progress "$PROJECT_ROOT/storage/logs/stream_progress.log" \
        -c:v libx264 -preset veryfast -b:v 2500k -maxrate 2500k -bufsize 5000k \
        -pix_fmt yuv420p -g 50 -c:a aac -b:a 128k -ar 44100 \
        -f flv "$STREAM_URL$STREAM_KEY" >> "$PROJECT_ROOT/storage/logs/stream.log" 2>&1 &
    
    FFMPEG_PID=$!
    wait $FFMPEG_PID
    
    EXIT_CODE=$?
    echo "$(date): FFmpeg jarayoni to'xtadi (Exit Code: $EXIT_CODE). 5 soniyadan keyin qayta urunib ko'radi..." >> "$PROJECT_ROOT/storage/logs/stream.log"
    sleep 5
done
