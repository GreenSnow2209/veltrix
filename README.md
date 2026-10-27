# Veltrix

<p align="center">
  <b>Familiar to Build. Modern by Design.</b>
</p>

<p align="center">
  <a href="https://packagist.org/packages/veltrix/laravel-admin"><img src="https://img.shields.io/packagist/v/veltrix/laravel-admin.svg?style=flat-square" alt="Latest Version"></a>
  <a href="https://packagist.org/packages/veltrix/laravel-admin"><img src="https://img.shields.io/packagist/dt/veltrix/laravel-admin.svg?style=flat-square" alt="Total Downloads"></a>
  <a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square" alt="Software License"></a>
  <img src="https://img.shields.io/badge/php-%5E8.2%20%7C%20%5E8.3%20%7C%20%5E8.4%20%7C%20%5E8.5-blue?style=flat-square" alt="PHP 8.2-8.5">
  <img src="https://img.shields.io/badge/laravel-%5E11.0%20%7C%20%5E12.0%20%7C%20%5E13.0-red?style=flat-square" alt="Laravel 11, 12, 13">
</p>

---

## 💡 Overview

**Veltrix** is an open-source administrative framework for Laravel that combines the beloved, high-velocity developer experience and syntax of `z-song/laravel-admin` with modern frontend engineering, contemporary UI/UX, strict security hardening, and full compatibility with modern Laravel (11.x, 12.x, and 13.x) and PHP (8.2 through 8.5).

If you loved building CRUD applications with `Grid`, `Form`, `Show`, and `Tree` in `laravel-admin`, Veltrix allows you to keep that productive workflow without wrestling with abandoned jQuery plugins, unpatched vulnerabilities (like CVE-2023-24249), or heavy single-page application setups.

---

## ✨ Key Features

- **🎯 100% Familiar DX**: Build resources using familiar declarative PHP syntax (`Grid`, `Form`, `Show`, `Tree`, `Content`).
- **🚀 Modern Vanilla Frontend**: Refined, responsive UI with native dark/light mode toggle, clean CSS variables, and zero mandatory node/npm or Livewire build dependencies in host applications.
- **🛡️ Built-in Security Hardening**:
  - Direct mitigation for **CVE-2023-24249** (arbitrary file upload / RCE) with multi-extension and script execution blocking.
  - Strict server-side authentication & authorization verification on AJAX actions and inline switch mutation.
  - Zero insecure default passwords in production.
  - Operation logging with automatic sensitive credential redaction.
- **🔄 Drop-in Legacy Compatibility Layer**: Built-in compatibility shims for `Encore\Admin\*` namespaces, facades, and helpers (`admin_url()`, `admin_user()`, `admin_toastr()`), allowing seamless migration of existing controllers.
- **⚡ Modern Laravel & PHP Support**: Native support for Laravel 11.x, 12.x, and 13.x on PHP 8.2, 8.3, 8.4, and 8.5.
- **👥 Enterprise RBAC**: Role-based access control with fine-grained route/method permissions, recursive menu management, and audit logging.

---

## 📦 Requirements

- **PHP**: `^8.2`, `^8.3`, `^8.4`, or `^8.5`
- **Laravel**: `^11.0`, `^12.0`, or `^13.0`
- **Database**: MySQL 8+, PostgreSQL 13+, or SQLite 3.35+

---

## 🚀 Quick Start

### 1. Install via Composer

```bash
composer require veltrix/laravel-admin
```

### 2. Run the Veltrix Installer

```bash
php artisan veltrix:install
```

This command will:
1. Run database migrations (`admin_users`, `admin_roles`, `admin_permissions`, `admin_menu`, `admin_operation_log`).
2. Seed initial administrative tables and permissions.
3. Publish configuration to `config/veltrix.php`.
4. Generate the `app/Admin` directory containing `routes.php`, `bootstrap.php`, and `Controllers/HomeController.php`.

