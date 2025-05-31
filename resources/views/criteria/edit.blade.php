@extends('layouts.app')

@section('content')
<div class="bg-white p-6 rounded-lg shadow-md max-w-lg mx-auto">
    <a href="{{ route('decision_cases.show', $decisionCase->id) }}" class="text-blue-500 hover:text-blue-700 mb-4 inline-block">&larr; Kembali ke Detail Kasus</a>

    <h1 class="text-3xl font-bold text-gray-800 mb-6">Edit Kriteria: "{{ $criterion->name }}" untuk "{{ $decisionCase->name }}"</h1>

    <form action="{{ route('decision_cases.criteria.update', [$decisionCase->id, $criterion->id]) }}" method="POST">
        @csrf
        @method('PUT') {{-- Penting untuk metode UPDATE --}}
        <div class="mb-4">
            <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Nama Kriteria:</label>
            <input type="text" name="name" id="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('name') border-red-500 @enderror" value="{{ old('name', $criterion->name) }}" required>
            @error('name')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-4">
            <label for="type" class="block text-gray-700 text-sm font-bold mb-2">Tipe Kriteria:</label>
            <select name="type" id="type" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('type') border-red-500 @enderror" required>
                <option value="">Pilih Tipe</option>
                <option value="benefit" {{ old('type', $criterion->type) == 'benefit' ? 'selected' : '' }}>Benefit (Semakin Besar Semakin Baik)</option>
                <option value="cost" {{ old('type', $criterion->type) == 'cost' ? 'selected' : '' }}>Cost (Semakin Kecil Semakin Baik)</option>
            </select>
            @error('type')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-6">
            <label for="weight" class="block text-gray-700 text-sm font-bold mb-2">Bobot Kriteria (misal: 1-5):</label>
            <input type="number" name="weight" id="weight" step="0.01" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('weight') border-red-500 @enderror" value="{{ old('weight', $criterion->weight) }}" required min="0">
            @error('weight')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>
        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-300">
                Perbarui Kriteria
            </button>
            <a href="{{ route('decision_cases.show', $decisionCase->id) }}" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection