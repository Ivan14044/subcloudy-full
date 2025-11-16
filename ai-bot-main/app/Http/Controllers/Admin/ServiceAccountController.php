<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ServiceAccountController extends Controller
{
    public function index()
    {
        $serviceAccounts = ServiceAccount::with('service')->orderBy('id', 'desc')->get();

        return view('admin.service-accounts.index', compact('serviceAccounts'));
    }

    public function create()
    {
        $services = Service::withEnglishName()->orderBy('id', 'desc')->get();

        $user = auth()->user();
        $apiToken = $user->createToken('blade-temp')->plainTextToken;

        return view('admin.service-accounts.create', compact('services', 'apiToken'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->getRules());

        ServiceAccount::create($validated);

        // After successful save, save profile data by port/profile and then stop the temporary session
        $pidValue = $request->input('session_pid');
        $portValue = $request->input('session_port');
        $profileId = $request->input('profile_id');
        $pid = is_null($pidValue) ? 0 : (int) $pidValue;
        $port = is_null($portValue) ? 0 : (int) $portValue;
        if ($port > 0 && $profileId) {
            try {
                $baseUrl = config('services.browser_api.url', env('BROWSER_API_URL', 'https://workspace.subcloudy.com/api/'));
                // Ask the browser API to copy the running profile to a persistent location
                Http::timeout(15)->get(rtrim($baseUrl, '/') . '/copy_profile', [
                    'port' => $port,
                    'profile' => $profileId,
                ]);
            } catch (\Throwable $e) {
                // Ignore errors, proceed
            }
        }

        if ($pid > 0) {
            try {
                $baseUrl = config('services.browser_api.url', env('BROWSER_API_URL', 'https://workspace.subcloudy.com/api/'));
                Http::timeout(8)->post(rtrim($baseUrl, '/') . '/stop', ['pid' => $pid]);
            } catch (\Throwable $e) {
                // Ignore errors, proceed to redirect
            }

            Cache::forget('browser_sessions.active_count');
        }

        return redirect()->route('admin.service-accounts.index')->with('success', 'Service account successfully created.');
    }

    public function edit(ServiceAccount $serviceAccount)
    {
        $services = Service::withEnglishName()->orderBy('id', 'desc')->get();

        $user = auth()->user();
        $apiToken = $user->createToken('blade-temp')->plainTextToken;

        return view('admin.service-accounts.edit', compact('serviceAccount', 'services', 'apiToken'));
    }

    public function update(Request $request, ServiceAccount $serviceAccount)
    {
        $validated = $request->validate($this->getRules($serviceAccount->id));

        $serviceAccount->update($validated);

        // Copy running profile to persistent storage and stop the session if provided
        $pidValue = $request->input('session_pid');
        $portValue = $request->input('session_port');
        $profileId = $request->input('profile_id');
        $pid = is_null($pidValue) ? 0 : (int) $pidValue;
        $port = is_null($portValue) ? 0 : (int) $portValue;
        if ($port > 0 && $profileId) {
            try {
                $baseUrl = config('services.browser_api.url', env('BROWSER_API_URL', 'https://workspace.subcloudy.com/api/'));
                Http::timeout(15)->get(rtrim($baseUrl, '/') . '/copy_profile', [
                    'port' => $port,
                    'profile' => $profileId,
                ]);
            } catch (\Throwable $e) {
                // Ignore errors
            }
        }

        if ($pid > 0) {
            try {
                $baseUrl = config('services.browser_api.url', env('BROWSER_API_URL', 'https://workspace.subcloudy.com/api/'));
                Http::timeout(8)->post(rtrim($baseUrl, '/') . '/stop', ['pid' => $pid]);
            } catch (\Throwable $e) {
                // Ignore errors
            }
            Cache::forget('browser_sessions.active_count');
        }

        $route = $request->has('save')
            ? route('admin.service-accounts.edit', $serviceAccount->id)
            : route('admin.service-accounts.index');

        return redirect($route)->with('success', 'Service account successfully updated.');
    }

    public function destroy(ServiceAccount $serviceAccount)
    {
        $serviceAccount->delete();

        return redirect()->route('admin.service-accounts.index')->with('success', 'Service account successfully deleted.');
    }

    private function getRules($id = null): array
    {
        return [
            'service_id' => ['required', 'exists:services,id'],
            'profile_id' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('service_accounts', 'profile_id')->ignore($id),
            ],
            'credentials' => ['nullable', 'array'],
            'expiring_at' => ['nullable', 'date'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
