<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    /** @use HasFactory<\Database\Factories\MunicipalityFactory> */
    use HasFactory;

    protected $primaryKey = 'id';
    protected $fillable =
    [
        'municipality',
        'gmap_url',
        'num_of_brgys',
        'barangays',

    ];

    protected $table = "municipalities";


    protected $casts = [
        'created_at' => 'datetime',

    ];

    public function lgu(){
        return $this->hasMany('App\Models\Lgu');
    }

    public function field_officer(){
        return $this->hasMany('App\Models\Field_Officer');
    }
}
