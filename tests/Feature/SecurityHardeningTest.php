<?php

namespace Veltrix\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Veltrix\Actions\Action;
use Veltrix\Auth\Database\Administrator;
use Veltrix\Facades\Veltrix;
use Veltrix\Form\Field\File;
use Veltrix\Form\Field\Image;
use Veltrix\Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    protected Administrator $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        Veltrix::routes();
        $this->adminUser = Administrator::where('username', 'admin')->first();
    }

    public function test_malicious_php_upload_is_blocked_cve_2023_24249(): void
    {
        Storage::fake('public');

        $fileField = new File('attachment');
        $maliciousFile = UploadedFile::fake()->create('shell.php', 100, 'application/x-php');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/strictly prohibited/i');

        $fileField->prepare($maliciousFile);
    }

    public function test_malicious_phar_and_script_upload_blocked(): void
    {
        Storage::fake('public');

        $fileField = new File('document');
        $pharFile = UploadedFile::fake()->create('payload.phar', 100, 'application/octet-stream');

        $this->expectException(\InvalidArgumentException::class);
        $fileField->prepare($pharFile);
    }

    public function test_double_extension_attack_is_blocked(): void
    {
        Storage::fake('public');

        $imageField = new Image('photo');
        $doubleExtFile = UploadedFile::fake()->create('exploit.php.jpg', 100, 'image/jpeg');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/strictly prohibited/i');

        $imageField->prepare($doubleExtFile);
    }

    public function test_valid_image_upload_succeeds(): void
    {
        Storage::fake('public');

        $imageField = new Image('avatar');
        $validImage = UploadedFile::fake()->create('profile.jpg', 100, 'image/jpeg');

        $storedPath = $imageField->prepare($validImage);

        $this->assertNotNull($storedPath);
        Storage::disk('public')->assertExists($storedPath);
    }

    public function test_unauthenticated_handle_action_is_rejected(): void
    {
        $response = $this->post(veltrix_base_path('_handle_action_'), [
            '_action' => 'switch',
            '_model'  => Administrator::class,
            '_id'     => $this->adminUser->id,
            'column'  => 'name',
            'value'   => 'Hacked',
        ]);

        $this->assertTrue(in_array($response->status(), [401, 403]));
    }

    public function test_arbitrary_class_action_execution_is_blocked(): void
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->post(veltrix_base_path('_handle_action_'), [
                '_action' => \Illuminate\Support\Facades\Artisan::class,
            ]);

        $response->assertStatus(403);
    }

    public function test_sensitive_columns_cannot_be_toggled_via_switch(): void
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->post(veltrix_base_path('_handle_action_'), [
                '_action' => 'switch',
                '_model'  => Administrator::class,
                '_id'     => $this->adminUser->id,
                'column'  => 'password',
                'value'   => 'newplaintextpassword',
            ]);

        $response->assertStatus(403);
    }

    public function test_custom_authorized_action_succeeds(): void
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->post(veltrix_base_path('_handle_action_'), [
                '_action' => TestCustomSecurityAction::class,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => true, 'message' => 'Action executed safely']);
    }
}

class TestCustomSecurityAction extends Action
{
    public function handle(\Illuminate\Http\Request $request)
    {
        return $this->response()->success('Action executed safely');
    }
}
