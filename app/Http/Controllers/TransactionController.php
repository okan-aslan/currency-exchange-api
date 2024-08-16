<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountTransactionRequest;
use App\Http\Resources\AccountResource;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    use ApiResponses;

    public function deposit(AccountTransactionRequest $request): JsonResponse
    {
        try {
            $account = $request->user()->accounts()->where("account_no", $request->input('account_no'))->firstOrFail();

            $account->balance += $request->input('amount');
            $account->save();

            return $this->success(
                ["account" => new AccountResource($account)],
                "Para başarıyla yatırıldı.",
                200
            );
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    public function withdraw(AccountTransactionRequest $request): JsonResponse
    {
        try {
            $account = $request->user()->accounts()->where("account_no", $request->input('account_no'))->firstOrFail();

            if ($account->balance < $request->input('amount')) {
                return response()->json(['message' => 'Yetersiz bakiye.'], 400);
            }

            $account->balance -= $request->input('amount');
            $account->save();

            return $this->success(
                ["account" => new AccountResource($account)],
                "Para başarıyla çekildi.",
                200
            );
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }
}
