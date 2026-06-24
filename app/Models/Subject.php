<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = ['nama', 'kode'];

    public function questionBanks(): HasMany
    {
        return $this->hasMany(QuestionBank::class);
    }
}
