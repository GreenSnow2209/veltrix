<?php

namespace Veltrix\Tests\Feature;

use Illuminate\Support\Facades\Hash;
use Veltrix\Auth\Database\Administrator;
use Veltrix\Auth\Database\OperationLog;
use Veltrix\Auth\Database\Permission;
use Veltrix\Auth\Database\Role;
use Veltrix\Facades\Veltrix;
use Veltrix\Tests\TestCase;

class UserResourceCrudTest extends TestCase
{
    protected Administrator $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        Veltrix::routes();

        $this->adminUser = Administrator::where('username', 'admin')->first();
    }

    public function test_user_listing(): void
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->get(veltrix_base_path('auth/users'));

        $response->assertStatus(200);
        $response->assertSee('Administrator Users');
        $response->assertSee('admin');
        $response->assertSee('Roles');
    }

    public function test_user_search_and_filter(): void
    {
        Administrator::create([
            'username' => 'johndoe',
            'name'     => 'John Doe',
            'password' => Hash::make('secret123'),
        ]);

        // Search match
        $response = $this->actingAs($this->adminUser, 'admin')
            ->get(veltrix_base_path('auth/users?_search_=johndoe'));

        $response->assertStatus(200);
        $response->assertSee('John Doe');

        // Filter by username
        $filterResponse = $this->actingAs($this->adminUser, 'admin')
            ->get(veltrix_base_path('auth/users?username=johndoe'));

        $filterResponse->assertStatus(200);
        $filterResponse->assertSee('John Doe');
    }

    public function test_user_sorting(): void
    {
        Administrator::create([
            'username' => 'alice',
            'name'     => 'Alice Smith',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->actingAs($this->adminUser, 'admin')
            ->get(veltrix_base_path('auth/users?_sort[column]=id&_sort[type]=desc'));

        $response->assertStatus(200);
        $response->assertSee('Alice Smith');
    }

    public function test_user_creation_form(): void
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->get(veltrix_base_path('auth/users/create'));

        $response->assertStatus(200);
        $response->assertSee('Create Record');
        $response->assertSee('Username');
        $response->assertSee('Password');
    }

    public function test_user_creation_validation(): void
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->post(veltrix_base_path('auth/users'), [
                'username' => '',
                'name'     => '',
            ]);

        $response->assertSessionHasErrors(['username', 'name']);
    }

    public function test_user_creation_and_role_attachment(): void
    {
        $role = Role::where('slug', 'administrator')->first();

        $response = $this->actingAs($this->adminUser, 'admin')
            ->post(veltrix_base_path('auth/users'), [
                'username' => 'newadmin',
                'name'     => 'New Admin',
                'password' => 'secret99',
                'roles'    => [$role->id],
            ]);

        $response->assertRedirect(veltrix_base_path('auth/users'));

        $newUser = Administrator::where('username', 'newadmin')->first();
        $this->assertNotNull($newUser);
        $this->assertTrue(Hash::check('secret99', $newUser->password));
        $this->assertTrue($newUser->roles->contains($role->id));
    }

    public function test_user_detail_view(): void
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->get(veltrix_base_path("auth/users/{$this->adminUser->id}"));

        $response->assertStatus(200);
        $response->assertSee('Record Details');
        $response->assertSee('admin');
        $response->assertSee('Administrator');
    }

    public function test_user_editing_and_update(): void
    {
        $editFormResponse = $this->actingAs($this->adminUser, 'admin')
            ->get(veltrix_base_path("auth/users/{$this->adminUser->id}/edit"));

        $editFormResponse->assertStatus(200);
        $editFormResponse->assertSee('Edit Record');

        $updateResponse = $this->actingAs($this->adminUser, 'admin')
            ->put(veltrix_base_path("auth/users/{$this->adminUser->id}"), [
                'username' => 'admin',
                'name'     => 'Super Administrator',
            ]);

        $updateResponse->assertRedirect(veltrix_base_path('auth/users'));

        $this->adminUser->refresh();
        $this->assertEquals('Super Administrator', $this->adminUser->name);
    }

    public function test_user_deletion(): void
    {
        $toDelete = Administrator::create([
            'username' => 'temporary',
            'name'     => 'Temporary User',
            'password' => Hash::make('pass'),
        ]);

        $response = $this->actingAs($this->adminUser, 'admin')
            ->delete(veltrix_base_path("auth/users/{$toDelete->id}"));

        $this->assertNull(Administrator::find($toDelete->id));
    }

    public function test_unauthorized_user_is_denied(): void
    {
        // Create user with zero roles and zero permissions
        $limitedUser = Administrator::create([
            'username' => 'limited',
            'name'     => 'Limited User',
            'password' => Hash::make('pass'),
        ]);

        $response = $this->actingAs($limitedUser, 'admin')
            ->get(veltrix_base_path('auth/users'));

        $response->assertStatus(403);
    }

    public function test_operation_logging_records_mutations(): void
    {
        $this->actingAs($this->adminUser, 'admin')
            ->post(veltrix_base_path('auth/users'), [
                'username' => 'loggeduser',
                'name'     => 'Logged User',
                'password' => 'secret123',
            ]);

        $log = OperationLog::where('path', 'like', '%auth/users%')
            ->where('method', 'POST')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($this->adminUser->id, $log->user_id);
        $this->assertStringNotContainsString('secret123', $log->input); // Password must be masked
    }
}
