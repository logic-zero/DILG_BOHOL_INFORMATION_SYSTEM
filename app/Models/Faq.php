<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    /** @use HasFactory<\Database\Factories\FaqFactory> */
    use HasFactory;

    protected $primaryKey = 'id';
    protected $fillable =
        [
            'outcome_area',
            'program',
            'questions',
            'answers',

        ];

    protected $table = "faqs";


    protected $casts = [
        'created_at' => 'datetime',

    ];


    // public function scopeSearch($query, $terms)
    // {
    //     collect(explode(" ", $terms))->filter()->each(function ($term) use ($query) {
    //         $term = '%' . $term . '%';

    //         $query->where('questions', 'like', $term)
    //             ->orWhere('program_id', 'like', $term)
    //             ->orWhere('answers', 'like', $term)
    //             ->orWhere('outcome_area', 'like', $term);
    //     });
    // }

    // public function getActivitylogOptions(): LogOptions
    // {
    //     return LogOptions::defaults()
    //         ->logOnly(['questions', 'answers', 'outcome_area'])
    //         ->setDescriptionForEvent(fn(string $eventName) => "A Frequently Asked Question (FAQ) has been {$eventName}")
    //         ->logOnlyDirty();
    // }
}
