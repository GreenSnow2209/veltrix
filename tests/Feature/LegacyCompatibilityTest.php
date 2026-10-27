<?php

namespace Veltrix\Tests\Feature;

use Encore\Admin\Auth\Database\Administrator as LegacyAdminUser;
use Encore\Admin\Auth\Permission as LegacyPermission;
use Encore\Admin\Controllers\AdminController as LegacyAdminController;
use Encore\Admin\Facades\Admin as LegacyAdmin;
use Encore\Admin\Form as LegacyForm;
use Encore\Admin\Grid as LegacyGrid;
use Encore\Admin\Layout\Content as LegacyContent;
use Encore\Admin\Show as LegacyShow;
use Encore\Admin\Tree as LegacyTree;
use Encore\Admin\Widgets\Box as LegacyBox;
use Encore\Admin\Widgets\InfoBox as LegacyInfoBox;
use Illuminate\Support\Facades\Route;
use Veltrix\Auth\Database\Administrator;
use Veltrix\Facades\Veltrix;
use Veltrix\Tests\TestCase;

class LegacyCompatibilityTest extends TestCase
{
    protected Administrator $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        Veltrix::routes();
        $this->adminUser = Administrator::where('username', 'admin')->first();
    }

    public function test_legacy_admin_facade_and_helpers(): void
    {
        $this->actingAs($this->adminUser, 'admin');

        $this->assertNotNull(LegacyAdmin::user());
        $this->assertEquals('admin', LegacyAdmin::user()->username);
        $this->assertEquals(LegacyAdmin::user()->id, admin_user()->id);

        $this->assertEquals('/admin/test', admin_base_path('test'));
        $this->assertStringContainsString('/admin/test', admin_url('test'));

        admin_toastr('Operation completed successfully', 'success');
        $toastr = session('toastr');
        $this->assertNotNull($toastr);
        $this->assertEquals('success', $toastr->first('type'));
        $this->assertEquals('Operation completed successfully', $toastr->first('message'));

        $this->assertEquals('Username', admin_trans('username'));
    }

    public function test_legacy_asset_injection(): void
    {
        LegacyAdmin::js('https://cdn.example.com/custom.js');
        LegacyAdmin::css('https://cdn.example.com/custom.css');

        $this->assertContains('https://cdn.example.com/custom.js', LegacyAdmin::js());
        $this->assertContains('https://cdn.example.com/custom.css', LegacyAdmin::css());
    }

    public function test_legacy_grid_instantiation_and_rendering(): void
    {
        $grid = new LegacyGrid(new Administrator());
        $grid->column('id', 'ID')->sortable();
        $grid->column('username', 'Username');
        $grid->column('name', 'Name');

        $html = $grid->render();
        $this->assertStringContainsString('Username', $html);
        $this->assertStringContainsString('admin', $html);
    }

    public function test_legacy_form_instantiation_and_rendering(): void
    {
        $form = new LegacyForm(new Administrator());
        $form->text('username', 'Username')->required();
        $form->text('name', 'Name')->required();
        $form->password('password', 'Password');

        $html = $form->render();
        $this->assertStringContainsString('Username', $html);
        $this->assertStringContainsString('field-username', $html);
    }

    public function test_legacy_show_instantiation_and_rendering(): void
    {
        $show = new LegacyShow($this->adminUser);
        $show->field('id', 'ID');
        $show->field('username', 'Username');
        $show->field('name', 'Name');

        $html = $show->render();
        $this->assertStringContainsString('Username', $html);
        $this->assertStringContainsString('admin', $html);
    }

    public function test_legacy_tree_instantiation_and_rendering(): void
    {
        $tree = new LegacyTree(new \Veltrix\Auth\Database\Menu());
        $tree->branch(function ($branch) {
            return "<span>{$branch['title']}</span>";
        });

        $html = $tree->render();
        $this->assertStringContainsString('vx-tree', $html);
        $this->assertStringContainsString('Dashboard', $html);
    }

    public function test_legacy_widgets_rendering(): void
    {
        $box = new LegacyBox('Stats Widget', '<p>Active Sessions: 42</p>');
        $boxHtml = $box->render();
        $this->assertStringContainsString('Stats Widget', $boxHtml);
        $this->assertStringContainsString('Active Sessions: 42', $boxHtml);

        $infoBox = new LegacyInfoBox('Revenue', 'dollar-sign', 'green', '/admin/orders', '$128,000');
        $infoBoxHtml = $infoBox->render();
        $this->assertStringContainsString('Revenue', $infoBoxHtml);
        $this->assertStringContainsString('$128,000', $infoBoxHtml);
    }

    public function test_legacy_permission_checker(): void
    {
        $this->actingAs($this->adminUser, 'admin');

        $this->assertTrue(LegacyPermission::isAdministrator());
        $this->assertTrue(LegacyPermission::check('*'));
    }

    public function test_legacy_admin_controller_route_execution(): void
    {
        Route::middleware(['admin'])->group(function () {
            Route::resource('admin/legacy-users', LegacyTestUserController::class);
        });

        $response = $this->actingAs($this->adminUser, 'admin')
            ->get('/admin/legacy-users');

        $response->assertStatus(200);
        $response->assertSee('Legacy Users');
        $response->assertSee('admin');
    }
}

class LegacyTestUserController extends LegacyAdminController
{
    protected string $title = 'Legacy Users';

    protected function grid(): LegacyGrid
    {
        $grid = new LegacyGrid(new Administrator());
        $grid->column('id', 'ID');
        $grid->column('username', 'Username');
        return $grid;
    }

    protected function form(): LegacyForm
    {
        $form = new LegacyForm(new Administrator());
        $form->text('username', 'Username');
        return $form;
    }

    protected function detail(mixed $id): LegacyShow
    {
        $show = new LegacyShow(Administrator::findOrFail($id));
        $show->field('id', 'ID');
        $show->field('username', 'Username');
        return $show;
    }
}
