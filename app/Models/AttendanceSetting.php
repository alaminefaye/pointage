<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceSetting extends Model
{
    protected $fillable = [
        'site_id',
        'setting_key',
        'setting_value',
    ];

    /**
     * Get the site that owns the setting.
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get setting value by key for a specific site.
     */
    public static function getValue(?int $siteId, string $key, $default = null)
    {
        $query = self::where('setting_key', $key);
        if ($siteId) {
            $query->where('site_id', $siteId);
        } else {
            $query->whereNull('site_id');
        }
        $setting = $query->first();
        return $setting ? $setting->setting_value : $default;
    }

    /**
     * Set setting value by key for a specific site.
     */
    public static function setValue(?int $siteId, string $key, string $value): void
    {
        self::updateOrCreate(
            ['site_id' => $siteId, 'setting_key' => $key],
            ['setting_value' => $value]
        );
    }
}
