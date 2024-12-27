<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use QCod\AppSettings\SavesSettings;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    use SavesSettings;

    /**
     * Clear the Laravel log file.
     *
     * @return \Illuminate\Http\Response
     */
    public function clearLog()
    {
        $logFile = storage_path('logs/laravel.log');

        if (file_exists($logFile)) {
            file_put_contents($logFile, '');
            Log::info('Log file cleared successfully.');
            return back()->with('success', 'Log file cleared successfully.');
        } else {
            return back()->withErrors(['error' => 'Log file does not exist.']);
        }
    }
}
