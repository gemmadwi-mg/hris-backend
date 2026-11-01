<?php

namespace Database\Seeders;

use App\Models\TerRate; // Pastikan model Anda bernama TerRate
use Illuminate\Database\Seeder;

class TerRateSeeder extends Seeder
{
    public function run(): void
    {
        // Data untuk Kategori A (TK/0, TK/1, K/0)
        $ratesA = [
            ['lower_limit' => 0, 'upper_limit' => 5400000, 'rate' => 0.0000],
            ['lower_limit' => 5400001, 'upper_limit' => 5650000, 'rate' => 0.0025],
            ['lower_limit' => 5650001, 'upper_limit' => 5950000, 'rate' => 0.0050],
            ['lower_limit' => 5950001, 'upper_limit' => 6300000, 'rate' => 0.0075],
            ['lower_limit' => 6300001, 'upper_limit' => 6750000, 'rate' => 0.0100],
            ['lower_limit' => 6750001, 'upper_limit' => 7500000, 'rate' => 0.0125],
            ['lower_limit' => 7500001, 'upper_limit' => 8550000, 'rate' => 0.0150],
            ['lower_limit' => 8550001, 'upper_limit' => 9650000, 'rate' => 0.0175],
            ['lower_limit' => 9650001, 'upper_limit' => 10050000, 'rate' => 0.0200],
            ['lower_limit' => 10050001, 'upper_limit' => 10350000, 'rate' => 0.0225],
            ['lower_limit' => 10350001, 'upper_limit' => 10700000, 'rate' => 0.0250],
            ['lower_limit' => 10700001, 'upper_limit' => 11050000, 'rate' => 0.0300],
            ['lower_limit' => 11050001, 'upper_limit' => 11600000, 'rate' => 0.0350],
            ['lower_limit' => 11600001, 'upper_limit' => 12500000, 'rate' => 0.0400],
            ['lower_limit' => 12500001, 'upper_limit' => 13750000, 'rate' => 0.0500],
            ['lower_limit' => 13750001, 'upper_limit' => 15100000, 'rate' => 0.0600],
            ['lower_limit' => 15100001, 'upper_limit' => 16950000, 'rate' => 0.0700],
            ['lower_limit' => 16950001, 'upper_limit' => 19750000, 'rate' => 0.0800],
            ['lower_limit' => 19750001, 'upper_limit' => 24150000, 'rate' => 0.0900],
            ['lower_limit' => 24150001, 'upper_limit' => 26450000, 'rate' => 0.1000],
            ['lower_limit' => 26450001, 'upper_limit' => 28000000, 'rate' => 0.1100],
            ['lower_limit' => 28000001, 'upper_limit' => 30050000, 'rate' => 0.1200],
            ['lower_limit' => 30050001, 'upper_limit' => 32400000, 'rate' => 0.1300],
            ['lower_limit' => 32400001, 'upper_limit' => 35400000, 'rate' => 0.1400],
            ['lower_limit' => 35400001, 'upper_limit' => 39100000, 'rate' => 0.1500],
            ['lower_limit' => 39100001, 'upper_limit' => 43850000, 'rate' => 0.1600],
            ['lower_limit' => 43850001, 'upper_limit' => 47800000, 'rate' => 0.1700],
            ['lower_limit' => 47800001, 'upper_limit' => 51400000, 'rate' => 0.1800],
            ['lower_limit' => 51400001, 'upper_limit' => 56300000, 'rate' => 0.1900],
            ['lower_limit' => 56300001, 'upper_limit' => 62200000, 'rate' => 0.2000],
            ['lower_limit' => 62200001, 'upper_limit' => 68600000, 'rate' => 0.2100],
            ['lower_limit' => 68600001, 'upper_limit' => 77500000, 'rate' => 0.2200],
            ['lower_limit' => 77500001, 'upper_limit' => 89000000, 'rate' => 0.2300],
            ['lower_limit' => 89000001, 'upper_limit' => 103000000, 'rate' => 0.2400],
            ['lower_limit' => 103000001, 'upper_limit' => 125000000, 'rate' => 0.2500],
            ['lower_limit' => 125000001, 'upper_limit' => 157000000, 'rate' => 0.2600],
            ['lower_limit' => 157000001, 'upper_limit' => 206000000, 'rate' => 0.2700],
            ['lower_limit' => 206000001, 'upper_limit' => 337000000, 'rate' => 0.2800],
            ['lower_limit' => 337000001, 'upper_limit' => 454000000, 'rate' => 0.2900],
            ['lower_limit' => 454000001, 'upper_limit' => 550000000, 'rate' => 0.3000],
            ['lower_limit' => 550000001, 'upper_limit' => 695000000, 'rate' => 0.3100],
            ['lower_limit' => 695000001, 'upper_limit' => 910000000, 'rate' => 0.3200],
            ['lower_limit' => 910000001, 'upper_limit' => 1400000000, 'rate' => 0.3300],
            ['lower_limit' => 1400000001, 'upper_limit' => PHP_INT_MAX, 'rate' => 0.3400],
        ];

        // Data untuk Kategori B (TK/2, K/1, TK/3, K/2)
        $ratesB = [
            ['lower_limit' => 0, 'upper_limit' => 6200000, 'rate' => 0.0000],
            ['lower_limit' => 6200001, 'upper_limit' => 6500000, 'rate' => 0.0025],
            ['lower_limit' => 6500001, 'upper_limit' => 6850000, 'rate' => 0.0050],
            ['lower_limit' => 6850001, 'upper_limit' => 7300000, 'rate' => 0.0075],
            ['lower_limit' => 7300001, 'upper_limit' => 9200000, 'rate' => 0.0100],
            ['lower_limit' => 9200001, 'upper_limit' => 10750000, 'rate' => 0.0150],
            ['lower_limit' => 10750001, 'upper_limit' => 11250000, 'rate' => 0.0200],
            ['lower_limit' => 11250001, 'upper_limit' => 11600000, 'rate' => 0.0250],
            ['lower_limit' => 11600001, 'upper_limit' => 12600000, 'rate' => 0.0300],
            ['lower_limit' => 12600001, 'upper_limit' => 13600000, 'rate' => 0.0400],
            ['lower_limit' => 13600001, 'upper_limit' => 14950000, 'rate' => 0.0500],
            ['lower_limit' => 14950001, 'upper_limit' => 16400000, 'rate' => 0.0600],
            ['lower_limit' => 16400001, 'upper_limit' => 18450000, 'rate' => 0.0700],
            ['lower_limit' => 18450001, 'upper_limit' => 21850000, 'rate' => 0.0800],
            ['lower_limit' => 21850001, 'upper_limit' => 26000000, 'rate' => 0.0900],
            ['lower_limit' => 26000001, 'upper_limit' => 27700000, 'rate' => 0.1000],
            ['lower_limit' => 27700001, 'upper_limit' => 29350000, 'rate' => 0.1100],
            ['lower_limit' => 29350001, 'upper_limit' => 31450000, 'rate' => 0.1200],
            ['lower_limit' => 31450001, 'upper_limit' => 33950000, 'rate' => 0.1300],
            ['lower_limit' => 33950001, 'upper_limit' => 37100000, 'rate' => 0.1400],
            ['lower_limit' => 37100001, 'upper_limit' => 41100000, 'rate' => 0.1500],
            ['lower_limit' => 41100001, 'upper_limit' => 45800000, 'rate' => 0.1600],
            ['lower_limit' => 45800001, 'upper_limit' => 49500000, 'rate' => 0.1700],
            ['lower_limit' => 49500001, 'upper_limit' => 53800000, 'rate' => 0.1800],
            ['lower_limit' => 53800001, 'upper_limit' => 58500000, 'rate' => 0.1900],
            ['lower_limit' => 58500001, 'upper_limit' => 64000000, 'rate' => 0.2000],
            ['lower_limit' => 64000001, 'upper_limit' => 71000000, 'rate' => 0.2100],
            ['lower_limit' => 71000001, 'upper_limit' => 80000000, 'rate' => 0.2200],
            ['lower_limit' => 80000001, 'upper_limit' => 93000000, 'rate' => 0.2300],
            ['lower_limit' => 93000001, 'upper_limit' => 109000000, 'rate' => 0.2400],
            ['lower_limit' => 109000001, 'upper_limit' => 129000000, 'rate' => 0.2500],
            ['lower_limit' => 129000001, 'upper_limit' => 163000000, 'rate' => 0.2600],
            ['lower_limit' => 163000001, 'upper_limit' => 211000000, 'rate' => 0.2700],
            ['lower_limit' => 211000001, 'upper_limit' => 374000000, 'rate' => 0.2800],
            ['lower_limit' => 374000001, 'upper_limit' => 459000000, 'rate' => 0.2900],
            ['lower_limit' => 459000001, 'upper_limit' => 555000000, 'rate' => 0.3000],
            ['lower_limit' => 555000001, 'upper_limit' => 704000000, 'rate' => 0.3100],
            ['lower_limit' => 704000001, 'upper_limit' => 957000000, 'rate' => 0.3200],
            ['lower_limit' => 957000001, 'upper_limit' => 1405000000, 'rate' => 0.3300],
            ['lower_limit' => 1405000001, 'upper_limit' => PHP_INT_MAX, 'rate' => 0.3400],
        ];

        // Data untuk Kategori C (K/3)
        $ratesC = [
            ['lower_limit' => 0, 'upper_limit' => 6600000, 'rate' => 0.0000],
            ['lower_limit' => 6600001, 'upper_limit' => 6950000, 'rate' => 0.0025],
            ['lower_limit' => 6950001, 'upper_limit' => 7350000, 'rate' => 0.0050],
            ['lower_limit' => 7350001, 'upper_limit' => 7800000, 'rate' => 0.0075],
            ['lower_limit' => 7800001, 'upper_limit' => 8850000, 'rate' => 0.0100],
            ['lower_limit' => 8850001, 'upper_limit' => 9800000, 'rate' => 0.0125],
            ['lower_limit' => 9800001, 'upper_limit' => 10950000, 'rate' => 0.0150],
            ['lower_limit' => 10950001, 'upper_limit' => 11200000, 'rate' => 0.0175],
            ['lower_limit' => 11200001, 'upper_limit' => 12050000, 'rate' => 0.0200],
            ['lower_limit' => 12050001, 'upper_limit' => 12950000, 'rate' => 0.0300],
            ['lower_limit' => 12950001, 'upper_limit' => 14150000, 'rate' => 0.0400],
            ['lower_limit' => 14150001, 'upper_limit' => 15550000, 'rate' => 0.0500],
            ['lower_limit' => 15550001, 'upper_limit' => 17050000, 'rate' => 0.0600],
            ['lower_limit' => 17050001, 'upper_limit' => 19500000, 'rate' => 0.0700],
            ['lower_limit' => 19500001, 'upper_limit' => 22700000, 'rate' => 0.0800],
            ['lower_limit' => 22700001, 'upper_limit' => 26600000, 'rate' => 0.0900],
            ['lower_limit' => 26600001, 'upper_limit' => 28100000, 'rate' => 0.1000],
            ['lower_limit' => 28100001, 'upper_limit' => 30100000, 'rate' => 0.1100],
            ['lower_limit' => 30100001, 'upper_limit' => 32600000, 'rate' => 0.1200],
            ['lower_limit' => 32600001, 'upper_limit' => 35400000, 'rate' => 0.1300],
            ['lower_limit' => 35400001, 'upper_limit' => 38900000, 'rate' => 0.1400],
            ['lower_limit' => 38900001, 'upper_limit' => 43000000, 'rate' => 0.1500],
            ['lower_limit' => 43000001, 'upper_limit' => 47400000, 'rate' => 0.1600],
            ['lower_limit' => 47400001, 'upper_limit' => 51200000, 'rate' => 0.1700],
            ['lower_limit' => 51200001, 'upper_limit' => 55800000, 'rate' => 0.1800],
            ['lower_limit' => 55800001, 'upper_limit' => 60400000, 'rate' => 0.1900],
            ['lower_limit' => 60400001, 'upper_limit' => 66700000, 'rate' => 0.2000],
            ['lower_limit' => 66700001, 'upper_limit' => 74500000, 'rate' => 0.2100],
            ['lower_limit' => 74500001, 'upper_limit' => 83200000, 'rate' => 0.2200],
            ['lower_limit' => 83200001, 'upper_limit' => 95600000, 'rate' => 0.2300],
            ['lower_limit' => 95600001, 'upper_limit' => 110000000, 'rate' => 0.2400],
            ['lower_limit' => 110000001, 'upper_limit' => 134000000, 'rate' => 0.2500],
            ['lower_limit' => 134000001, 'upper_limit' => 169000000, 'rate' => 0.2600],
            ['lower_limit' => 169000001, 'upper_limit' => 221000000, 'rate' => 0.2700],
            ['lower_limit' => 221000001, 'upper_limit' => 390000000, 'rate' => 0.2800],
            ['lower_limit' => 390000001, 'upper_limit' => 463000000, 'rate' => 0.2900],
            ['lower_limit' => 463000001, 'upper_limit' => 561000000, 'rate' => 0.3000],
            ['lower_limit' => 561000001, 'upper_limit' => 709000000, 'rate' => 0.3100],
            ['lower_limit' => 709000001, 'upper_limit' => 965000000, 'rate' => 0.3200],
            ['lower_limit' => 965000001, 'upper_limit' => 1419000000, 'rate' => 0.3300],
            ['lower_limit' => 1419000001, 'upper_limit' => PHP_INT_MAX, 'rate' => 0.3400],
        ];

        // Mengosongkan tabel sebelum mengisi agar tidak ada data ganda
        TerRate::query()->truncate();

        $counter = 1;

        // Memasukkan data Kategori A (TC01)
        foreach ($ratesA as $rate) {
            TerRate::create([
                'ter_rate_id' => 'T' . str_pad($counter++, 3, '0', STR_PAD_LEFT),
                'ter_category_id' => 'C01', 
                'lower_limit' => $rate['lower_limit'],
                'upper_limit' => $rate['upper_limit'],
                'rate' => $rate['rate'],
            ]);
        }

        // Memasukkan data Kategori B (TC02)
        foreach ($ratesB as $rate) {
            TerRate::create([
                'ter_rate_id' => 'T' . str_pad($counter++, 3, '0', STR_PAD_LEFT),
                'ter_category_id' => 'C02', 
                'lower_limit' => $rate['lower_limit'],
                'upper_limit' => $rate['upper_limit'],
                'rate' => $rate['rate'],
            ]);
        }

        // Memasukkan data Kategori C (TC03)
        foreach ($ratesC as $rate) {
            TerRate::create([
                'ter_rate_id' => 'T' . str_pad($counter++, 3, '0', STR_PAD_LEFT),
                'ter_category_id' => 'C03',
                'lower_limit' => $rate['lower_limit'],
                'upper_limit' => $rate['upper_limit'],
                'rate' => $rate['rate'],
            ]);
        }
    }
}