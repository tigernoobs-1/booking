<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Booking;
use Illuminate\Support\Facades\Log;
use App\Traits\EmailTraits;

class ReminderEmailCommand extends Command
{
    use EmailTraits;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:sendEmail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminder';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //Log::info(__METHOD__ . ' update status');
        $time = Carbon::now()->addHour()->startOfHour();
        Log::info(__METHOD__ . $time);
        $bookings = Booking::where('start_time', '=', $time)->with('users:id,name,email', 'items:name,id')->get();
        Log::info(__METHOD__ . $bookings);

        foreach ($bookings as $booking) {
            //Log::info(__METHOD__ . $booking->users->email);
            $this->emailReminder($booking);
        }
        $this->info('Email Send successfully.');
    }
}
