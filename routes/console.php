<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Schedule;

Schedule::command('qr:generate')->everyThirtySeconds();
Schedule::command('attendance:detect-absences')->dailyAt('23:59');
