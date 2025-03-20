<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PDMUs extends Model
{
    protected $primaryKey = 'id';
    protected $fillable =
    [
        'profile_img',
        'fname',
        'mid_initial',
        'lname',
        'position',

    ];

    protected $table = "pdmus";


    protected $casts = [
        'created_at' => 'datetime',

    ];
}
