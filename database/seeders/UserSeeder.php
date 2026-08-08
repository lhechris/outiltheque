<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $csvFile = storage_path('app/csv/users.csv');

        $file = fopen($csvFile, 'r');

        $header = fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {
            $data = array_combine($header, $row);
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => $data['password'],
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

