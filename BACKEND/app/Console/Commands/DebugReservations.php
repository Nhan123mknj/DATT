<?php

namespace App\Console\Commands;

use App\Models\DeviceReservation;
use Illuminate\Console\Command;

class DebugReservations extends Command
{
    protected $signature = 'debug:reservations';
    protected $description = 'Debug reservation auto-create system';

    public function handle()
    {
        $this->info('🔍 Debugging Reservation Auto-Create System');
        $this->newLine();

        // 1. Check approved reservations
        $this->info('1️⃣ Checking approved reservations...');
        $approvedReservations = DeviceReservation::where('status', 'approved')->get();
        
        $this->table(
            ['ID', 'User', 'Reserved From', 'Status', 'Is Future?', 'Completed At'],
            $approvedReservations->map(fn($r) => [
                $r->id,
                $r->user->name ?? 'N/A',
                $r->reserved_from,
                $r->status,
                $r->reserved_from->isFuture() ? 'Yes' : 'No',
                $r->completed_at ?? 'NULL'
            ])
        );

        // 2. Check past due reservations
        $this->newLine();
        $this->info('2️⃣ Checking PAST DUE reservations...');
        $pastDue = DeviceReservation::where('status', 'approved')
            ->where('reserved_from', '<=', now())
            ->get();
        
        if ($pastDue->isEmpty()) {
            $this->warn('⚠️ No past due reservations found!');
        } else {
            $this->info("✅ Found {$pastDue->count()} past due reservations:");
            foreach ($pastDue as $res) {
                $this->line("  - Reservation #{$res->id}: {$res->reserved_from} (should have created borrow)");
            }
        }

        // 3. Check if borrows were created
        $this->newLine();
        $this->info('3️⃣ Checking if borrows were created from reservations...');
        foreach ($pastDue as $res) {
            $borrow = \App\Models\Borrows::where('notes', 'like', "%đặt trước #{$res->id}%")->first();
            if ($borrow) {
                $this->line("  ✅ Reservation #{$res->id} → Borrow #{$borrow->id} created");
            } else {
                $this->error("  ❌ Reservation #{$res->id} → NO BORROW CREATED!");
            }
        }

        // 4. Check queue jobs
        $this->newLine();
        $this->info('4️⃣ Checking queue jobs...');
        try {
            $jobs = \DB::table('jobs')->where('queue', 'reservations')->get();
            $this->info("Jobs in 'reservations' queue: {$jobs->count()}");
            
            $failedJobs = \DB::table('failed_jobs')->get();
            $this->info("Failed jobs: {$failedJobs->count()}");
            
            if ($failedJobs->count() > 0) {
                $this->warn('⚠️ There are failed jobs! Check them:');
                foreach ($failedJobs as $job) {
                    $this->line("  - ID: {$job->id}, Failed at: {$job->failed_at}");
                }
            }
        } catch (\Exception $e) {
            $this->error('Could not check jobs table: ' . $e->getMessage());
        }

        // 5. Recommendations
        $this->newLine();
        $this->info('📋 Recommendations:');
        $this->line('  1. Make sure scheduler is running: php artisan schedule:work');
        $this->line('  2. Make sure queue worker is running: php artisan queue:work --queue=reservations');
        $this->line('  3. Check logs: tail -f storage/logs/laravel.log');
        $this->line('  4. Manually process: php artisan app:process-due-reservations');
        
        return Command::SUCCESS;
    }
}
