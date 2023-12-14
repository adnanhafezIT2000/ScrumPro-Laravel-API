<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    use HasFactory;


    protected $fillable=[
            'role_id','category_id', 'team_id' , 'full_name',
            'email','password','phone','address',
            'avatar','birthday','gender',
            'hourly_rate', 'rank' , 'status'
    ];
}
