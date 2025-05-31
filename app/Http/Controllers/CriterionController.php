<?php

namespace App\Http\Controllers;

use App\Models\Criterion;
use App\Models\DecisionCase;
use Illuminate\Http\Request;

class CriterionController extends Controller
{
    /**
     * Menampilkan form untuk menambah kriteria baru.
     */
    public function create(DecisionCase $decisionCase)
    {
        return view('criteria.create', compact('decisionCase'));
    }

    /**
     * Menyimpan kriteria baru ke database.
     */
    public function store(Request $request, DecisionCase $decisionCase)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:benefit,cost',
            'weight' => 'required|numeric|min:0', // Bobot bisa 0 atau lebih
        ]);

        $decisionCase->criteria()->create($request->all());

        return redirect()->route('decision_cases.show', $decisionCase->id)
                         ->with('success', 'Kriteria berhasil ditambahkan!');
    }

    /**
     * Menampilkan form untuk mengedit kriteria.
     */
    public function edit(DecisionCase $decisionCase, Criterion $criterion)
    {
        // Pastikan kriteria yang diedit adalah bagian dari kasus keputusan ini
        if ($criterion->decision_case_id !== $decisionCase->id) {
            abort(404); // Atau redirect dengan pesan error
        }
        return view('criteria.edit', compact('decisionCase', 'criterion'));
    }

    /**
     * Memperbarui kriteria di database.
     */
    public function update(Request $request, DecisionCase $decisionCase, Criterion $criterion)
    {
        // Pastikan kriteria yang diupdate adalah bagian dari kasus keputusan ini
        if ($criterion->decision_case_id !== $decisionCase->id) {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:benefit,cost',
            'weight' => 'required|numeric|min:0',
        ]);

        $criterion->update($request->all());

        return redirect()->route('decision_cases.show', $decisionCase->id)
                         ->with('success', 'Kriteria berhasil diperbarui!');
    }

    /**
     * Menghapus kriteria dari database.
     */
    public function destroy(DecisionCase $decisionCase, Criterion $criterion)
    {
        // Pastikan kriteria yang dihapus adalah bagian dari kasus keputusan ini
        if ($criterion->decision_case_id !== $decisionCase->id) {
            abort(404);
        }
        $criterion->delete();

        return redirect()->route('decision_cases.show', $decisionCase->id)
                         ->with('success', 'Kriteria berhasil dihapus!');
    }
}