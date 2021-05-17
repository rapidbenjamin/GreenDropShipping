<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\DashboardSteps;
use App\ImportList;
use App\Settings;
use App\Order;
use App\User;

class AjaxController extends Controller
{
    
    public function index(Request $parameters)
    {
        
    	if($parameters['action'] == 'add_check'){

    		if($row = DashboardSteps::find($parameters['id_user'])){
    			if($parameters['step'] == 1)$row->step1 = $parameters['value'];
    			if($parameters['step'] == 2)$row->step2 = $parameters['value'];
    			if($parameters['step'] == 3)$row->step3 = $parameters['value'];
    			if($parameters['step'] == 4)$row->step4 = $parameters['value'];
    			if($parameters['step'] == 5)$row->step5 = $parameters['value'];
    			if($parameters['step'] == 6)$row->step6 = $parameters['value'];
    			$row->save();
    		}else{
    			$row = new DashboardSteps;
    			$row->id = $parameters['id_user'];
    			$row->step1 = 0;
    			$row->step2 = 0;
    			$row->step3 = 0;
    			$row->step4 = 0;
    			$row->step5 = 0;
    			$row->step6 = 0;
    			$row->save();


    			$row = DashboardSteps::find($parameters['id_user']);
    			if($parameters['step'] == 1)$row->step1 = $parameters['value'];
    			if($parameters['step'] == 2)$row->step2 = $parameters['value'];
    			if($parameters['step'] == 3)$row->step3 = $parameters['value'];
    			if($parameters['step'] == 4)$row->step4 = $parameters['value'];
    			if($parameters['step'] == 5)$row->step5 = $parameters['value'];
    			if($parameters['step'] == 6)$row->step6 = $parameters['value'];
    			$row->save();
    		}

    		echo json_encode(1);
    	}

        if($parameters['action'] == 'add_import_list'){

            $row = new ImportList;
            $row->id_customer = Auth::user()->id;
            $row->id_product = $parameters['id_product'];
            $row->save();

            return json_encode($parameters['id_product']);
        }

        if($parameters['action'] == 'delete_import_list'){

			$this->authorize('plan_delete-product-import-list'); 
            $row = ImportList::find($parameters['id_import_list']);
            $row->delete();

            return json_encode(1);
        } 

        if($parameters['action'] == 'update_notes'){
            $row = Order::find($parameters['id_order']);
            $row->notes = $row->notes.$parameters['notes'];
            $row->save();

            return json_encode(1);
        }       
 

        if($parameters['action'] == 'save-user'){
            $row = new User;
            $row->name = $parameters['user'];
            $row->email = $parameters['email'];
            $row->password = $parameters['password'];
            $row->role = 'admin';
            $row->save();

            return json_encode(1);
        }  




	}
	
	public function saveSettings(Request $request){
		//dd($request->all());
		$request->validate([
			'set8' => 'required|integer',
		]);

		$settings = Settings::where('id_merchant',Auth::user()->id)->first();
		if($settings==null){
			$settings = new Settings();
			$settings->id_merchant = Auth::user()->id;
		}
		$settings->set1 = $request->set1=='true'?1:0;
		$settings->set2 = $request->set2=='true'?1:0;
		$settings->set3 = $request->set3=='true'?1:0;
		$settings->set4 = $request->set4=='true'?1:0;
		$settings->set5 = $request->set5=='true'?1:0;
		$settings->set6 = $request->set6=='true'?1:0;
		$settings->set7 = $request->set7=='true'?1:0;
		$settings->set8 = $request->set8;
		$settings->save();
		return response()->json(['res'=>'ok']);
	}
    
}