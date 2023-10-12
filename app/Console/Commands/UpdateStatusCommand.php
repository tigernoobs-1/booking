<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status in the database based on current datetime and end time';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info(__METHOD__ . ' update status');
        $bookings = Booking::where('end_time', '<=', Carbon::now())->get();

        foreach ($bookings as $booking) {
            $booking->status = 'Completed';
            $booking->save();
        }
       
        $this->info('Status updated successfully.');
    }
}
