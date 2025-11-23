<?php

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Requests\RequestCodeRequest;
use App\Modules\Auth\Requests\VerifyCodeRequest;
use App\Modules\Auth\Services\AuthCodeService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        private AuthCodeService $authCodeService
    ) {
    }

    /**
     * Request a login code.
     */
    public function requestCode(RequestCodeRequest $request): JsonResponse
    {
        $email = $request->validated()['email'];

        $user = $this->authCodeService->getOrCreateUser($email);
        $this->authCodeService->createLoginCode($user);

        return response()->json([
            'message' => 'Código enviado para seu e-mail',
        ]);
    }

    /**
     * Verify code and authenticate user.
     */
    public function verifyCode(VerifyCodeRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = $this->authCodeService->verifyCode($data['email'], $data['code']);

        if (!$user) {
            return response()->json([
                'message' => 'Código inválido ou expirado',
            ], 422);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $token,
        ]);
    }
}
