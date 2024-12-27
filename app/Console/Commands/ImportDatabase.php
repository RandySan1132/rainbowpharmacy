<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ImportDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:import {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a SQL file into the database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File not found: $filePath");
            return 1;
        }

        $sqlContent = file_get_contents($filePath);

        try {
            DB::unprepared($sqlContent);
            $this->info('Database imported successfully.');
            Log::info('Database imported successfully.');
            return 0;
        } catch (Exception $e) {
            $this->error('Error importing database: ' . $e->getMessage());
            Log::error('Error importing database: ' . $e->getMessage());
            Log::error('SQL file content: ' . $sqlContent);
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
    }
}
