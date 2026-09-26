<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;

class EmployeeController extends Controller
{
    public function index()
    {
        // Eager loading relasi 'position.department' mencegah N+1 Query Problem
        $employees = Employee::with('position.department')->latest()->paginate(10);
        return EmployeeResource::collection($employees);
    }

    public function store(StoreEmployeeRequest $request)
    {
        // $request->validated() hanya mengambil data yang lolos aturan FormRequest
        $employee = Employee::create($request->validated());
        $employee->load('position.department'); // Muat relasi setelah dibuat

        return new EmployeeResource($employee);
    }

    public function show(Employee $employee)
    {
        $employee->load('position.department');
        return new EmployeeResource($employee);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $employee->update($request->validated());
        $employee->load('position.department');

        return new EmployeeResource($employee);
    }

    public function destroy(Employee $employee)
    {
        $employee->delete(); // Ini akan memicu SoftDelete secara otomatis
        
        return response()->json([
            'message' => 'Data karyawan berhasil dihapus (Soft Delete)'
        ], 200);
    }
}