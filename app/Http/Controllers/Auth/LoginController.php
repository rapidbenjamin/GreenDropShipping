<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

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
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Handle an authentication attempt.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return Response
     */
    public function login(Request $request)
    {
        if (\Auth::attempt(['email' => $request->email, 'password' => $request->password, 'role' => 'admin'])) {
            // Authentication passed...
            return redirect('admin/dashboard');
        } else if (\Auth::attempt(['email' => $request->email, 'password' => $request->password, 'role' => 'merchant'])) {
            // Authentication passed...

            return redirect('/');
        } else {
            return redirect('/login')->withInput(Input::except('password'))->withErrors(['email' => 'The credentials do not match our records']);
        }
    }
}
