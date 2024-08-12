<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "accountNo" => $this->account_no,
            "currency" => $this->currency,
            "balance" => $this->balance,
            "createdAt" => $this->created_at->format("Y-m-d H:i:s"),
            "user" => new UserResource($this->whenLoaded('user')),
        ];
    }
}
