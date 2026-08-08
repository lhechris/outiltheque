<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Tool;
use App\Models\Category;
use App\Models\Contract;

class ToolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $csvFile = storage_path('app/csv/tools.csv');

        $file = fopen($csvFile, 'r');

        $header = fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {
            $data = array_combine($header, $row);
            //name,description,contract,duration,number,category,file1,file2,advice,caution
            $cat = Category::where('name',$data['category'])->first();
            $catid = -1;
            if ($cat) {
                $catid = $cat->id;
            }
            $contract = Contract::first();
            Tool::updateOrCreate(
                ['name' => $data['name']],
                [
                    'description' => $data['description'],
                    'number' => $data['number'],
                    'icon' => $data['file1'],
                    'image'  => $data['file2'],
                    'advice' =>$data['advice'],
                    'caution'=>$data['caution'],
                    'category_id' => $catid,
                    'contract_id' => $contract->id,
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
                'prenom' => $data['prenom'],
                'type' => $data['type'],
                'birthdate' => $data['birthdate'],
                'licence' => $data['licence'],
            ];
        }
        Member::insert($members);
        */

        fclose($file);
    }
}

