<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateRoomStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-room-statuses';

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

        $this->info('Updating room statuses...');

        $rooms = Room::all();

        foreach ($rooms as $room) {
            $currentBooking = Booking::where('room_id', $room->id)
                ->where('date_start', '<=', Carbon::now())
                ->where('date_end', '>', Carbon::now())
                ->first();

            if ($currentBooking) {
                $room->status = 'busy';
            } else {
                $room->status = 'free';
            }

            $room->save();
        }

        $this->info('Room statuses updated successfully.');
    }
}
