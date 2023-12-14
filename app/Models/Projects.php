<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Projects extends Model
{
    use HasFactory;

    protected $fillable=[
        'owner_id','team_id', 'name' ,'actual_termination_date',
        'planned_termination_date','budget','description'
];}
