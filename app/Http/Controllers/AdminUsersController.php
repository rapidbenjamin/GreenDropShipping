<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;

class AdminUsersController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $users = User::where('role', 'admin');
        $total_count = $users->count();
        $users = $users->orderBy('users.id', 'asc')
            ->skip(0)->take(10)->get();


        $this->authorize('view-admin-merchants');
        return view('admin_users', array(
            'users' => $users,
            'total_count' => $total_count
        ));
    }
    
    public function profile()
    {
        $this->authorize('view-admin-merchants');
        $user = User::where('id', Auth::user()->id)->first();
        return view('admin_profile', [
            'user' => $user
        ]);
    }
    
    public function create_user()
    {
        $this->authorize('view-admin-merchants');
        return view('admin_add_user');
    }
}
