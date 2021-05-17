<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        
        $merchants_list = User::select('users.*')
        ->where('role','admin')
        ->orderBy('users.id','asc')
        ->paginate(50);


        $this->authorize('view-admin-merchants'); 
        return view('admin_users',Array(
            'merchants_list' => $merchants_list
        ));
    }
    

}