<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CentralSetting extends Model
{
    protected $connection = 'mysql';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = config('tenancy.database.central_connection', 'mysql');
    }

    protected $fillable = ['key', 'value'];

    public static function get($key, $default = null)
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function set($key, $value)
    {
        return static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function getJson(string $key, $default = null)
    {
        $raw = static::get($key);
        if ($raw === null) return $default;

        $decoded = json_decode($raw, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $default;
    }

    public static function setJson(string $key, $value)
    {
        return static::set($key, json_encode($value));
    }
}
