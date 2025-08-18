<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Bonus;
use App\Balance;
use App\Models\Transaction;

class UserBonusController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // Or your admin middleware
    }

    // START: METHODS FOR MANAGING BONUS TYPES

    /**
     * Display a list of all bonus types.
     */
    public function index()
    {
        $bonuses = Bonus::orderBy('name')->get();
        return view('admin-views.bonuses.index', compact('bonuses'));
    }

    /**
     * Show the form for creating a new bonus type.
     */
    public function create()
    {
        return view('admin-views.bonuses.create');
    }

    /**
     * Store a newly created bonus type in the database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:bonuses|max:255',
            'amount' => 'required|numeric|min:0',
        ]);

        Bonus::create([
            'name' => $request->name,
            'amount' => $request->amount,
        ]);

        return redirect()->route('admin.bonuses.index')->with('success', 'New bonus type created successfully.');
    }
    
    /**
     * Display the specified bonus type.
     */
    public function show(Bonus $bonus)
    {
        return view('admin-views.bonuses.show', compact('bonus'));
    }

    /**
     * Show the form for editing the specified bonus type.
     */
    public function edit(Bonus $bonus)
    {
        return view('admin-views.bonuses.edit', compact('bonus'));
    }

    /**
     * Update the specified bonus type in the database.
     */
    public function update(Request $request, Bonus $bonus)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:bonuses,name,' . $bonus->id,
            'amount' => 'required|numeric|min:0',
        ]);

        $bonus->update([
            'name' => $request->name,
            'amount' => $request->amount,
        ]);

        return redirect()->route('admin.bonuses.index')->with('success', 'Bonus type updated successfully.');
    }


    /**
     * Delete a bonus type from the database.
     */
    public function destroy(Bonus $bonus)
    {
        try {
            $bonus->delete();
            return redirect()->route('admin.bonuses.index')->with('success', 'Bonus type deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.bonuses.index')->with('error', 'Could not delete bonus type. It might be in use.');
        }
    }

    // END: METHODS FOR MANAGING BONUS TYPES


    // START: METHOD FOR AWARDING A BONUS
    public function awardBonus(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:user,id',
            'bonus_id' => 'required|exists:bonuses,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $bonus = Bonus::findOrFail($request->bonus_id);

        DB::beginTransaction();
        try {
            $userBalance = Balance::firstOrCreate(['user_id' => $user->id], ['balance' => 0.00]);
            $opening_balance = $userBalance->balance;
            $closing_balance = $opening_balance + $bonus->amount;

            Transaction::create([
                'user_id' => $user->id,
                'status' => 'bonus',
                'amount' => $bonus->amount,
                'opening_balance' => $opening_balance,
                'closing_balance' => $closing_balance,
                'remarks' => $bonus->name,
                'transaction_type' => 'deposit',
            ]);

            $userBalance->balance = $closing_balance;
            $userBalance->save();

            DB::commit();
            return redirect()->back()->with('success', 'Bonus awarded successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Bonus award failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to award bonus. Please try again.');
        }
    }
    // END: METHOD FOR AWARDING A BONUS
}