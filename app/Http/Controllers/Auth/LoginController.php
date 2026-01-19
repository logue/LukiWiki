<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Handle Social login request.
     */
    public function socialLogin(string $social): RedirectResponse
    {
        return Socialite::driver($social)->redirect();
    }

    /**
     * Obtain the user information from Social Logged in.
     */
    public function handleProviderCallback(string $social): mixed
    {
        $userSocial = Socialite::driver($social)->user();

        $user = User::where(['email' => $userSocial->getEmail()])->first();

        if ($user) {
            Auth::login($user);

            return redirect()->action('WikiController@index');
        }

        return view('auth.register', ['name' => $userSocial->getName(), 'email' => $userSocial->getEmail()]);
    }
}
