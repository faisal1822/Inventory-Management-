<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    public static function get(string $key, $default = null): ?string
    {
        return Cache::rememberForever("app_setting_{$key}", function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("app_setting_{$key}");
    }

    public static function getAllSettings(): array
    {
        $defaults = [
            'business_name' => 'StockMaster Pro',
            'business_address' => 'Dhaka, Bangladesh',
            'phone' => '+880 1700 000000',
            'email' => 'info@stockmaster.com',
            'currency_symbol' => '৳',
            'logo' => null,
            'tax_percentage' => '0',
            'invoice_prefix' => 'INV-',
            'purchase_prefix' => 'PO-',
        ];

        $settings = static::pluck('value', 'key')->toArray();

        return array_merge($defaults, $settings);
    }
}
