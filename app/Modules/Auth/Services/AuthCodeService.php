<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Repositories\LoginCodeRepository;
use App\Modules\Users\Models\Users;
use Illuminate\Support\Facades\Mail;
use App\Mail\LoginCodeMail;

class AuthCodeService
{
    public function __construct(
        private LoginCodeRepository $loginCodeRepository
    ) {
    }

    /**
     * Generate a 6-digit numeric code.
     */
    public function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create or get user by email.
     */
    public function getOrCreateUser(string $email): Users
    {
        return Users::firstOrCreate(
            ['email' => $email],
            ['email' => $email]
        );
    }

    /**
     * Create a login code for the user.
     */
    public function createLoginCode(Users $user): \App\Modules\Auth\Models\LoginCode
    {
        $code = $this->generateCode();
        $expiresAt = now()->addMinutes(10);

        $loginCode = $this->loginCodeRepository->create($user, $code, $expiresAt);

        // Send email with the code
        Mail::to($user->email)->send(new LoginCodeMail($code));

        return $loginCode;
    }

    /**
     * Verify and use a login code.
     */
    public function verifyCode(string $email, string $code): ?Users
    {
        $user = Users::where('email', $email)->first();

        if (!$user) {
            return null;
        }

        $loginCode = $this->loginCodeRepository->findLatestUnusedByUserAndCode($user, $code);

        if (!$loginCode || !$loginCode->isValid()) {
            return null;
        }

        // Mark code as used
        $this->loginCodeRepository->markAsUsed($loginCode);

        return $user;
    }
}
