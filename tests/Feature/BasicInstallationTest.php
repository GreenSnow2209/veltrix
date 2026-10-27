<?php

namespace Veltrix\Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Veltrix\Auth\Database\Administrator;
use Veltrix\Facades\Veltrix;
use Veltrix\Tests\TestCase;

class BasicInstallationTest extends TestCase
{
    public function test_tables_created_successfully(): void
    {
        $this->assertTrue(Schema::hasTable('admin_users'));
        $this->assertTrue(Schema::hasTable('admin_roles'));
        $this->assertTrue(Schema::hasTable('admin_permissions'));
        $this->assertTrue(Schema::hasTable('admin_menu'));
        $this->assertTrue(Schema::hasTable('admin_operation_log'));
    }

    public function test_administrator_seeded(): void
    {
        $user = Administrator::where('username', 'admin')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->isAdministrator());
        $this->assertTrue($user->can('*'));
    }

    public function test_login_page_renders(): void
    {
        Veltrix::routes();

        $response = $this->get(veltrix_base_path('auth/login'));
        $response->assertStatus(200);
        $response->assertSee('Login');
        $response->assertSee('Veltrix');
    }

    public function test_login_authentication_works(): void
    {
        Veltrix::routes();

        $response = $this->post(veltrix_base_path('auth/login'), [
            'username' => 'admin',
            'password' => 'admin',
        ]);

        $response->assertRedirect(veltrix_base_path());
        $this->assertTrue(Veltrix::guard()->check());
        $this->assertEquals('admin', Veltrix::user()->username);
    }
}
