<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit(SettingsService $settings)
    {
        return view('admin.settings', ['settings' => $settings->all()]);
    }

    public function update(Request $request, SettingsService $settings)
    {
        $data = $request->validate([
            'store_name' => ['required', 'string', 'max:80'],
            'returns_days' => ['required', 'integer', 'between:0,365'],
            'return_shipping' => ['required', 'string', 'max:500'],
            'processing_days' => ['required', 'integer', 'between:0,30'],
            'shipping_regions' => ['required', 'string', 'max:500'],
            'support_hours' => ['required', 'string', 'max:120'],
        ]);
        $settings->setMany($data);

        return back()->with('toast', 'Store settings saved.');
    }
}
