<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavingsBlockTranslation extends Model
{
    protected $fillable = [
        'savings_block_id',
        'locale',
        'code',
        'value'
    ];

    public $timestamps = true;

    public function savingsBlock()
    {
        return $this->belongsTo(SavingsBlock::class);
    }
}

