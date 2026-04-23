<?php
declare(strict_types=1);


namespace App\Repositories;

use Illuminate\Contracts\Auth\Authenticatable;
use Tymon\JWTAuth\JWTAuth;

class TokenRepository
{
    public function __construct(private readonly JWTAuth $jwt) {}

    public function generateFor(Authenticatable $user): string
    {
        return $this->jwt->fromUser($user);
    }
}
