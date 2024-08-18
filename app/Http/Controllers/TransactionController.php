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

            $account->balance += $request->input('amount');
            $account->save();

            $request->user()->transactions()->create([
                'account_id' => $account->id,
                'type' => 'deposit',
                'amount' => $request->input('amount'),
            ]);

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

            $request->user()->transactions()->create([
                'account_id' => $account->id,
                'type' => 'withdraw',
                'amount' => $request->input('amount'),
            ]);

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
            DB::beginTransaction();

            $sourceAccount = $request->user()->accounts()->where("account_no", $request->input('account_no'))->firstOrFail();
            $targetAccount = Account::where('account_no', $request->input('target_account_no'))->firstOrFail();
            $amount = $request->input('amount');

            if ($sourceAccount->balance < $amount) {
                return $this->error(null, "Yetersiz bakiye.", 400);
            }

            if ($sourceAccount->currency !== $targetAccount->currency) {
                return $this->error(null, "Para birimleri uyumsuz.", 400);
            }

            if ($sourceAccount->user_id === $targetAccount->user_id) {
                return $this->error(null, "Kendinize transfer yapamazsınız.", 400);
            }

            $updatedRows = DB::update('UPDATE accounts SET balance = balance - ? WHERE id = ?', [$amount, $sourceAccount->id]);

            if ($updatedRows === 0) {
                return $this->error(null, "Kaynak hesap güncellenemedi.", 400);
            }

            $updatedRows = DB::update('UPDATE accounts SET balance = balance + ? WHERE id = ?', [$amount, $targetAccount->id]);

            if ($updatedRows === 0) {
                return $this->error(null, "Hedef hesap güncellenemedi.", 400);
            }

            $request->user()->transactions()->create([
                'account_id' => $sourceAccount->id,
                'type' => 'transfer',
                'amount' => $amount,
                'target_account_id' => $targetAccount->id,
            ]);

            DB::commit();

            return $this->success(
                new AccountResource($sourceAccount),
                "Para başarıyla transfer edildi.",
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->error(null, $e->getMessage(), 500);
        }
    }
}
