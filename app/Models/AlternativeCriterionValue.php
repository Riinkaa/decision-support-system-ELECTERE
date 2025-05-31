<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlternativeCriterionValue extends Model
{
    use HasFactory;
    protected $table = 'alternative_criteria_values'; 
    // Kolom yang dapat diisi secara massal
    protected $fillable = ['alternative_id', 'criterion_id', 'value'];

    // Relasi ke alternatif
    public function alternative()
    {
        return $this->belongsTo(Alternative::class);
    }

    // Relasi ke kriteria
    public function criterion()
    {
        return $this->belongsTo(Criterion::class);
    }
}