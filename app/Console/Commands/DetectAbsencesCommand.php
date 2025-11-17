<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AttendanceCalculationService;
use Carbon\Carbon;

class DetectAbsencesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:detect-absences {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detect absences for all employees for a specific date (default: yesterday)';

    protected $calculationService;

    public function __construct(AttendanceCalculationService $calculationService)
    {
        parent::__construct();
        $this->calculationService = $calculationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dateInput = $this->argument('date');
        
        if ($dateInput) {
            $date = Carbon::parse($dateInput);
        } else {
            $date = Carbon::yesterday();
        }

        $this->info("Detecting absences for date: {$date->format('Y-m-d')}");
        
        $this->calculationService->detectAbsences($date);
        
        $this->info("Absence detection completed.");
        
        return Command::SUCCESS;
    }
}
