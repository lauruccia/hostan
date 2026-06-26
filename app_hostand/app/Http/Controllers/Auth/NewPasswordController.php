<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        // DEBUG: Log page load data
        \Log::info('Password reset page loaded', [
            'email_from_url' => $request->email,
            'token_from_route' => $request->route('token'),
            'all_request_data' => $request->all()
        ]);

        $user = \App\Models\User::find(1);
        if ($user && !empty($user->lang)) {
            \App::setLocale($user->lang);
        }
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        // DEBUG: Log incoming request data
        \Log::info('Password reset request received', [
            'email' => $request->email,
            'token' => $request->token,
            'has_password' => !empty($request->password),
            'has_password_confirmation' => !empty($request->password_confirmation)
        ]);

        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // DEBUG: Log validation passed
        \Log::info('Password reset validation passed');

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                // DEBUG: Log user before update
                \Log::info('Updating password for user', ['user_id' => $user->id, 'email' => $user->email]);
                
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                // DEBUG: Log user after update
                \Log::info('Password updated successfully for user', ['user_id' => $user->id]);

                event(new PasswordReset($user));
            }
        );

        // DEBUG: Log password reset status
        \Log::info('Password reset status', ['status' => $status]);

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status == Password::PASSWORD_RESET) {
            \Log::info('Redirecting to login with success message');
            return redirect()->route('login')->with('status', __($status));
        } else {
            \Log::error('Password reset failed', ['status' => $status]);
            return back()->withInput($request->only('email'))
                    ->withErrors(['email' => __($status)]);
        }
    }
}
