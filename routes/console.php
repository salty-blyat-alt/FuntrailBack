<?php

use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    echo "Updating room status\n";

    $rooms = Room::all();
    
    foreach ($rooms as $room) {
        $currentBooking = Booking::where('room_id', $room->id)  
            ->where('date_end', '<', Carbon::now())
            ->first();
        
        if ($currentBooking) {
            $room->status = 'free';
            echo "Room {$room->id} set to free\n";
        } else {
            $room->status = 'busy';
            echo "Room {$room->id} set to busy\n";
        }
        
        $room->save();
    }
})->everySecond();

Schedule::call(function () {
    echo 'hello';
})->everySecond();
