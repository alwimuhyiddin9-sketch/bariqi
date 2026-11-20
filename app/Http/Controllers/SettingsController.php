<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\StoreSetting;
use App\Models\AppSetting;

class SettingsController extends Controller
{
    public function index()
    {
        $store = StoreSetting::find(1);
        $app = AppSetting::find(1);
        $admin = User::first();
        return view('settings.index', compact('store', 'app', 'admin'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'store.name' => 'nullable',
            'store.address' => 'nullable',
            'store.phone' => 'nullable',
            'store.email' => 'nullable',
            'store.logo' => 'nullable',
            'app.min_stock_limit' => 'nullable|integer|min:0',
            'app.trx_prefix' => 'nullable',
            'app.theme' => 'nullable|in:light,dark',
            'app.date_format' => 'nullable',
            'app.footer_note' => 'nullable',
            'admin.name' => 'nullable',
            'admin.username' => 'nullable',
            'admin.email' => 'nullable|email',
            'admin.phone' => 'nullable',
            'admin.password' => 'nullable|min:6',
        ]);

        if (!empty($data['store'])) {
            StoreSetting::updateOrCreate(['id' => 1], $data['store']);
        }
        if (!empty($data['app'])) {
            AppSetting::updateOrCreate(['id' => 1], $data['app']);
        }
        if (!empty($data['admin'])) {
            $admin = User::first();
            $adminData = $data['admin'];
            if (!empty($adminData['password'])) {
                $adminData['password'] = bcrypt($adminData['password']);
            } else {
                unset($adminData['password']);
            }
            $admin->update($adminData);
        }
        return redirect()->route('settings.index')->with('success', 'Pengaturan disimpan');
    }
}
