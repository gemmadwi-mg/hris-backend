<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Set ke true agar request diizinkan
    }

    public function rules(): array
    {
        return [
            'position_id' => 'required|uuid|exists:positions,id',
            'nip' => 'required|string|max:20|unique:employees,nip',
            'nama_lengkap' => 'required|string|max:100',
            'email' => 'required|email|unique:employees,email',
            'nomor_telepon' => 'nullable|string|max:15',
            'tanggal_bergabung' => 'required|date',
            'nama_bank' => 'nullable|string|max:50',
            'nomor_rekening' => 'nullable|string|max:50',
        ];
    }
}
