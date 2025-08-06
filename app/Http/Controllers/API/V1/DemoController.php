<?php 

namespace App\Http\Controllers\API\V1;

use App\Demo;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DemoController extends Controller{
	/**
	*
	*
	* @return \Illuminate\Http\Response
	*/
	public function index(Request $request){

		$data_col=[];
	
		$data_col['total_tax']="Test";
		Transaction::create(['user_id'=>1,
            'status'=>'reversed',
            'amount'=>100,
            'opening_balance'=>12500,
            'closing_balance'=>10000]);
		
	$response=[
		'message'=>'Success'
		];

	return response()->json($response);

	}	
}

?>