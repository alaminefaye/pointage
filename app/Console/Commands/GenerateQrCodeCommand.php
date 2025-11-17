<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\QrCodeService;

class GenerateQrCodeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qr:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new QR code for attendance';

    protected $qrCodeService;

    public function __construct(QrCodeService $qrCodeService)
    {
        parent::__construct();
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Generate QR codes for all active sites
        $this->qrCodeService->generateQrCodesForAllSites();
        
        $this->info("QR Codes generated for all active sites");
        
        return Command::SUCCESS;
    }
}
