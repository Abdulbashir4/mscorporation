<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CompanySettingController extends Controller
{
    public function logo(): StreamedResponse
    {
        $path = CompanySetting::find(1)?->logo;
        abort_unless($path && str_starts_with($path, 'company-logos/') && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'business' => ['nullable', 'string', 'max:200'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
            'license' => ['nullable', 'string', 'max:100'],
            'vat' => ['nullable', 'string', 'max:100'],
            'tagline' => ['nullable', 'string', 'max:200'],
            'logo' => ['nullable', File::image()->types(['png', 'jpg', 'jpeg', 'webp'])->max(2048)],
            'remove_logo' => ['nullable', 'boolean'],
        ]);
        unset($data['logo'], $data['remove_logo']);
        if ($request->boolean('remove_logo')) {
            $data['logo'] = null;
        }
        $previousLogo = CompanySetting::find(1)?->logo;
        $path = null;
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('company-logos', 'local');
            if ($path === false) {
                throw ValidationException::withMessages(['logo' => 'The logo could not be saved. Please try again.']);
            }
            $data['logo'] = $path;
        }
        try {
            CompanySetting::unguarded(fn () => CompanySetting::updateOrCreate(['id' => 1], $data));
        } catch (\Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $exception;
        }
        if (array_key_exists('logo', $data) && $previousLogo && str_starts_with($previousLogo, 'company-logos/')) {
            Storage::disk('local')->delete($previousLogo);
        }

        return response()->json(['message' => 'Company information saved.', 'company' => CompanySetting::profile()]);
    }
}
