<?php

namespace App\Http\Controllers;

use App\Models\DecisionCase;
use Illuminate\Http\Request;
use App\Services\ElectreService;

class DecisionCaseController extends Controller
{
    /**
     * Menampilkan daftar semua kasus keputusan.
     */
    public function index()
    {
        // Mengambil semua kasus keputusan dari database
        $decisionCases = DecisionCase::all();

        // Mengirim data kasus keputusan ke view
        return view('decision_cases.index', compact('decisionCases'));
    }

    /**
     * Menampilkan form untuk membuat kasus keputusan baru.
     */
    public function create()
    {
        return view('decision_cases.create');
    }

    /**
     * Menyimpan kasus keputusan baru ke database.
     */
    public function store(Request $request)
    {
        // Validasi input dari form
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Membuat kasus keputusan baru di database
        DecisionCase::create($request->all());

        // Redirect kembali ke halaman daftar kasus dengan pesan sukses
        return redirect()->route('decision_cases.index')->with('success', 'Kasus keputusan berhasil ditambahkan!');
    }

    /**
     * Menampilkan detail atau dashboard untuk kasus keputusan tertentu.
     */
    public function show(DecisionCase $decisionCase)
    {
        // Kita akan mengembangkan ini nanti untuk menampilkan kriteria, alternatif, dan hasil perhitungan.
        return view('decision_cases.show', compact('decisionCase'));
    }

    /**
     * Menampilkan form untuk mengedit kasus keputusan yang sudah ada.
     */
    public function edit(DecisionCase $decisionCase)
    {
        return view('decision_cases.edit', compact('decisionCase'));
    }

    /**
     * Memperbarui kasus keputusan di database.
     */
    public function update(Request $request, DecisionCase $decisionCase)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $decisionCase->update($request->all());

        return redirect()->route('decision_cases.index')->with('success', 'Kasus keputusan berhasil diperbarui!');
    }

    /**
     * Menghapus kasus keputusan dari database.
     */
    public function destroy(DecisionCase $decisionCase)
    {
        $decisionCase->delete();

        return redirect()->route('decision_cases.index')->with('success', 'Kasus keputusan berhasil dihapus!');
    }
    public function calculateElectre(Request $request, DecisionCase $decisionCase)
    {
       

        // Inisialisasi service ELECTRE
        $electreService = new ElectreService($decisionCase);
       
        $results = $electreService->calculateElectre();
        // Jika ada error (misal: kriteria/alternatif kurang)
        if (isset($results['error'])) {
            return redirect()->route('decision_cases.show', $decisionCase->id)
                             ->with('error', $results['error']);
        }

        // Simpan hasil perhitungan ke session agar bisa ditampilkan di view show
        session()->flash('electre_results', $results);

        // Redirect kembali ke halaman show untuk menampilkan hasil
        return redirect()->route('decision_cases.show', $decisionCase->id)
                         ->with('success', 'Perhitungan ELECTRE berhasil dilakukan!');
    }
}