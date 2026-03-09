#!/bin/bash

# ==========================================
# Telegram Video Loop Stream Script (v2.0)
# ==========================================

# Arguments
VIDEO_SOURCE="$1"
STREAM_KEY="$2"
STREAM_URL="${3:-rtmps://dc4-1.rtmp.t.me/s/}"

# Default source if none provided
if [ -z "$VIDEO_SOURCE" ]; then
    VIDEO_SOURCE="public/video/live.mp4"
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
        DIRECT_URL=$(yt-dlp -g -f "best[height<=720]" "$VIDEO_SOURCE" 2>/dev/null)
        if [ $? -ne 0 ] || [ -z "$DIRECT_URL" ]; then
            echo "Xato: Yutub URLni olib bo'lmadi. 10 soniyadan keyin qayta urunish..."
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
    ffmpeg -re -i "$INPUT_URL" -progress "/home/nazarbek/server/ramazon.nanoteam.uz/storage/logs/stream_progress.log" \
        -c:v libx264 -preset veryfast -b:v 2000k -maxrate 2000k -bufsize 4000k \
        -pix_fmt yuv420p -g 50 -c:a aac -b:a 128k -ar 44100 \
        -f flv "$STREAM_URL$STREAM_KEY" &
    
    FFMPEG_PID=$!
    wait $FFMPEG_PID

    echo "Stream tugadi yoki to'xtadi. Qayta boshlanmoqda..."
    sleep 2
done
