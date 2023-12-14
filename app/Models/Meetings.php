<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meetings extends Model
{
    use HasFactory;
    protected $fillable = ['project_id' , 'sprint_id' , 'type' ,
                            'description' , 'date' , 'start_time' ,
                            'end_time'];
}
