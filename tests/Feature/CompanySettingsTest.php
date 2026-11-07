<?php

namespace Tests\Feature;

use App\Models\CompanySetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompanySettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_information_is_saved_and_rendered_across_pages(): void
    {
        $this->postJson('/settings/company', ['name' => 'Example Medical', 'business' => 'Equipment Supplier', 'email' => 'office@example.com', 'tagline' => 'Company tagline'])
            ->assertOk()->assertJsonPath('company.name', 'Example Medical');
        $this->assertDatabaseHas('company_settings', ['id' => 1, 'name' => 'Example Medical']);
        $this->get('/')->assertOk()->assertSee('Example Medical')->assertSee('Company tagline');
        $this->get('/settings')->assertOk()->assertSee('Example Medical');
        $this->postJson('/settings/company', ['name' => 'Updated Company'])->assertOk();
        $this->assertDatabaseCount('company_settings', 1);
    }

    public function test_logo_is_saved_rendered_preserved_and_removed(): void
    {
        Storage::fake('local');
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
        $logo = UploadedFile::fake()->createWithContent('logo.png', $png);
        $this->post('/settings/company', ['name' => 'Logo Company', 'logo' => $logo], ['Accept' => 'application/json'])->assertOk();
        $stored = CompanySetting::findOrFail(1)->logo;
        $this->assertStringStartsWith('company-logos/', $stored);
        Storage::disk('local')->assertExists($stored);
        $this->get(route('settings.company.logo'))->assertOk();
        $url = route('settings.company.logo');
        $this->get('/')->assertOk()->assertSee($url, false);
        $this->get('/settings')->assertOk()->assertSee('Current company logo')->assertSee($url, false);
        $this->get(route('sales.index'))->assertOk()->assertSee('src="'.$url.'" alt="Logo Company"', false);
        $this->postJson('/settings/company', ['name' => 'Logo Company'])->assertOk();
        $this->assertSame($stored, CompanySetting::findOrFail(1)->logo);
        $this->postJson('/settings/company', ['name' => 'Logo Company', 'remove_logo' => true])->assertOk();
        $this->assertNull(CompanySetting::findOrFail(1)->logo);
        Storage::disk('local')->assertMissing($stored);
        $this->get(route('settings.company.logo'))->assertNotFound();
        $this->get(route('sales.index'))->assertOk()->assertDontSee($stored, false);
    }

    public function test_invalid_company_and_non_image_logo_are_rejected(): void
    {
        $this->postJson('/settings/company', ['name' => '', 'email' => 'invalid'])->assertUnprocessable()->assertJsonValidationErrors(['name', 'email']);
        $this->post('/settings/company', ['name' => 'Company', 'logo' => UploadedFile::fake()->create('logo.txt', 1, 'text/plain')], ['Accept' => 'application/json'])->assertUnprocessable()->assertJsonValidationErrors('logo');
        $this->assertDatabaseCount('company_settings', 0);
    }
}

