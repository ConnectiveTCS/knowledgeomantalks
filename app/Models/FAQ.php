<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FAQ extends Model
{
    //
    protected $table = 'f_a_q_s';
    protected $fillable = ['question', 'answer', 'is_active'];
    protected $casts = [
        'is_active' => 'boolean',
    ];
    public $timestamps = true;
    protected $primaryKey = 'id';
    
}
