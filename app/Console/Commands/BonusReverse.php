<?php

namespace App\Console\Commands;

use App\Balance;
use App\Models\Transaction;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class BonusReverse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bonus:reverse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bonus Reverse';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $days_ago = date('Y-m-d', strtotime('-3 days'));
        $query = User::whereDate('created_at',$days_ago)->get();

        foreach ($query as $user) {

            $transaction = Transaction::where('user_id', $user->id)->where('remarks', 'like', '%Place Bet%')->sum('transaction.amount');
            if($transaction < Config::get('constants.default_bonus')) {


                 $avail_balance = Balance::where('user_id', $user->id)->first()->balance;

                $bal = Config::get('constants.default_bonus') - $transaction;


                $balance_amt = $avail_balance - $bal;
                $final_balance = DB::table('balance')
                    ->where('user_id', $user->id)
                    ->update(['balance' => $balance_amt]);


                //Reverse from transaction table

                Transaction::create(
                    [
                        'user_id'=>$user->id,
                        'status'=>'Reversed',
                        'amount'=>$bal,
                        'opening_balance'=>$avail_balance,
                        'closing_balance'=>$balance_amt,
                        'remarks'=>'Registration Bonus Reveresed'
                    ]
                );

            }

        }
    }
}
