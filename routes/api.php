<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AutenticationController;
use App\Http\Controllers\ComprobanteController;

Route::post('/login', [AutenticationController::class, 'login']);
Route::group(['middleware' => ['jwt.verify']], function() {
    Route::post('/comprobante', [ComprobanteController::class, 'registrarComprobante']);
    Route::get('/comprobante/{id_comprobante}', [ComprobanteController::class, 'comprobanteById']);
    Route::delete('/comprobante/{id_comprobante}', [ComprobanteController::class, 'deleteComprobanteById']);
    Route::get('/comprobantes/total', [ComprobanteController::class, 'comprobanteAll']);
    Route::get('/items/total', [ComprobanteController::class, 'itemsAll']);
});