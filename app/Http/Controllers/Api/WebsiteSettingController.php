<?php

namespace App\Http\Controllers\Api;

use App\Models\Setting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WebsiteSettingController extends Controller
{
    private function setting()
    {
        return Setting::firstOrFail();
    }

    /* =========================
       GET SETTINGS
    ========================= */
    public function show()
    {
        $setting = $this->setting();

        return response()->json([
            'setting' => array_merge($setting->toArray(), [
                'company_logo_url' => \App\Helpers\Setting::resolve_company_logo_url($setting->company_logo),
                'website_favicon_url' => \App\Helpers\Setting::resolve_favicon_url($setting->website_favicon),
            ]),
        ]);
    }

    /* =========================
       WEBSITE TAB
    ========================= */
    public function updateWebsite(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:150',
            'website_name' => 'required|string|max:150',
            'copyright' => 'required|string|max:150',
            'company_logo' => 'nullable|string|max:2000',
            'website_favicon' => 'nullable|string|max:2000',
            'google_analytics' => 'nullable|string',
            'google_map' => 'nullable|string',
            'google_recaptcha_sitekey' => 'nullable|string',
        ]);

        $setting = $this->setting();

        $data = $request->only([
            'company_name',
            'website_name',
            'copyright',
            'google_analytics',
            'google_map',
            'google_recaptcha_sitekey',
        ]);

        if ($request->has('company_logo')) {
            $data['company_logo'] = trim((string) $request->input('company_logo'));
        }

        if ($request->has('website_favicon')) {
            $data['website_favicon'] = trim((string) $request->input('website_favicon'));
        }

        if ($request->hasFile('company_logo')) {
            if ($setting->company_logo && ! $this->isManagedFilePath($setting->company_logo)) {
                Storage::disk('public')->delete($setting->company_logo);
            }
            $data['company_logo'] = $request->file('company_logo')->store('logos', 'public');
        }

        if ($request->hasFile('website_favicon')) {
            if ($setting->website_favicon && ! $this->isManagedFilePath($setting->website_favicon)) {
                Storage::disk('public')->delete($setting->website_favicon);
            }
            $data['website_favicon'] = $request->file('website_favicon')->store('icons', 'public');
        }

        $setting->update($data);

        return response()->json([
            'message' => 'Website settings updated',
            'setting' => array_merge($setting->fresh()->toArray(), [
                'company_logo_url' => \App\Helpers\Setting::resolve_company_logo_url($setting->company_logo),
                'website_favicon_url' => \App\Helpers\Setting::resolve_favicon_url($setting->website_favicon),
            ]),
        ]);
    }

    private function isManagedFilePath(?string $path): bool
    {
        if (! $path) {
            return false;
        }

        return str_contains($path, 'file-manager')
            || str_starts_with($path, 'http://')
            || str_starts_with($path, 'https://');
    }

    /* =========================
       CONTACT TAB
    ========================= */
    public function updateContact(Request $request)
    {
        $request->validate([
            'company_address' => 'required',
            'mobile_no' => 'required',
            'tel_no' => 'required',
            'email' => 'required|email',
        ]);

        $this->setting()->update($request->only([
            'company_address',
            'mobile_no',
            'fax_no',
            'tel_no',
            'email',
        ]));

        return response()->json(['message' => 'Contact settings updated']);
    }

    /* =========================
       DATA PRIVACY TAB
    ========================= */
    public function updatePrivacy(Request $request)
    {
        $request->validate([
            'data_privacy_title' => 'required',
            'data_privacy_popup_content' => 'required',
            'data_privacy_content' => 'required',
        ]);

        $this->setting()->update($request->only([
            'data_privacy_title',
            'data_privacy_popup_content',
            'data_privacy_content',
        ]));

        return response()->json(['message' => 'Data privacy updated']);
    }
}
