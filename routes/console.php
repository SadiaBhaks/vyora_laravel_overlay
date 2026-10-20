<?php

use Illuminate\Support\Facades\Schedule;

// Return reserved stock for orders that were never paid.
Schedule::command('vyora:release-stale-orders')->everyTenMinutes()->withoutOverlapping();
