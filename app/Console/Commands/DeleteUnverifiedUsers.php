<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;
class DeleteUnverifiedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-unverified-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (Carbon::today()->day === 1) {
            $dateThreeDaysAgo = Carbon::now()->subDays(3);
            User::where('is_verified', false)
                ->where('created_at', '<', $dateThreeDaysAgo)
                ->delete();
            
            $this->info('تم حذف المستخدمين غير المتحققين بنجاح.');
        }
    }
}
