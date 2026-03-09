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
        echo "yt-dlp orqali URL olinmoqda..." >> "$PROJECT_ROOT/storage/logs/stream.log"
        # Aggressive bypass: iOS/Android clients + Mobile User-Agent + JS Runtime
        DIRECT_URL=$($YT_DLP -g --no-check-certificate --prefer-free-formats \
            --user-agent "Mozilla/5.0 (iPhone; CPU iPhone OS 16_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.5 Mobile/15E148 Safari/604.1" \
            --extractor-args "youtube:player-client=ios,android,web_creator,web_embedded,web,mweb" \
            -f "best[height<=720]" "$VIDEO_SOURCE" 2>> "$PROJECT_ROOT/storage/logs/stream.log")
        if [ $? -ne 0 ] || [ -z "$DIRECT_URL" ]; then
            echo "Xato: Yutub URLni olib bo'lmadi. 10 soniyadan keyin qayta urunish..."
            echo "$(date): yt-dlp xatoga uchradi yoki URL bo'sh qoldi." >> "$PROJECT_ROOT/storage/logs/stream.log"
            sleep 10
            continue
        fi
        INPUT_URL="$DIRECT_URL"
    else
        # Local file
        if [ ! -f "$VIDEO_SOURCE" ]; then
            echo "Xato: Fayl topilmadi: $VIDEO_SOURCE. 10 soniyadan keyin qayta urunish..."
            sleep 10
            continue
        fi
        INPUT_URL="$VIDEO_SOURCE"
    fi

    # FFmpeg Stream
    $FFMPEG -re -i "$INPUT_URL" -progress "$PROJECT_ROOT/storage/logs/stream_progress.log" \
        -c:v libx264 -preset veryfast -b:v 2000k -maxrate 2000k -bufsize 4000k \
        -pix_fmt yuv420p -g 50 -c:a aac -b:a 128k -ar 44100 \
        -f flv "$STREAM_URL$STREAM_KEY" >> "$PROJECT_ROOT/storage/logs/stream.log" 2>&1 &
    
    FFMPEG_PID=$!
    wait $FFMPEG_PID

    echo "Stream tugadi yoki to'xtadi. Qayta boshlanmoqda..."
    sleep 2
done