### 3. Access the Admin Panel

Start your Laravel local server:
```bash
php artisan serve
```

Navigate to: `http://localhost:8000/admin`  
Log in with your administrator credentials created during installation.

---

## 🛠️ Usage Example

### Creating a CRUD Controller

Use the Artisan command to scaffold a new Admin CRUD Controller:

```bash
php artisan veltrix:make PostController --model="App\Models\Post"
```

The generated controller at `app/Admin/Controllers/PostController.php`:

```php
<?php

namespace App\Admin\Controllers;

use App\Models\Post;
use Veltrix\Controllers\AdminController;
use Veltrix\Form\Form;
use Veltrix\Grid\Grid;
use Veltrix\Show\Show;

class PostController extends AdminController
{
    protected string $title = 'Posts';

    protected function grid(): Grid
    {
        $grid = new Grid(new Post());

        $grid->column('id', 'ID')->sortable();
        $grid->column('title', 'Title')->filter();
        $grid->column('author.name', 'Author');
        $grid->column('is_published', 'Published')->switch();
        $grid->column('created_at', 'Created At')->sortable();

        $grid->filter(function ($filter) {
            $filter->like('title', 'Title');
            $filter->equal('is_published', 'Status')->select([
                1 => 'Published',
                0 => 'Draft',
            ]);
        });

        return $grid;
    }

    protected function form(): Form
    {
        $form = new Form(new Post());

        $form->display('id', 'ID');
        $form->text('title', 'Title')->required();
        $form->textarea('content', 'Content');
        $form->image('cover_image', 'Cover Image')->move('covers');
        $form->switch('is_published', 'Published')->default(false);

        return $form;
    }

    protected function detail(mixed $id): Show
    {
        $show = new Show(Post::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('title', 'Title');
        $show->field('content', 'Content');
        $show->field('cover_image', 'Cover Image')->image();
        $show->field('created_at', 'Created At');

        return $show;
    }
}
```

Register the resource route in `app/Admin/routes.php`:

```php
$router->resource('posts', PostController::class);
```

---

## 🔄 Migrating from `z-song/laravel-admin`

Veltrix provides comprehensive backward compatibility shims for `Encore\Admin\*` namespaces. In most applications, migration requires minimal changes:

1. Replace the composer requirement:
   ```bash
   composer remove encore/laravel-admin
   composer require veltrix/laravel-admin
   ```
2. Your existing controllers using:
   ```php
   use Encore\Admin\Controllers\AdminController;
   use Encore\Admin\Grid;
   use Encore\Admin\Form;
   use Encore\Admin\Show;
   use Encore\Admin\Facades\Admin;
   ```
   continue to work seamlessly through the compatibility layer without immediate refactoring!
3. Re-publish assets and migrations:
   ```bash
   php artisan veltrix:publish --assets --force
   ```

---

## ⌨️ Artisan Commands

| Command | Alias | Description |
|---|---|---|
| `veltrix:install` | `admin:install` | Install Veltrix administrative panel and initialize database |
| `veltrix:make` | `admin:make` | Scaffold a new CRUD controller with Grid, Form, and Show |
| `veltrix:controller` | `admin:controller` | Generate a resource controller |
| `veltrix:create-user` | `admin:create-user` | Create an administrator user securely |
| `veltrix:reset-password` | `admin:reset-password` | Reset password for an administrator |
| `veltrix:menu` | `admin:menu` | Display sidebar menu hierarchy |
| `veltrix:permission` | `admin:permission` | List configured permissions and routes |
| `veltrix:publish` | `admin:publish` | Publish configuration, translations, or UI assets |

---

## 🔒 Security Policy

If you discover a security vulnerability within Veltrix, please send an e-mail to security@veltrix.dev. All security vulnerabilities will be promptly addressed.

---

## 📄 License

Veltrix is open-source software licensed under the [MIT license](LICENSE).
