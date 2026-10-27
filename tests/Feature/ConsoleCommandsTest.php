<?php

namespace Veltrix\Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Veltrix\Auth\Database\Administrator;
use Veltrix\Auth\Database\Menu;
use Veltrix\Auth\Database\Permission;
use Veltrix\Auth\Database\Role;
use Veltrix\Tests\TestCase;

class ConsoleCommandsTest extends TestCase
{
    protected function tearDown(): void
    {
        $testController = veltrix_path('Controllers/PostController.php');
        if (File::exists($testController)) {
            File::delete($testController);
        }

        parent::tearDown();
    }

    public function test_create_user_command_via_veltrix_signature(): void
    {
        $this->artisan('veltrix:create-user', [
            '--user'     => 'newadmin',
            '--name'     => 'New Admin',
            '--password' => 'SafePass123!',
        ])->assertExitCode(0);

        $user = Administrator::where('username', 'newadmin')->first();
        $this->assertNotNull($user);
        $this->assertEquals('New Admin', $user->name);
        $this->assertTrue(Hash::check('SafePass123!', $user->password));

        $adminRole = Role::where('slug', 'administrator')->first();
        $this->assertTrue($user->roles->contains($adminRole->id));
    }

    public function test_create_user_command_via_legacy_alias(): void
    {
        $this->artisan('admin:create-user', [
            '--user'     => 'legacybot',
            '--name'     => 'Legacy Bot',
            '--password' => 'SafePassLegacy123!',
        ])->assertExitCode(0);

        $user = Administrator::where('username', 'legacybot')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('SafePassLegacy123!', $user->password));
    }

    public function test_reset_password_command(): void
    {
        $user = Administrator::where('username', 'admin')->first();
        $this->assertNotNull($user);

        $this->artisan('veltrix:reset-password', [
            'username'   => 'admin',
            '--password' => 'ResetPassword123!',
        ])->assertExitCode(0);

        $user->refresh();
        $this->assertTrue(Hash::check('ResetPassword123!', $user->password));
    }

    public function test_reset_password_nonexistent_user_fails(): void
    {
        $this->artisan('veltrix:reset-password', [
            'username'   => 'does_not_exist',
            '--password' => 'foo',
        ])->assertExitCode(1);
    }

    public function test_menu_listing_command(): void
    {
        $this->artisan('veltrix:menu')
            ->expectsTable(
                ['ID', 'Parent', 'Order', 'Title', 'Icon', 'URI'],
                Menu::all(['id', 'parent_id', 'order', 'title', 'icon', 'uri'])->toArray()
            )
            ->assertExitCode(0);
    }

    public function test_permission_listing_command(): void
    {
        $permissions = Permission::all(['id', 'slug', 'name', 'http_method', 'http_path'])
            ->map(fn($item) => [
                'id'          => $item->id,
                'slug'        => $item->slug,
                'name'        => $item->name,
                'http_method' => is_array($item->http_method) ? implode(', ', $item->http_method) : (string)$item->http_method,
                'http_path'   => $item->http_path,
            ])
            ->toArray();

        $this->artisan('veltrix:permission')
            ->expectsTable(
                ['ID', 'Slug', 'Name', 'HTTP Method', 'HTTP Path'],
                $permissions
            )
            ->assertExitCode(0);
    }

    public function test_make_controller_command(): void
    {
        $this->artisan('veltrix:make', [
            'name'    => 'PostController',
            '--model' => 'App\\Models\\Post',
            '--title' => 'Blog Posts',
        ])->assertExitCode(0);

        $targetFile = veltrix_path('Controllers/PostController.php');
        $this->assertFileExists($targetFile);

        $content = File::get($targetFile);
        $this->assertStringContainsString('class PostController extends AdminController', $content);
        $this->assertStringContainsString('Blog Posts', $content);
        $this->assertStringContainsString('App\\Models\\Post', $content);
    }
}
