<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nip' => $this->nip,
            'nama_lengkap' => $this->nama_lengkap,
            'email' => $this->email,
            'nomor_telepon' => $this->nomor_telepon,
            'tanggal_bergabung' => $this->tanggal_bergabung->format('Y-m-d'),

            // Mengambil relasi (Mencegah error jika relasi kosong)
            'jabatan' => $this->whenLoaded('position', function () {
                return [
                    'id' => $this->position->id,
                    'nama_jabatan' => $this->position->nama_jabatan,
                    // Mengambil departemen dari jabatan
                    'departemen' => $this->position->department ? $this->position->department->nama_departemen : null,
                ];
            }),

            'rekening' => [
                'bank' => $this->nama_bank,
                'nomor' => $this->nomor_rekening,
            ],
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
