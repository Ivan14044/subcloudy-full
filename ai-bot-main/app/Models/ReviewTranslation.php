<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewTranslation extends Model
{
    protected $fillable = [
        'review_id',
        'locale',
        'code',
        'value'
    ];

    public $timestamps = true;
}

