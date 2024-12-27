<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Log;
use Artisan;
use Storage;
use Response;
use Exception;
use League\Flysystem\Adapter\Local;
use Illuminate\Support\Facades\DB; // Add this import
use GuzzleHttp\Client; // Add this import

class BackupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = 'backups';
        $this->data['backups'] = [];
        $disk = Storage::disk('local');
        $files = $disk->allFiles('backups');

        // make an array of backup files, with their filesize and creation date
        foreach ($files as $k => $f) {
            // only take the sql files into account
            if (substr($f, -4) == '.sql' && $disk->exists($f)) {
                $this->data['backups'][] = [
                    'file_path'     => $f,
                    'file_name'     => str_replace('backups/', '', $f),
                    'file_size'     => $disk->size($f),
                    'last_modified' => $disk->lastModified($f),
                    'disk'          => 'local',
                    'download'      => true,
                ];
            }
        }
        return view('admin.backup', $this->data, compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        try {
            $databaseName = env('DB_DATABASE');
            $username = env('DB_USERNAME');
            $password = env('DB_PASSWORD');
            $host = env('DB_HOST');
            $port = env('DB_PORT');

            $backupFile = storage_path("app/backups/backup-{$databaseName}-" . date('Y-m-d_H-i-s') . ".sql");

            $command = "mysqldump --user={$username} --host={$host} --port={$port} {$databaseName} > \"{$backupFile}\"";
            if (!empty($password)) {
                $command = "mysqldump --user={$username} --password={$password} --host={$host} --port={$port} {$databaseName} > \"{$backupFile}\"";
            }

            Log::info("Running command: $command");

            $result = null;
            $output = [];
            exec($command . ' 2>&1', $output, $result);

            Log::info("Command output: " . implode("\n", $output));
            Log::info("Command result code: $result");

            if ($result !== 0) {
                throw new Exception('Error creating backup: ' . implode("\n", $output));
            }

            return back()->with('success', 'Database backup created successfully.');
        } catch (Exception $e) {
            Log::error('Exception: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withErrors(['error' => 'Error creating backup: ' . $e->getMessage()]);
        }
    }

    /**
     * Downloads a backup zip file.
     */
    public function download()
    {
        $disk = Storage::disk(Request::input('disk'));
        $file_name = Request::input('file_name');
        $adapter = $disk->getDriver()->getAdapter();

        if ($adapter instanceof Local) {
            $storage_path = $disk->getDriver()->getAdapter()->getPathPrefix();

            if ($disk->exists($file_name)) {
                return response()->download($storage_path.$file_name);
            } else {
                abort(404, trans('backup.backup_doesnt_exist'));
            }
        } else {
            abort(404, trans('backup.only_local_downloads_supported'));
        }
    }

    /**
     * Export the database SQL.
     *
     * @return \Illuminate\Http\Response
     */
    public function export()
    {
        try {
            $databaseName = env('DB_DATABASE');
            $username = env('DB_USERNAME');
            $password = env('DB_PASSWORD');
            $host = env('DB_HOST');
            $port = env('DB_PORT');

            $backupFile = storage_path("app/backup-{$databaseName}-" . date('Y-m-d_H-i-s') . ".sql");

            $command = "mysqldump --user={$username} --host={$host} --port={$port} {$databaseName} > {$backupFile}";
            if (!empty($password)) {
                $command = "mysqldump --user={$username} --password={$password} --host={$host} --port={$port} {$databaseName} > {$backupFile}";
            }

            Log::info("Running command: $command");

            $result = null;
            $output = [];
            exec($command . ' 2>&1', $output, $result);

            Log::info("Command output: " . implode("\n", $output));

            if ($result !== 0) {
                throw new Exception('Error exporting database: ' . implode("\n", $output));
            }

            return response()->download($backupFile)->deleteFileAfterSend(true);
        } catch (Exception $e) {
            Log::error($e);
            return back()->withErrors(['error' => 'Error exporting database: ' . $e->getMessage()]);
        }
    }

    /**
     * Manually export the database SQL.
     *
     * @return \Illuminate\Http\Response
     */
    public function manualExport()
    {
        try {
            $databaseName = env('DB_DATABASE');
            $username = env('DB_USERNAME');
            $password = env('DB_PASSWORD');
            $host = env('DB_HOST');
            $port = env('DB_PORT');

            $backupFile = storage_path("app/backup-{$databaseName}-" . date('Y-m-d_H-i-s') . ".sql");

            $command = "mysqldump --user={$username} --host={$host} --port={$port} {$databaseName} > \"{$backupFile}\"";
            if (!empty($password)) {
                $command = "mysqldump --user={$username} --password={$password} --host={$host} --port={$port} {$databaseName} > \"{$backupFile}\"";
            }

            Log::info("Running command: $command");

            $result = null;
            $output = [];
            exec($command . ' 2>&1', $output, $result);

            Log::info("Command output: " . implode("\n", $output));
            Log::info("Command result code: $result");

            if ($result !== 0) {
                throw new Exception('Error exporting database: ' . implode("\n", $output));
            }

            // Send the file to Telegram
            $this->sendToTelegram($backupFile);

            return back()->with('success', 'Database backup exported and sent to Telegram successfully.');
        } catch (Exception $e) {
            Log::error('Exception: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withErrors(['error' => 'Error exporting database: ' . $e->getMessage()]);
        }
    }

    /**
     * Send the exported SQL file to Telegram.
     *
     * @param string $filePath
     * @return void
     */
    protected function sendToTelegram($filePath)
    {
        $botToken = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID');
        $client = new Client();

        try {
            $response = $client->post("https://api.telegram.org/bot{$botToken}/sendDocument", [
                'multipart' => [
                    [
                        'name'     => 'chat_id',
                        'contents' => $chatId
                    ],
                    [
                        'name'     => 'document',
                        'contents' => fopen($filePath, 'r')
                    ]
                ]
            ]);

            $responseBody = $response->getBody()->getContents();
            Log::info('Telegram API response: ' . $responseBody);

            $responseData = json_decode($responseBody, true);
            if ($responseData['ok']) {
                Log::info('Database backup sent to Telegram successfully.');
            } else {
                Log::error('Failed to send database backup to Telegram: ' . $responseData['description']);
            }
        } catch (Exception $e) {
            Log::error('Failed to send database backup to Telegram: ' . $e->getMessage());
        }
    }

    /**
     * Import the database SQL.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        $request->validate([
            'sql_file' => 'required|file|mimetypes:text/plain,text/x-sql,application/sql',
        ]);

        $sqlFile = $request->file('sql_file');
        $sqlContent = file_get_contents($sqlFile);

        Log::info('SQL file MIME type: ' . $sqlFile->getMimeType());
        Log::info('SQL file original name: ' . $sqlFile->getClientOriginalName());

        try {
            DB::unprepared($sqlContent);
            Log::info('Database import successful.');
            return back()->with('success', 'Database imported successfully.');
        } catch (Exception $e) {
            Log::error('Error importing database: ' . $e->getMessage());
            Log::error('SQL file content: ' . $sqlContent);
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withErrors(['error' => 'Error importing database: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  file $file_name
     * @return \Illuminate\Http\Response
     */
    public function destroy($file_name)
    {
        $disk = Storage::disk(Request::input('disk'));

        if ($disk->exists($file_name)) {
            $disk->delete($file_name);
            $notification = notify('backup deleted successfully');
            return back()->with($notification);
        } else {
            abort(404, trans('backup.backup_doesnt_exist'));
        }
    }
}
