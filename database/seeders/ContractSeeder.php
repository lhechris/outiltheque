<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Contract;

class ContractSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = storage_path('app/csv/contracts.csv');

        $file = fopen($csvFile, 'r');

        $header = fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {
            $data = array_combine($header, $row);
            Contract::updateOrCreate(
                ['name' => $data['name']],
                [
                    'unit' => $data['unit'],
                    'flat_rate' => $data['flat_rate'],
                    'restriction' => $data['restriction'],
                    'color' => $data['color'],
                ]
            );
        }

        fclose($file);
    }
}
