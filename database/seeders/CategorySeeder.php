<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Category;


class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $csvFile = storage_path('app/csv/categories.csv');

        $file = fopen($csvFile, 'r');

        $header = fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {
            $data = array_combine($header, $row);
            Category::updateOrCreate(
                ['name' => $data['name']],
                [
                    'description' => $data['description'],
                ]
            );
        }

        // Alternative pour inserer en un bloc
        /*
        $members = [];
        while (($row = fgetcsv($file)) !== false) {
            $data = array_combine($header, $row);

            $members[] = [
                'name' => $data['name'],
                'description' => $data['description'],
            ];
        }
        Member::insert($members);
        */

        fclose($file);
    }
}

