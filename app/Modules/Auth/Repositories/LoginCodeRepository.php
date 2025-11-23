<?php

namespace App\Modules\Auth\Repositories;

use App\Modules\Auth\Models\LoginCode;
use App\Modules\Users\Models\Users;

class LoginCodeRepository
{
    public function __construct(
        private LoginCode $model
    ) {
    }

    /**
     * Create a new login code.
     */
    public function create(Users $user, string $code, \DateTime $expiresAt): LoginCode
    {
        return $this->model->create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Find the latest unused login code for a user.
     */
    public function findLatestUnusedByUserAndCode(Users $user, string $code): ?LoginCode
    {
        return $this->model->where('user_id', $user->id)
            ->where('code', $code)
            ->whereNull('used_at')
            ->latest()
            ->first();
    }

    /**
     * Mark a login code as used.
     */
    public function markAsUsed(LoginCode $loginCode): bool
    {
        return $loginCode->update(['used_at' => now()]);
    }
}
