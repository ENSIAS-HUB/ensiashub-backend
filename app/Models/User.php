<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    protected $fillable = [
        'emailInstitutionnel',
        'nom',
        'prenom',
        'password',
        'photoProfil',
        'bio',
        'profileActif',
        'roles',
    ];

    
    protected $hidden = [
        'password',
        'remember_token',
    ];

    
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'profileActif' => 'boolean',
            'roles' => 'array', 
        ];
    }


    
    public function modifierProfile(array $data)
    {
        return $this->update($data);
    }
}