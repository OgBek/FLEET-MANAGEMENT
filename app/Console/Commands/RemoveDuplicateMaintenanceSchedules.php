<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\MaintenanceSchedule;

class RemoveDuplicateMaintenanceSchedules extends Command
{
    protected $signature = 'maintenance:remove-duplicates';
    protected $description = 'Remove duplicate maintenance schedule records';

    public function handle()
    {
        $this->info('Starting to remove duplicate maintenance schedules...');

        // Find duplicates based on vehicle_id, maintenance_type, scheduled_date, and status
        $duplicates = DB::table('maintenance_schedules')
            ->select('vehicle_id', 'maintenance_type', 'scheduled_date', 'status', DB::raw('COUNT(*) as count'), DB::raw('MIN(id) as keep_id'))
            ->groupBy('vehicle_id', 'maintenance_type', 'scheduled_date', 'status')
            ->having('count', '>', 1)
            ->get();

        $deletedCount = 0;

        foreach ($duplicates as $duplicate) {
            // Delete all duplicates except the one with the lowest ID
            $deleted = MaintenanceSchedule::where('vehicle_id', $duplicate->vehicle_id)
                ->where('maintenance_type', $duplicate->maintenance_type)
                ->where('scheduled_date', $duplicate->scheduled_date)
                ->where('status', $duplicate->status)
                ->where('id', '>', $duplicate->keep_id)
                ->delete();

            $deletedCount += $deleted;
        }

        $this->info("Successfully removed {$deletedCount} duplicate maintenance schedule(s).");
    }
}
