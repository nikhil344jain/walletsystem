<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function me()
    {
        $user = $this->guard()->user();
        $balanceDetail = Wallet::where('user_id', $user->id)->orderBy('id', 'desc')->first();
        $balance = 0;
        if ($balanceDetail) {
            $balance = $balanceDetail->balance;
        }
        return response()->json(['user' => $user, 'balance' => $balance], 200);
    }

    public function addMoney(Request $request)
    {
        try {
            $user = $this->guard()->user();
            $validate = Validator::make($request->all(), [
                'amount' => ['required', 'numeric', 'between:3,100'],
            ]);
            if($validate->fails()){
                return response()->json(['error'=>$validate->errors()->toJson()], 400);
            }
            $balanceDetail = Wallet::where('user_id', $user->id)->orderBy('id', 'desc')->first();
            $balance = 0;
            if ($balanceDetail) {
                $balance = $request['amount'] + $balanceDetail->balance;
            } else {
                $balance = $request['amount'];
            }
            $ab = Wallet::create([
                'user_id' => $user->id,
                'amount' => $request['amount'],
                'type' => 'add',
                'balance' => $balance,
            ]);
            return response()->json(['message' => 'Money added to wallet successfully'], 200);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['message' => 'An error occurred'], 500);
        }
    }

    public function buyCookie(Request $request)
    {
        try {
            $user = $this->guard()->user();
            $balanceDetail = Wallet::where('user_id', $user->id)->orderBy('id', 'desc')->first();
            $balance = 0;
            $amount = 0;
            if ($balanceDetail) {
                $balance = $balanceDetail->balance;
                $amount = $balanceDetail->amount;
            }
            if ($balance < 1) {
                return response()->json(['message' => 'Insufficient balance to buy a cookie'], 400);
            }
            $balance = $balance - 1;
            DB::transaction(function () use ($user, $balance) {
                Wallet::create([
                    'user_id' => $user->id,
                    'amount' => 1,
                    'type' => 'deduct',
                    'balance' => $balance,
                ]);
            });

            return response()->json(['message' => 'Cookie purchased successfully']);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['message' => 'An error occurred'], 500);
        }
    }

    public function guard()
    {
        return Auth::guard('api');
    }
}
