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
            'school_name',
            'status',
            'plan',
            'expires_at',
            'custom_disabled_message',
            'created_at',
            'updated_at',
        ];
    }

    public function hasFeature($feature): bool
    {
        $features = [
            'Basic' => ['image_upload', 'pin_announcements', 'timeline_view', 'custom_logo', 'light_dark_mode'],
            'Pro' => ['image_upload', 'video_upload', 'pin_announcements', 'timeline_view', 'categories', 'theme_customization', 'pre_built_templates', 'custom_logo', 'light_dark_mode', 'reports'],
            'Ultimate' => ['image_upload', 'video_upload', 'pin_announcements', 'timeline_view', 'categories', 'theme_customization', 'pre_built_templates', 'custom_logo', 'light_dark_mode', 'reports'],
        ];

        $plan = $this->plan ?? 'Basic';
        return in_array($feature, $features[$plan] ?? []);
    }

    public function getLimit($type): int
    {
        $limits = [
            'Basic' => ['admins' => 1, 'teachers' => 5, 'templates' => 0],
            'Pro' => ['admins' => 5, 'teachers' => 15, 'templates' => 5],
            'Ultimate' => ['admins' => 10, 'teachers' => -1, 'templates' => -1], // -1 for unlimited
        ];
        
        $plan = $this->plan ?? 'Basic';
        return $limits[$plan][$type] ?? 0;
    }
}
