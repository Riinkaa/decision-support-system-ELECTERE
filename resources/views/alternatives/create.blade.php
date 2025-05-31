@extends('layouts.app')

@section('content')
<div class="bg-white p-6 rounded-lg shadow-md max-w-xl mx-auto">
    <a href="{{ route('decision_cases.show', $decisionCase->id) }}" class="text-blue-500 hover:text-blue-700 mb-4 inline-block">&larr; Kembali ke Detail Kasus</a>

    <h1 class="text-3xl font-bold text-gray-800 mb-6">Tambah Alternatif Baru untuk "{{ $decisionCase->name }}"</h1>

    @if($criteria->isEmpty())
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">
            <p class="font-bold">Perhatian:</p>
            <p>Anda belum memiliki kriteria untuk kasus keputusan ini. Harap tambahkan kriteria terlebih dahulu sebelum menambahkan alternatif.</p>
        </div>
        <a href="{{ route('decision_cases.criteria.create', $decisionCase->id) }}" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300">
            Tambah Kriteria Sekarang
        </a>
    @else
        <form action="{{ route('decision_cases.alternatives.store', $decisionCase->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Nama Alternatif:</label>
                <input type="text" name="name" id="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('name') border-red-500 @enderror" value="{{ old('name') }}" required>
                @error('name')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <h2 class="text-xl font-semibold text-gray-700 mb-4 mt-6">Nilai Preferensi per Kriteria:</h2>
            @foreach ($criteria as $criterion)
                <div class="mb-4">
                    <label for="criterion_values_{{ $criterion->id }}" class="block text-gray-700 text-sm font-bold mb-2">
                        {{ $criterion->name }} ({{ ucfirst($criterion->type) }})
                    </label>
                    <input type="number" name="criterion_values[{{ $criterion->id }}]" id="criterion_values_{{ $criterion->id }}" step="any" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('criterion_values.' . $criterion->id) border-red-500 @enderror" value="{{ old('criterion_values.' . $criterion->id) }}" required>
                    @error('criterion_values.' . $criterion->id)
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

            <div class="flex items-center justify-between mt-6">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-300">
                    Simpan Alternatif
                </button>
                <a href="{{ route('decision_cases.show', $decisionCase->id) }}" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                    Batal
                </a>
            </div>
        </form>
    @endif
</div>
@endsection