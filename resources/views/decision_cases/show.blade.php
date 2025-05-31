@extends('layouts.app')

@section('content')
<div class="bg-white p-6 rounded-lg shadow-md">
    <a href="{{ route('decision_cases.index') }}" class="text-blue-500 hover:text-blue-700 mb-4 inline-block">&larr; Kembali ke Daftar Kasus</a>

    <h1 class="text-3xl font-bold text-gray-800 mb-4">Detail Kasus Keputusan: {{ $decisionCase->name }}</h1>
    <p class="text-gray-600 mb-6">{{ $decisionCase->description }}</p>

    <hr class="my-6">

    {{-- Bagian Manajemen Kriteria (sudah ada) --}}
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-semibold text-gray-700">Manajemen Kriteria</h2>
        <a href="{{ route('decision_cases.criteria.create', $decisionCase->id) }}" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300">
            Tambah Kriteria
        </a>
    </div>

    @if ($decisionCase->criteria->isEmpty())
        <p class="text-gray-600 mb-6">Belum ada kriteria untuk kasus keputusan ini. Silakan tambahkan kriteria terlebih dahulu sebelum menambahkan alternatif.</p>
    @else
        <div class="overflow-x-auto mb-6">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kriteria</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bobot</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($decisionCase->criteria as $criterion)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $criterion->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ ucfirst($criterion->type) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $criterion->weight }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('decision_cases.criteria.edit', [$decisionCase->id, $criterion->id]) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                <form action="{{ route('decision_cases.criteria.destroy', [$decisionCase->id, $criterion->id]) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kriteria ini? Ini juga akan menghapus nilai preferensi terkait.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <hr class="my-6">

    {{-- Bagian Manajemen Alternatif (sudah ada) --}}
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-semibold text-gray-700">Manajemen Alternatif</h2>
        @if ($decisionCase->criteria->isNotEmpty())
            <a href="{{ route('decision_cases.alternatives.create', $decisionCase->id) }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300">
                Tambah Alternatif
            </a>
        @else
            <span class="text-red-500 text-sm">Tambahkan kriteria terlebih dahulu</span>
        @endif
    </div>

    @if ($decisionCase->alternatives->isEmpty())
        <p class="text-gray-600 mb-6">Belum ada alternatif untuk kasus keputusan ini.</p>
    @else
        <div class="overflow-x-auto mb-6">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Alternatif</th>
                        @foreach ($decisionCase->criteria as $criterion)
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $criterion->name }} ({{ ucfirst($criterion->type) }})</th>
                        @endforeach
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($decisionCase->alternatives as $alternative)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $alternative->name }}</td>
                            @foreach ($decisionCase->criteria as $criterion)
                                @php
                                    $value = $alternative->alternativeCriteriaValues->where('criterion_id', $criterion->id)->first();
                                @endphp
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $value ? $value->value : 'N/A' }}
                                </td>
                            @endforeach
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('decision_cases.alternatives.edit', [$decisionCase->id, $alternative->id]) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                <form action="{{ route('decision_cases.alternatives.destroy', [$decisionCase->id, $alternative->id]) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus alternatif ini? Ini juga akan menghapus nilai preferensi terkait.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <hr class="my-6">

    <h2 class="text-2xl font-semibold text-gray-700 mb-4">Hasil Perhitungan ELECTRE</h2>
    @if ($decisionCase->criteria->isNotEmpty() && $decisionCase->alternatives->isNotEmpty())
        <form action="{{ route('decision_cases.calculate_electre', $decisionCase->id) }}" method="POST" class="mb-4">
            @csrf
            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300 mb-4">
                Proses ELECTRE
            </button>
        </form>

        @if (session('electre_results'))
            @php
                $results = session('electre_results');
                $alternatives = $decisionCase->alternatives; // Ambil ulang untuk nama alternatif
                $criteria = $decisionCase->criteria; // Ambil ulang untuk nama kriteria
            @endphp
            <div class="mt-8">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Peringkat Alternatif:</h3>
                <div class="overflow-x-auto mb-6">
                    <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peringkat</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alternatif</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Skor Dominasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($results['rankings'] as $ranking)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $ranking['rank'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $ranking['alternative'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ number_format($ranking['score'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <h3 class="text-xl font-bold text-gray-800 mb-4 mt-6">Matriks Hasil (Untuk Debugging/Analisis Lebih Lanjut):</h3>

                {{-- Matriks Keputusan Awal --}}
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-700 mb-2">Matriks Keputusan Awal (X)</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200 rounded-lg text-xs">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-2 border-r"></th>
                                    @foreach ($criteria as $crit)
                                        <th class="px-4 py-2 border-r">{{ $crit->name }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($alternatives as $altIndex => $alt)
                                    <tr class="border-t">
                                        <td class="px-4 py-2 border-r font-medium">{{ $alt->name }}</td>
                                        @foreach ($criteria as $critIndex => $crit)
                                            <td class="px-4 py-2 border-r">{{ number_format($results['matrices']['decision_matrix'][$altIndex][$critIndex], 2) }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Matriks Ternormalisasi --}}
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-700 mb-2">Matriks Ternormalisasi (V)</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200 rounded-lg text-xs">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-2 border-r"></th>
                                    @foreach ($criteria as $crit)
                                        <th class="px-4 py-2 border-r">{{ $crit->name }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($alternatives as $altIndex => $alt)
                                    <tr class="border-t">
                                        <td class="px-4 py-2 border-r font-medium">{{ $alt->name }}</td>
                                        @foreach ($criteria as $critIndex => $crit)
                                            <td class="px-4 py-2 border-r">{{ number_format($results['matrices']['normalized_matrix'][$altIndex][$critIndex], 4) }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Matriks Ternormalisasi Berbobot --}}
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-700 mb-2">Matriks Ternormalisasi Berbobot (Y)</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200 rounded-lg text-xs">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-2 border-r"></th>
                                    @foreach ($criteria as $crit)
                                        <th class="px-4 py-2 border-r">{{ $crit->name }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($alternatives as $altIndex => $alt)
                                    <tr class="border-t">
                                        <td class="px-4 py-2 border-r font-medium">{{ $alt->name }}</td>
                                        @foreach ($criteria as $critIndex => $crit)
                                            <td class="px-4 py-2 border-r">{{ number_format($results['matrices']['weighted_normalized_matrix'][$altIndex][$critIndex], 4) }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Matriks Konkordansi --}}
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-700 mb-2">Matriks Indeks Konkordansi (C)</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200 rounded-lg text-xs">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-2 border-r"></th>
                                    @foreach ($alternatives as $alt)
                                        <th class="px-4 py-2 border-r">{{ $alt->name }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($alternatives as $i => $altRow)
                                    <tr class="border-t">
                                        <td class="px-4 py-2 border-r font-medium">{{ $altRow->name }}</td>
                                        @foreach ($alternatives as $j => $altCol)
                                            <td class="px-4 py-2 border-r">{{ number_format($results['matrices']['concordance_matrix'][$i][$j], 4) }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Matriks Diskordansi --}}
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-700 mb-2">Matriks Indeks Diskordansi (D)</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200 rounded-lg text-xs">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-2 border-r"></th>
                                    @foreach ($alternatives as $alt)
                                        <th class="px-4 py-2 border-r">{{ $alt->name }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($alternatives as $i => $altRow)
                                    <tr class="border-t">
                                        <td class="px-4 py-2 border-r font-medium">{{ $altRow->name }}</td>
                                        @foreach ($alternatives as $j => $altCol)
                                            <td class="px-4 py-2 border-r">{{ number_format($results['matrices']['discordance_matrix'][$i][$j], 4) }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Matriks Dominansi Konkordansi --}}
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-700 mb-2">
                        Matriks Dominansi Konkordansi (F_C) 
                        @if(isset($results['concordance_threshold']))
                        (Threshold Otomatis: {{ number_format($results['concordance_threshold'], 2) }})
                        @endif
                    </h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200 rounded-lg text-xs">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-2 border-r"></th>
                                    @foreach ($alternatives as $alt)
                                        <th class="px-4 py-2 border-r">{{ $alt->name }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($alternatives as $i => $altRow)
                                    <tr class="border-t">
                                        <td class="px-4 py-2 border-r font-medium">{{ $altRow->name }}</td>
                                        @foreach ($alternatives as $j => $altCol)
                                            <td class="px-4 py-2 border-r">{{ $results['matrices']['concordance_dominance_matrix'][$i][$j] }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Matriks Dominansi Diskordansi --}}
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-700 mb-2">
                        Matriks Dominansi Diskordansi (F_D)
                        @if(isset($results['discordance_threshold']))
                            (Threshold Otomatis: {{ number_format($results['discordance_threshold'], 4) }})
                        @endif
                    </h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200 rounded-lg text-xs">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-2 border-r"></th>
                                    @foreach ($alternatives as $alt)
                                        <th class="px-4 py-2 border-r">{{ $alt->name }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($alternatives as $i => $altRow)
                                    <tr class="border-t">
                                        <td class="px-4 py-2 border-r font-medium">{{ $altRow->name }}</td>
                                        @foreach ($alternatives as $j => $altCol)
                                            <td class="px-4 py-2 border-r">{{ $results['matrices']['discordance_dominance_matrix'][$i][$j] }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Matriks Agregat Dominansi --}}
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-700 mb-2">Matriks Agregat Dominansi (E)</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200 rounded-lg text-xs">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-2 border-r"></th>
                                    @foreach ($alternatives as $alt)
                                        <th class="px-4 py-2 border-r">{{ $alt->name }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($alternatives as $i => $altRow)
                                    <tr class="border-t">
                                        <td class="px-4 py-2 border-r font-medium">{{ $altRow->name }}</td>
                                        @foreach ($alternatives as $j => $altCol)
                                            <td class="px-4 py-2 border-r">{{ $results['matrices']['aggregate_dominance_matrix'][$i][$j] }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>


            </div>
        @endif

    @else
        <span class="text-red-500 text-sm mt-4 inline-block">Harap tambahkan minimal satu kriteria dan satu alternatif untuk memproses perhitungan.</span>
    @endif
</div>
@endsection