<?php

namespace Database\Seeders;

use App\Models\Certification;
use Illuminate\Database\Seeder;

class CertificationSeeder extends Seeder
{
    public function run(): void
    {
        $certifications = [
            ['name' => 'Sertifikat Halal', 'issuer' => 'MUI'],
            ['name' => 'Izin Edar BPOM', 'issuer' => 'BPOM RI'],
            ['name' => 'ISO 22000', 'issuer' => 'Badan Standardisasi Nasional'],
        ];

        foreach ($certifications as $certification) {
            Certification::firstOrCreate(
                ['name' => $certification['name'], 'issuer' => $certification['issuer']]
            );
        }
    }
}
