<?php
declare(strict_types=1);


namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Repositories\TokenRepository;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly TokenRepository $tokens,
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $patient = $this->auth->attempt($request->login, $request->password);

        if (!$patient) {
            return response()->json(['message' => 'Błędne dane logowania.'], 401);
        }

        return response()->json(['token' => $this->tokens->generateFor($patient)]);
    }
}
