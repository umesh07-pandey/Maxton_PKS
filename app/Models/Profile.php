<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;
    protected $table="profile";

    protected $fillable =[
        'registration_id',
        'gender',
        'name',
        'dob',
        'phone',
        'profile_pic',
        'country'
    ];


    public function User(){
        return $this->belongsTo(Registration::class,'registration_id');
    }
}
