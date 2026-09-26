<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Mendapatkan UUID dari parameter URL (misal: /api/employees/{employee})
        $employeeId = $this->route('employee')->id;

        return [
            'position_id' => 'required|uuid|exists:positions,id',
            'nip' => ['required', 'string', 'max:20', Rule::unique('employees')->ignore($employeeId)],
            'nama_lengkap' => 'required|string|max:100',
            'email' => ['required', 'email', Rule::unique('employees')->ignore($employeeId)],
            'nomor_telepon' => 'nullable|string|max:15',
            'tanggal_bergabung' => 'required|date',
            'nama_bank' => 'nullable|string|max:50',
            'nomor_rekening' => 'nullable|string|max:50',
        ];
    }
}
