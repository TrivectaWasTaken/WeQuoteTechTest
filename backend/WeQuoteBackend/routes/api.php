<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

Route::get('/projects', [ProjectController::class, 'index']);
Route::get('/projects/{id}', [ProjectController::class, 'show']);
Route::get('/quotes/{id}', [ProjectController::class, 'quotes']);

Route::get('/invoices/{id}', [InvoiceController::class, 'index']);

Route::get('/customers/{id}/projects', [CustomerController::class, 'projects']);
Route::get('/customers/{id}', [CustomerController::class, 'show']);

Route::get('/organisation/{id}', [OrganisationController::class, 'show']);
Route::get('/stats/{id}', [OrganisationController::class, 'stats']);
