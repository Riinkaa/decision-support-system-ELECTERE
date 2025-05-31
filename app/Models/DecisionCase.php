<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DecisionCase extends Model
{
    use HasFactory;

    // Kolom yang dapat diisi secara massal
    protected $fillable = ['name', 'description'];

    // Relasi ke kriteria
    public function criteria()
    {
        return $this->hasMany(Criterion::class);
    }

    // Relasi ke alternatif
    public function alternatives()
    {
        return $this->hasMany(Alternative::class);
    }
}