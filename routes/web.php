<?php

use App\Http\Controllers\DecisionCaseController;
use App\Http\Controllers\CriterionController;
use App\Http\Controllers\AlternativeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route default yang akan mengarahkan ke halaman daftar kasus keputusan
Route::get('/', function () {
    return redirect()->route('decision_cases.index');
});

// Resource route untuk DecisionCaseController
// Ini secara otomatis akan membuat route untuk index, create, store, show, edit, update, dan destroy
Route::resource('decision_cases', DecisionCaseController::class);
Route::resource('decision_cases.criteria', CriterionController::class)->except(['index', 'show']);
Route::resource('decision_cases.alternatives', AlternativeController::class)->except(['index', 'show']);
Route::post('decision_cases/{decisionCase}/calculate-electre', [DecisionCaseController::class, 'calculateElectre'])->name('decision_cases.calculate_electre');