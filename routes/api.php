<?php

use App\Http\Controllers\Api\EmployeeController;
use Illuminate\Support\Facades\Route;

// Untuk sementara kita biarkan terbuka tanpa autentikasi agar mudah ditest di Postman
Route::apiResource('employees', EmployeeController::class);
