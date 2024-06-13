<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\BarangKeluarController;


Route::get('/', function () {
    return view('login');
});


Route::resource('barang', BarangController::class);
Route::resource('kategori', KategoriController::class);
Route::resource('barangmasuk', BarangMasukController::class);
Route::resource('barangkeluar', BarangKeluarController::class);

