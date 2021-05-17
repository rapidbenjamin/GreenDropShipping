<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


class HelpController extends Controller
{
    
    public function index()
    {
        $this->authorize('view-merchant-help'); 
        return view('help');
    }
    
}