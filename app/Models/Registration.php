<?php

namespace App\Models;

// use App\Http\Middleware\Authenticate;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Registration extends Authenticatable implements JWTSubject
{
    use HasFactory;

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'id'=>$this->id,
            'email'=>$this->email,
        ];
    }
    protected $table = "registration";

    protected $fillable = [

        'username',
        'email',
        'password'
        
    ];


    public function profile(){
        return $this->hasone(profile::class,'registration_id');
    }
    

}
