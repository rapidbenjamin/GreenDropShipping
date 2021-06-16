<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class AdminMerchantsController extends Controller
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
            ->where('role', 'merchant')
            ->orderBy('users.id', 'asc');
        $total_count = $merchants_list->count();
        $merchants_list = $merchants_list->take(10)->get();

        $this->authorize('view-admin-merchants');
        return view('admin_merchants', array(
            'merchants_list' => $merchants_list,
            'total_count' => $total_count
        ));
    }

    public function show(User $merchant)
    {
        $this->authorize('view-admin-merchants');
        return view('admin_merchants_detail', array(
            'merchant' => $merchant
        ));
    }

    public function exportCSV()
    {

        $now = gmdate("D, d M Y H:i:s");
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");

        // force download
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");

        // disposition / encoding on response body
        header("Content-Disposition: attachment;filename=merchants.csv");
        header("Content-Transfer-Encoding: binary");

        $merchants = User::select('users.name', 'users.email', 'users.shopify_url', 'users.role', 'users.plan', 'users.active', 'users.created_at')->where('role', 'merchant')->get()->toArray();
        ob_start();
        $df = fopen("php://output", 'w');

        fputcsv($df, array_keys(reset($merchants)));
        foreach ($merchants as $merchant) {
            fwrite($df, implode(",", $merchant) . "\n");
        }
        fclose($df);
        echo ob_get_clean();
        die();
    }
}
