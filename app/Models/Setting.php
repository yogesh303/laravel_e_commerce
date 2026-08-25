<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/Setting.php
class Setting extends Model
{
    protected $fillable = ['key', 'value', 'is_encrypted'];

    public static function get(string $key, $default = null)
    {
        return cache()->rememberForever("setting.$key", function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            if (!$setting) return $default;

            return $setting->is_encrypted
                ? decrypt($setting->value)
                : $setting->value;
        });
    }

    public static function set(string $key, $value, bool $encrypted = false)
    {
        self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $encrypted ? encrypt($value) : $value,
                'is_encrypted' => $encrypted,
            ]
        );
        cache()->forget("setting.$key");
    }
}