<?php

namespace App\Http\Controllers;

use App\Balance;
use Illuminate\Support\Facades\Auth;

class BetController extends Controller
{
    public function bet() {
        $balance = Balance::where('user_id', Auth::user()->id)->first();
        $data = ['balance' => $balance->balance];
        return view('menu-pages.bet', $data);
    }

    public function faqs() {
        return view('faqs');
    }

    public function responsibleGambling() {
        return view('responsible-gambling');
    }

    public function termConditions() {
        return view('terms-conditions');
    }

    public function privacyPolicy() {
        return view('privacy-policy');
    }

    public function bonusTerms() {
        return view('bonus-terms');
    }
}
