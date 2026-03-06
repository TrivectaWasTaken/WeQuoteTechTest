<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

Route::get('/projects', [ProjectController::class, 'index']);
Route::get('/projects/{project}', [ProjectController::class, 'show']);
Route::get('/quotes/{project}', [ProjectController::class, 'quotes']);

Route::get('/invoices/{project}', [InvoiceController::class, 'index']);

Route::get('/customers/{customer}/projects', [CustomerController::class, 'projects']);

Route::get('/organisation/{organisation}', [OrganisationController::class, 'show']);
Route::get('/stats/{organisation}', [OrganisationController::class, 'stats']);
