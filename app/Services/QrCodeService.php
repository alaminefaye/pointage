<?php

namespace App\Services;

use App\Models\QrCode;
use App\Models\Site;
use Illuminate\Support\Str;
use Carbon\Carbon;
use stdClass;

class QrCodeService
{
    /**
     * Generate a new QR code for a specific site that expires in 30 seconds.
     */
    public function generateQrCode(int $siteId): QrCode
    {
        // Invalidate all existing unused QR codes for this site
        QrCode::where('site_id', $siteId)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->update(['is_used' => true]);

        // Generate new QR code
        $code = Str::random(32);
        $expiresAt = now()->addSeconds(30);

        return QrCode::create([
            'site_id' => $siteId,
            'code' => $code,
            'expires_at' => $expiresAt,
            'is_used' => false,
        ]);
    }

    /**
     * Generate QR codes for all active sites.
     */
    public function generateQrCodesForAllSites(): void
    {
        $sites = Site::where('is_active', true)->get();
        
        foreach ($sites as $site) {
            $this->generateQrCode($site->id);
        }
    }

    /**
     * Get current valid QR code for a specific site or generate new one.
     */
    public function getCurrentQrCode(int $siteId): QrCode
    {
        $qrCode = QrCode::where('site_id', $siteId)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$qrCode) {
            $qrCode = $this->generateQrCode($siteId);
        }

        return $qrCode;
    }

    /**
     * Get current QR codes for all active sites.
     */
    public function getCurrentQrCodesForAllSites(): array
    {
        $sites = Site::where('is_active', true)->get();
        $qrCodes = [];

        foreach ($sites as $site) {
            $qrCodes[$site->id] = $this->getCurrentQrCode($site->id);
        }

        return $qrCodes;
    }

    /**
     * Validate and use QR code.
     * Supports both dynamic QR codes (from QrCode model) and static QR codes (from Site model).
     */
    public function validateAndUseQrCode(string $code, int $employeeId)
    {
        // First, try to find a dynamic QR code
        $qrCode = QrCode::where('code', $code)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        if ($qrCode) {
            $qrCode->update([
                'is_used' => true,
                'used_by_employee_id' => $employeeId,
                'used_at' => now(),
            ]);

            return $qrCode;
        }

        // If not found, check if it's a static QR code from a site
        $site = Site::where('static_qr_code', $code)
            ->where('is_active', true)
            ->first();

        if ($site) {
            // Create a pseudo-QR code object for static codes
            $staticQrCode = new stdClass();
            $staticQrCode->site_id = $site->id;
            $staticQrCode->code = $code;
            $staticQrCode->is_static = true;
            
            return $staticQrCode;
        }

        return null;
    }

    /**
     * Clean up expired QR codes (should be run periodically).
     */
    public function cleanupExpiredQrCodes(): void
    {
        QrCode::where('expires_at', '<', now()->subDays(7))->delete();
    }
}
