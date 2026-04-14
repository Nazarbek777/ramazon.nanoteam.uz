<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class LiveStreamController extends Controller
{
    public function index()
    {
        $stream = DB::table('live_streams')->first();

        // Check if process is still running
        if ($stream && $stream->pid) {
            $isRunning = $this->isProcessRunning($stream->pid);
            if (!$isRunning && $stream->is_active) {
                DB::table('live_streams')->where('id', $stream->id)->update([
                    'is_active' => false,
                    'pid' => null
                ]);
                $stream->is_active = false;
                $stream->pid = null;
            }
        }

        // Scan for local video files
        $videoPath = storage_path('app/public/videos');
        if (!File::exists($videoPath)) {
            File::makeDirectory($videoPath, 0755, true);
        }

        $localVideos = [];
        $files = File::files($videoPath);
        foreach ($files as $file) {
            $extension = strtolower($file->getExtension());
            if (in_array($extension, ['mp4', 'mkv', 'avi', 'mov'])) {
                $localVideos[] = [
                    'name' => $file->getFilename(),
                    'path' => $file->getRealPath(),
                    'size' => round($file->getSize() / (1024 * 1024), 2) . ' MB'
                ];
            }
        }

        return view('admin.live-stream.index', compact('stream', 'localVideos'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'video_url' => 'nullable|string',
            'video_file' => 'nullable|file|mimes:mp4,mkv,avi,mov|max:102400', // 100MB max for UI upload
            'youtube_cookies' => 'nullable|file|mimes:txt|max:1024', // 1MB max for cookies
            'stream_url' => 'required|string',
            'stream_key' => 'required|string',
        ]);

        $videoUrl = $request->video_url;

        // Handle file upload
        if ($request->hasFile('video_file')) {
            $file = $request->file('video_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(storage_path('app/public/videos'), $fileName);
            $videoUrl = storage_path('app/public/videos/' . $fileName);
        }

        // Handle cookies upload
        if ($request->hasFile('youtube_cookies')) {
            $file = $request->file('youtube_cookies');
            $path = storage_path('youtube_cookies.txt');
            $file->move(storage_path(), 'youtube_cookies.txt');
            @chmod($path, 0666);
        }

        $data = [
            'video_url' => $videoUrl,
            'stream_url' => $request->stream_url,
            'stream_key' => $request->stream_key,
            'updated_at' => now(),
        ];

        DB::table('live_streams')->update($data);

        return redirect()->back()->with('success', 'Stream sozlamalari saqlandi.');
    }

    public function toggle(Request $request)
    {
        $stream = DB::table('live_streams')->first();
        $isRunning = $stream->pid ? $this->isProcessRunning($stream->pid) : false;

        if ($stream->is_active || $isRunning) {
            // Stop stream
            if ($stream->pid) {
                // Kill the script and all its children (ffmpeg)
                shell_exec("pkill -TERM -P {$stream->pid}");
                shell_exec("kill -9 {$stream->pid}");
            }

            // Safety: kill any remaining ffmpeg or stream.sh processes just in case
            // shell_exec("pkill -f stream.sh");
            // shell_exec("pkill -f ffmpeg");

            DB::table('live_streams')->update([
                'is_active' => false,
                'pid' => null,
                'updated_at' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Stream to\'xtatildi.', 'active' => false]);
        } else {
            // Start stream
            $scriptPath = base_path('stream.sh');
            $videoSource = $stream->video_url;

            $command = "nohup {$scriptPath} \"{$videoSource}\" \"{$stream->stream_key}\" \"{$stream->stream_url}\" >> " . storage_path('logs/stream.log') . " 2>&1 & echo $!";
            $pid = trim(shell_exec($command));

            DB::table('live_streams')->update([
                'is_active' => true,
                'pid' => $pid,
                'updated_at' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Stream boshlandi.', 'active' => true, 'pid' => $pid]);
        }
    }

    public function getStats()
    {
        $stats = [
            'is_running' => false,
            'is_active_db' => false,
            'uptime' => '00:00:00',
            'bitrate' => '0 kbps',
            'speed' => '0x',
            'total_size' => '0 MB',
            'out_time' => '00:00:00'
        ];

        $stream = DB::table('live_streams')->first();
        if ($stream) {
            $stats['is_active_db'] = (bool) $stream->is_active;

            if ($stream->pid && $this->isProcessRunning($stream->pid)) {
                $stats['is_running'] = true;

                // Calculate uptime
                $startTime = trim(shell_exec("ps -p {$stream->pid} -o lstart= | xargs -0 echo"));
                if ($startTime) {
                    $start = new \DateTime($startTime);
                    $now = new \DateTime();
                    $diff = $start->diff($now);
                    $stats['uptime'] = $diff->format('%H:%I:%S');
                }

                // Parse progress log
                $logPath = storage_path('logs/stream_progress.log');
                if (file_exists($logPath)) {
                    $content = file_get_contents($logPath);
                    $lines = explode("\n", $content);

                    // Get the last set of progress data (FFmpeg writes multiple lines)
                    foreach (array_reverse($lines) as $line) {
                        if (str_contains($line, 'bitrate='))
                            $stats['bitrate'] = trim(str_replace('bitrate=', '', $line));
                        if (str_contains($line, 'speed='))
                            $stats['speed'] = trim(str_replace('speed=', '', $line));
                        if (str_contains($line, 'total_size=')) {
                            $size = (int) trim(str_replace('total_size=', '', $line));
                            $stats['total_size'] = round($size / (1024 * 1024), 2) . ' MB';
                        }
                        if (str_contains($line, 'out_time='))
                            $stats['out_time'] = substr(trim(str_replace('out_time=', '', $line)), 0, 8);

                        // Break after getting one full set (approx)
                        if (str_contains($line, 'progress=continue'))
                            break;
                    }
                }
            }
        }

        return response()->json($stats);
    }

    private function isProcessRunning($pid)
    {
        if (!$pid)
            return false;
        $output = shell_exec("ps -p $pid");
        return str_contains($output, (string) $pid);
    }
}
