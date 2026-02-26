<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class LogViewerController extends Controller
{
    public function index()
    {
        $logPath = storage_path('logs/laravel.log');
        
        if (!File::exists($logPath)) {
            return "Log fayli topilmadi.";
        }

        $content = File::get($logPath);
        
        // Oxirgi 100 qatorni olish (oddiyroq usul)
        $lines = explode("\n", $content);
        $lastLines = array_slice($lines, -200);
        $output = implode("\n", $lastLines);

        return response($output, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function clear()
    {
        $logPath = storage_path('logs/laravel.log');
        File::put($logPath, "");
        return "Logs cleared.";
    }
}
