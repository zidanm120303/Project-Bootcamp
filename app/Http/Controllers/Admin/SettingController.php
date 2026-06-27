<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;

class SettingController extends Controller
{
    public function index()
    {
        return view('admin.settings', ['settings' => SystemSetting::orderBy('key')->get()]);
    }

    public function update()
    {
        $data = request()->validate([
            'platform_fee_percent' => ['required', 'numeric', 'between:0,30'],
            'trusted_min_score' => ['required', 'integer', 'between:0,100'],
            'payment_due_hours' => ['required', 'integer', 'between:1,168'],
        ]);
        foreach ($data as $key => $value) {
            SystemSetting::updateOrCreate(['key' => $key], ['value' => (string) $value, 'type' => 'number']);
        }

        return back()->with('success', 'Pengaturan sistem berhasil disimpan.');
    }
}
