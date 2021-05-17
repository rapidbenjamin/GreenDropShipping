<?php

namespace App\Http\Controllers;

use App\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
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
        $this->authorize('view-merchant-settings'); 
        $settings = Settings::where('id_merchant',Auth::user()->id)->first();
        if($settings==null){
            $settings = new Settings();
            $settings->set1 = 0;
            $settings->set2 = 0;
            $settings->set3 = 0;
            $settings->set4 = 0;
            $settings->set5 = 0;
            $settings->set6 = 0;
            $settings->set7 = 1;
            $settings->set8 = '';
        }
        return view('settings',['settings'=>$settings]);
    }
    
    
}