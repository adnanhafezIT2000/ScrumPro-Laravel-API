<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tasks extends Model
{
    use HasFactory;
    protected $fillable = ['user_id' , 'story_id' , 'category_id' , 'skill_id' ,
    'title' , 'description' , 'level' , 'status'];
}
