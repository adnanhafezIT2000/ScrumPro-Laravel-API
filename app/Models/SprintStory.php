<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SprintStory extends Model
{
    use HasFactory;

    protected $fillable = ['id' , 'sprint_id' , 'story_id' , 'story_rank'];
}
