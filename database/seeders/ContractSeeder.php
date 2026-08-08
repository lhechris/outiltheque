<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Contract;

class PriceSeeder extends Seeder
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
            //name,price
            Price::updateOrCreate(
                ['name' => $data['name']],
                [
                    'price' => $data['price'],
                    'restriction' => $data['restriction'],
                    'color' => $data['color'],
                ]
            );
        }

        fclose($file);
    }
}
