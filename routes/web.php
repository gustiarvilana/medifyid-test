<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/master-items', [App\Http\Controllers\MasterItemsController::class, 'index']);
Route::get('/master-items/search', [App\Http\Controllers\MasterItemsController::class, 'search']);
Route::get('/master-items/download-excel', [App\Http\Controllers\MasterItemsController::class, 'downloadExcel']);
Route::get('/master-items/form/{method}/{id?}', [App\Http\Controllers\MasterItemsController::class, 'formView']);
Route::post('/master-items/form/{method}/{id?}', [App\Http\Controllers\MasterItemsController::class, 'formSubmit']);

Route::get('/master-items/view/{kode}', [App\Http\Controllers\MasterItemsController::class, 'singleView']);
Route::POST('/master-items/delete/{id}', [App\Http\Controllers\MasterItemsController::class, 'delete']);
Route::POST('/master-items/force-delete/{id}', [App\Http\Controllers\MasterItemsController::class, 'forceDelete']);
Route::POST('/master-items/restore/{id}', [App\Http\Controllers\MasterItemsController::class, 'restore']);


Route::get('/master-items/update-random-data', [App\Http\Controllers\MasterItemsController::class, 'updateRandomData']);

// Kategori Routes
Route::get('/kategoris', [App\Http\Controllers\KategoriController::class, 'index']);
Route::get('/kategoris/search', [App\Http\Controllers\KategoriController::class, 'search']);
Route::get('/kategoris/form/{method}/{id?}', [App\Http\Controllers\KategoriController::class, 'formView']);
Route::post('/kategoris/form/{method}/{id?}', [App\Http\Controllers\KategoriController::class, 'formSubmit']);
Route::get('/kategoris/view/{id}', [App\Http\Controllers\KategoriController::class, 'singleView']);
Route::get('/kategoris/download-pdf/{id}', [App\Http\Controllers\KategoriController::class, 'downloadPdf']);
Route::post('/kategoris/delete/{id}', [App\Http\Controllers\KategoriController::class, 'delete']);

// Pasien Routes
Route::get('/pasiens', [App\Http\Controllers\PasienController::class, 'index']);
Route::get('/pasiens/search', [App\Http\Controllers\PasienController::class, 'search']);
Route::get('/pasiens/download-excel', [App\Http\Controllers\PasienController::class, 'downloadExcel']);
Route::get('/pasiens/form/{method}/{kode?}', [App\Http\Controllers\PasienController::class, 'formView']);
Route::post('/pasiens/form/{method}/{kode?}', [App\Http\Controllers\PasienController::class, 'formSubmit']);
Route::get('/pasiens/view/{kode}', [App\Http\Controllers\PasienController::class, 'singleView']);
Route::get('/pasiens/download-pdf/{kode}', [App\Http\Controllers\PasienController::class, 'downloadPdf']);
Route::post('/pasiens/delete/{id}', [App\Http\Controllers\PasienController::class, 'delete']);
Route::post('/pasiens/force-delete/{id}', [App\Http\Controllers\PasienController::class, 'forceDelete']);
Route::post('/pasiens/restore/{id}', [App\Http\Controllers\PasienController::class, 'restore']);

