<?php

use App\Console\Commands\CleanupUnusedProfileImages;
use App\Console\Commands\UpdateRoomStatuses;
use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;

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





// Schedule::call(function () {
//     // Define the storage directory for profile images
//     $storagePath = storage_path('app/public/users/profiles');

//     // Retrieve all profile image file paths in the storage directory
//     $files = File::allFiles($storagePath);

//     // Get the list of profile image filenames that are in use in the database
//     $usedImages = DB::table('users')->pluck('profile_img')->toArray();

//     print_r($usedImages); // Output used images for debugging

//     // Ensure filenames in `usedImages` match exactly with storage filenames
//     foreach ($files as $file) {
//         $filename = $file->getFilename();

//         // Check if this filename exists in the used images array
//         if (!in_array($filename, $usedImages)) {
//             // Delete the file from the 'public' disk
//             Storage::disk('public')->delete('users/profiles/' . $filename);
//             echo "Deleted unused profile image: " . $filename . PHP_EOL;
//         } else {
//             echo "File in use, not deleted: " . $filename . PHP_EOL;
//         }
//     }
// })->everyMinute(); 