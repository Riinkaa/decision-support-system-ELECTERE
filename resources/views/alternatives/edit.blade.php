@extends('layouts.app')

@section('content')
<div class="bg-white p-6 rounded-lg shadow-md max-w-xl mx-auto">
    <a href="{{ route('decision_cases.show', $decisionCase->id) }}" class="text-blue-500 hover:text-blue-700 mb-4 inline-block">&larr; Kembali ke Detail Kasus</a>

    <h1 class="text-3xl font-bold text-gray-800 mb-6">Edit Alternatif: "{{ $alternative->name }}" untuk "{{ $decisionCase->name }}"</h1>

    <form action="{{ route('decision_cases.alternatives.update', [$decisionCase->id, $alternative->id]) }}" method="POST">
        @csrf
        @method('PUT') {{-- Penting untuk metode UPDATE --}}
        <div class="mb-4">
            <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Nama Alternatif:</label>
            <input type="text" name="name" id="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('name') border-red-500 @enderror" value="{{ old('name', $alternative->name) }}" required>
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
                @php
                    // Ambil nilai yang sudah ada atau old() jika ada error validasi
                    $value = old('criterion_values.' . $criterion->id, $currentValues[$criterion->id] ?? '');
                @endphp
                <input type="number" name="criterion_values[{{ $criterion->id }}]" id="criterion_values_{{ $criterion->id }}" step="any" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('criterion_values.' . $criterion->id) border-red-500 @enderror" value="{{ $value }}" required>
                @error('criterion_values.' . $criterion->id)
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>
        @endforeach

        <div class="flex items-center justify-between mt-6">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-300">
                Perbarui Alternatif
            </button>
            <a href="{{ route('decision_cases.show', $decisionCase->id) }}" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection