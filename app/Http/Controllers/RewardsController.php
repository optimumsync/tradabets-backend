<?php


namespace App\Http\Controllers;


use App\Balance;
use App\Models\Transaction;
use Illuminate\Http\Request;

class RewardsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $user=auth()->user();


        $view_data=[];

        $filter_arr = [
            'date_from' => date("Y-m-d", strtotime("last week saturday")),
            'date_to' => date("Y-m-d", strtotime("this week friday")),
        ];
        if($request->form){
            $transaction=Transaction::where('user_id',$user->id)
                ->whereBetween('created_at',array($request->form['date_from'],$request->form['date_to']))->get()->all();
        }
        else
        {
            $transaction=Transaction::where('user_id',$user->id)
                ->whereBetween('created_at',array($filter_arr['date_from'],$filter_arr['date_to']))->get()->all();
        }
        $filter_arr = ($request->form) ? array_merge($filter_arr, $request->form) : $filter_arr;

        $balance= Balance::where('user_id',$user->id)->get()->all();

        $view_data=['transaction'=>$transaction,'user'=>$user,'filter_arr'=>$filter_arr,'balance'=>$balance];

        return view('rewards.rewards-index',$view_data);
    }

}
