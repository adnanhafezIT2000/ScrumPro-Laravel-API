<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcceptanceCritiria extends Model
{
    use HasFactory;
    protected $fillable=['story_id','description'];
}
