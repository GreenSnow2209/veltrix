<?php

namespace Veltrix\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Veltrix\Facades\Veltrix;
use Veltrix\Form\Form;
use Veltrix\Layout\Content;

class AuthController extends Controller
{
    protected string $loginView = 'veltrix::auth.login';

    public function getLogin(): mixed
    {
        if (Veltrix::guard()->check()) {
            return redirect(veltrix_base_path());
        }

        $view = view()->exists($this->loginView) ? $this->loginView : 'admin::login';
        return view($view);
    }

    public function postLogin(Request $request): RedirectResponse
    {
        $this->loginValidator($request->all())->validate();

        $credentials = $request->only(['username', 'password']);
        $remember = $request->boolean('remember');

        if (Veltrix::guard()->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            veltrix_toastr(veltrix_trans('login_successful') ?: 'Welcome back!', 'success');
            return redirect()->intended(veltrix_base_path());
        }

        return back()->withInput()->withErrors([
            'username' => veltrix_trans('auth_failed') ?: 'These credentials do not match our records.',
        ]);
    }

    public function getLogout(Request $request): RedirectResponse
    {
        Veltrix::guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(veltrix_base_path('auth/login'));
    }

    public function getSetting(Content $content): Content
    {
        $form = $this->settingForm();
        $form->edit(Veltrix::user()->getKey());

        return $content
            ->title(veltrix_trans('user_setting') ?: 'User Profile')
            ->body($form);
    }

    public function putSetting(Request $request): mixed
    {
        return $this->settingForm()->update(Veltrix::user()->getKey());
    }

    protected function settingForm(): Form
    {
        $class = config('veltrix.database.users_model', config('admin.database.users_model'));
        $form = new Form(new $class());

        $form->display('username', veltrix_trans('username') ?: 'Username');
        $form->text('name', veltrix_trans('name') ?: 'Name')->rules('required');
        $form->image('avatar', veltrix_trans('avatar') ?: 'Avatar');
        $form->password('password', veltrix_trans('password') ?: 'Password')->rules('nullable|confirmed|min:6');
        $form->password('password_confirmation', veltrix_trans('password_confirmation') ?: 'Password Confirmation');

        $form->setAction(veltrix_base_path('auth/setting'));

        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            } else {
                $form->ignore(['password', 'password_confirmation']);
            }
        });

        $form->saved(function () {
            veltrix_toastr(veltrix_trans('update_succeeded') ?: 'Profile updated successfully', 'success');
            return redirect(veltrix_base_path('auth/setting'));
        });

        return $form;
    }

    protected function loginValidator(array $data)
    {
        return Validator::make($data, [
            'username' => 'required',
            'password' => 'required',
        ]);
    }
}
