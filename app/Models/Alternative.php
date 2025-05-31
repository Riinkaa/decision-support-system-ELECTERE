<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alternative extends Model
{
    use HasFactory;

    // Kolom yang dapat diisi secara massal
    protected $fillable = ['decision_case_id', 'name'];

    // Relasi ke kasus keputusan
    public function decisionCase()
    {
        return $this->belongsTo(DecisionCase::class);
    }

    // Relasi ke nilai preferensi alternatif-kriteria
    public function alternativeCriteriaValues()
    {
        return $this->hasMany(AlternativeCriterionValue::class);
    }
}