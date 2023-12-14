<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sprints extends Model
{
    use HasFactory;
    protected $fillable=['project_id','name','strat_date','end_date','goal','status'];
}
