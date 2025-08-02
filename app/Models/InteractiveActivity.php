<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InteractiveActivity extends Model
{
    //
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'image',
        'type',
    ];
}
