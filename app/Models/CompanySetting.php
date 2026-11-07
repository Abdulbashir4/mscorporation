<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $guarded = ['id'];

    public static function profile(): array
    {
        $profile = array_merge([
            'name' => 'MS Corporation', 'business' => 'Medical Equipment Importer & Supplier',
            'phone' => '', 'email' => '', 'address' => '', 'license' => '', 'vat' => '',
            'tagline' => 'Better Equipment. Better Healthcare.', 'logo' => null,
        ], static::find(1)?->toArray() ?? []);
        if ($profile['logo'] && str_starts_with($profile['logo'], 'company-logos/')) {
            $profile['logo'] = route('settings.company.logo');
        }

        return $profile;
    }
}
