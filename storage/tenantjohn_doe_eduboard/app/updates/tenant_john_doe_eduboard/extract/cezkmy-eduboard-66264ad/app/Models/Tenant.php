<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    protected $guarded = [];

    public function getIncrementing()
    {
        return false;
    }

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'owner_id',
            'school_name',
            'school_short_name',
            'logo',
            'status',
            'plan',
            'system_version',
            'previous_version',
            'latest_backup_path',
            'latest_db_backup_path',
            'storage_limit_gb',
            'storage_used_gb',
            'bandwidth_limit_gb',
            'bandwidth_used_gb',
            'expires_at',
            'custom_disabled_message',
            'created_at',
            'updated_at',
        ];
    }

    public function billingHistories()
    {
        return $this->hasMany(BillingHistory::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function getActivePlan(): string
    {
        $plan = $this->plan ?? 'Basic';
        if ($plan !== 'Basic' && $this->expires_at && \Carbon\Carbon::parse($this->expires_at)->isPast()) {
            return 'Basic';
        }
        return $plan;
    }

    public function hasFeature($feature): bool
    {
        $features = [
            'Basic' => ['image_upload', 'pin_announcements', 'timeline_view', 'light_dark_mode'],
            'Pro' => ['image_upload', 'video_upload', 'pin_announcements', 'timeline_view', 'categories', 'theme_customization', 'custom_logo', 'light_dark_mode', 'reports'],
            'Ultimate' => ['image_upload', 'video_upload', 'pin_announcements', 'timeline_view', 'categories', 'theme_customization', 'pre_built_templates', 'custom_logo', 'light_dark_mode', 'reports'],
        ];

        $plan = $this->getActivePlan();
        return in_array($feature, $features[$plan] ?? []);
    }

    public function getLimit($type): int
    {
        $limits = [
            'Basic' => ['admins' => 1, 'teachers' => 5, 'templates' => 0],
            'Pro' => ['admins' => 5, 'teachers' => 15, 'templates' => 5],
            'Ultimate' => ['admins' => 10, 'teachers' => -1, 'templates' => -1], // -1 for unlimited
        ];
        
        $plan = $this->getActivePlan();
        return $limits[$plan][$type] ?? 0;
    }

    public function updateStorageUsage(): float
    {
        $totalBytes = 0;
        
        try {
            // Scan all tenant-uploaded files in their local public disk (announcements, branding, etc)
            $disk = \Illuminate\Support\Facades\Storage::disk('public');
            
            // This safely retrieves all files spanning across any newly created subdirectories 
            $files = $disk->allFiles();
            foreach ($files as $file) {
                $totalBytes += $disk->size($file);
            }
        } catch (\Throwable $e) {
            return (float) ($this->storage_used_gb ?? 0);
        }
        
        // Convert bytes to GB
        $gb = $totalBytes > 0 ? round($totalBytes / 1073741824, 4) : 0;
        
        // IMPORTANT: Use central DB connection explicitly — inside tenant context
        // DB::table() defaults to the tenant DB, so we must force 'mysql' (central)
        \Illuminate\Support\Facades\DB::connection('mysql')
            ->table('tenants')
            ->where('id', $this->id)
            ->update(['storage_used_gb' => $gb]);

        $this->storage_used_gb = $gb;

        return $gb;
    }

    public function isStorageFull(): bool
    {
        $limit = (float) ($this->storage_limit_gb ?? 5.0);
        
        // Use the cached storage_used_gb instead of re-scanning the entire disk every time
        // The usage is updated incrementally during uploads
        $used = (float) ($this->storage_used_gb ?? 0);
        
        return $used >= $limit;
    }

    /**
     * Increment storage usage without scanning the entire disk.
     * This is much faster for performance.
     */
    public function incrementStorageUsage(float $bytes): void
    {
        $gb = round($bytes / 1073741824, 6);
        
        \Illuminate\Support\Facades\DB::connection('mysql')
            ->table('tenants')
            ->where('id', $this->id)
            ->increment('storage_used_gb', $gb);
            
        $this->storage_used_gb += $gb;
    }
}
