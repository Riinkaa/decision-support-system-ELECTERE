<?php

namespace App\Http\Controllers;

use App\Models\Alternative;
use App\Models\DecisionCase;
use App\Models\AlternativeCriterionValue;
use Illuminate\Http\Request;


class AlternativeController extends Controller
{
    
    /**
     * Menampilkan form untuk menambah alternatif baru.
     */
    public function create(DecisionCase $decisionCase)
    {
        
        // Ambil semua kriteria yang terkait dengan kasus keputusan ini
        $criteria = $decisionCase->criteria;
        return view('alternatives.create', compact('decisionCase', 'criteria'));
    }

    /**
     * Menyimpan alternatif baru beserta nilai preferensinya ke database.
     */
    public function store(Request $request, DecisionCase $decisionCase)
    {
        // Validasi nama alternatif
        $request->validate([
            'name' => 'required|string|max:255',
            // Validasi nilai kriteria akan dilakukan secara dinamis
        ]);

        // Buat alternatif baru
        $alternative = $decisionCase->alternatives()->create([
            'name' => $request->name,
        ]);

        // Simpan nilai preferensi untuk setiap kriteria
        foreach ($decisionCase->criteria as $criterion) {
            $request->validate([
                'criterion_values.' . $criterion->id => 'required|numeric',
            ], [
                'criterion_values.*.required' => 'Nilai untuk kriteria ' . $criterion->name . ' harus diisi.',
                'criterion_values.*.numeric' => 'Nilai untuk kriteria ' . $criterion->name . ' harus berupa angka.',
            ]);

            AlternativeCriterionValue::create([
                'alternative_id' => $alternative->id,
                'criterion_id' => $criterion->id,
                'value' => $request->input('criterion_values.' . $criterion->id),
            ]);
        }

        return redirect()->route('decision_cases.show', $decisionCase->id)
                         ->with('success', 'Alternatif berhasil ditambahkan!');
    }

    /**
     * Menampilkan form untuk mengedit alternatif.
     */
    public function edit(DecisionCase $decisionCase, Alternative $alternative)
    {
        // Pastikan alternatif yang diedit adalah bagian dari kasus keputusan ini
        if ($alternative->decision_case_id !== $decisionCase->id) {
            abort(404);
        }

        $criteria = $decisionCase->criteria;
        // Ambil nilai preferensi yang sudah ada untuk alternatif ini
        $currentValues = $alternative->alternativeCriteriaValues->pluck('value', 'criterion_id')->toArray();

        return view('alternatives.edit', compact('decisionCase', 'alternative', 'criteria', 'currentValues'));
    }

    /**
     * Memperbarui alternatif beserta nilai preferensinya di database.
     */
    public function update(Request $request, DecisionCase $decisionCase, Alternative $alternative)
    {
        // Pastikan alternatif yang diupdate adalah bagian dari kasus keputusan ini
        if ($alternative->decision_case_id !== $decisionCase->id) {
            abort(404);
        }

        // Validasi nama alternatif
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Update nama alternatif
        $alternative->update(['name' => $request->name]);

        // Perbarui atau buat nilai preferensi untuk setiap kriteria
        foreach ($decisionCase->criteria as $criterion) {
            $request->validate([
                'criterion_values.' . $criterion->id => 'required|numeric',
            ], [
                'criterion_values.*.required' => 'Nilai untuk kriteria ' . $criterion->name . ' harus diisi.',
                'criterion_values.*.numeric' => 'Nilai untuk kriteria ' . $criterion->name . ' harus berupa angka.',
            ]);

            // Cek apakah nilai preferensi sudah ada, jika ya update, jika tidak buat baru
            AlternativeCriterionValue::updateOrCreate(
                [
                    'alternative_id' => $alternative->id,
                    'criterion_id' => $criterion->id,
                ],
                [
                    'value' => $request->input('criterion_values.' . $criterion->id),
                ]
            );
        }

        return redirect()->route('decision_cases.show', $decisionCase->id)
                         ->with('success', 'Alternatif berhasil diperbarui!');
    }

    /**
     * Menghapus alternatif dari database.
     */
    public function destroy(DecisionCase $decisionCase, Alternative $alternative)
    {
        // Pastikan alternatif yang dihapus adalah bagian dari kasus keputusan ini
        if ($alternative->decision_case_id !== $decisionCase->id) {
            abort(404);
        }
        $alternative->delete(); // Ini akan otomatis menghapus AlternativeCriterionValue terkait karena onDelete('cascade') di migrasi

        return redirect()->route('decision_cases.show', $decisionCase->id)
                         ->with('success', 'Alternatif berhasil dihapus!');
    }
}