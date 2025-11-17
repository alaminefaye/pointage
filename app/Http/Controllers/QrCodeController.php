<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Services\QrCodeService;
use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    protected $qrCodeService;

    public function __construct(QrCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Get current QR code for a specific site.
     */
    public function getCurrent(Request $request)
    {
        $siteId = $request->input('site_id');
        
        if (!$siteId) {
            // Get first active site if no site_id provided
            $site = Site::where('is_active', true)->first();
            if (!$site) {
                return response()->json([
                    'error' => 'Aucun site actif trouvé',
                ], 404);
            }
            $siteId = $site->id;
        }

        $qrCode = $this->qrCodeService->getCurrentQrCode($siteId);
        
        return response()->json([
            'site_id' => $siteId,
            'code' => $qrCode->code,
            'expires_at' => $qrCode->expires_at->toIso8601String(),
            'expires_in_seconds' => max(0, now()->diffInSeconds($qrCode->expires_at, false)),
        ]);
    }

    /**
     * Get current QR codes for all active sites.
     * Returns both dynamic QR codes and static QR codes.
     */
    public function getAllCurrent()
    {
        $sites = Site::where('is_active', true)->get();
        $result = [];

        foreach ($sites as $site) {
            // Use static QR code if available, otherwise use dynamic QR code
            if ($site->static_qr_code) {
                $result[] = [
                    'site_id' => $site->id,
                    'site_name' => $site->name,
                    'code' => $site->static_qr_code,
                    'is_static' => true,
                    'expires_at' => null,
                    'expires_in_seconds' => null,
                ];
            } else {
                // Fallback to dynamic QR code if no static code exists
                try {
                    $qrCode = $this->qrCodeService->getCurrentQrCode($site->id);
            $result[] = [
                        'site_id' => $site->id,
                'site_name' => $site->name,
                'code' => $qrCode->code,
                        'is_static' => false,
                'expires_at' => $qrCode->expires_at->toIso8601String(),
                'expires_in_seconds' => max(0, now()->diffInSeconds($qrCode->expires_at, false)),
            ];
                } catch (\Exception $e) {
                    // Skip sites without QR codes
                    continue;
                }
            }
        }

        return response()->json($result);
    }

    /**
     * Generate new QR code for a specific site (admin only).
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'site_id' => 'required|exists:sites,id',
        ]);

        $qrCode = $this->qrCodeService->generateQrCode($validated['site_id']);
        
        return response()->json([
            'success' => true,
            'site_id' => $validated['site_id'],
            'code' => $qrCode->code,
            'expires_at' => $qrCode->expires_at->toIso8601String(),
        ]);
    }

    /**
     * Generate QR codes for all active sites.
     */
    public function generateAll()
    {
        $this->qrCodeService->generateQrCodesForAllSites();
        
        return response()->json([
            'success' => true,
            'message' => 'QR codes générés pour tous les sites actifs',
        ]);
    }
}
