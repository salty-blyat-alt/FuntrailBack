<?php

use App\Console\Commands\UpdateRoomStatuses;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schedule;
 
Schedule::call([UpdateRoomStatuses::class, 'handle'])->daily();
