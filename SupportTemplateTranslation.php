<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTemplateTranslation extends Model
{
    protected $fillable = [
        'support_template_id',
        'locale',
        'code',
        'value',
    ];
}

