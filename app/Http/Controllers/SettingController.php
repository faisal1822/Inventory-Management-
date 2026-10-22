<?php

namespace App\Http\Controllers;

use App\Http\Requests\SettingRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = Setting::getAllSettings();

        return view('settings.index', compact('settings'));
    }

    public function update(SettingRequest $request): RedirectResponse
    {
        $fields = [
            'business_name',
            'business_address',
            'phone',
            'email',
            'currency_symbol',
            'tax_percentage',
        ];

        foreach ($fields as $field) {
            Setting::set($field, $request->input($field));
        }

        if ($request->hasFile('logo')) {
            $oldLogo = Setting::get('logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            $logoPath = $request->file('logo')->store('logos', 'public');
            Setting::set('logo', $logoPath);
        } elseif ($request->boolean('remove_logo')) {
            $oldLogo = Setting::get('logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            Setting::set('logo', null);
        }

        return back()->with('success', 'Application settings updated successfully.');
    }
}
