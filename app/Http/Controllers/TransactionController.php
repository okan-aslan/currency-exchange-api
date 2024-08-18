<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountTransactionRequest;
use App\Http\Requests\TransferTransactionRequest;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use App\Models\Transaction;
use App\Traits\ApiResponses;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    use ApiResponses;

    public function deposit(AccountTransactionRequest $request): JsonResponse
    {
        try {
            $account = $request->user()->accounts()->where("account_no", $request->input('account_no'))->firstOrFail();

            $account->deposit($request->input('amount'));

            Transaction::recordTransaction($request->user()->id, $account->id, 'deposit', $request->input('amount'));

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

            $account->withdraw($request->input('amount'));

            Transaction::recordTransaction($request->user()->id, $account->id, 'withdraw', $request->input('amount'));

            return $this->success(
                ["account" => new AccountResource($account)],
                "Para başarıyla çekildi.",
                200
            );
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    public function transfer(TransferTransactionRequest $request): JsonResponse
    {
        try {
            $sourceAccount = $request->user()->accounts()->where("account_no", $request->input('account_no'))->firstOrFail();
            $targetAccount = Account::where('account_no', $request->input('target_account_no'))->firstOrFail();

            $sourceAccount->transferTo($targetAccount, $request->input('amount'));

            Transaction::recordTransaction(
                $request->user()->id,
                $sourceAccount->id,
                'transfer',
                $request->input('amount'),
                $targetAccount->id
            );

            return $this->success(
                new AccountResource($sourceAccount),
                "Para başarıyla transfer edildi.",
                201
            );
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }
}
