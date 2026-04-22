<?php
declare(strict_types=1);


namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Patient extends Authenticatable implements JWTSubject
{
    protected $fillable = ['id', 'name', 'surname', 'is_male', 'birth_date'];

    protected $hidden = ['remember_token'];

    public $incrementing = false;

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function getAuthPassword(): string
    {
        return $this->birth_date;
    }
}
