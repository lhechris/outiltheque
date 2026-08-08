<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Feature;
use App\Models\Tool;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = storage_path('app/csv/features.csv');

        $file = fopen($csvFile, 'r');

        $header = fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {
            $data = array_combine($header, $row);
            //name,val,tool
            $tool = Tool::where('name',$data['tool'])->first();
            $toolid = -1;
            if ($tool) {
                $toolid = $tool->id;
            }
            Feature::insert(                
                [
                    'name' => $data['name'],
                    'val' => $data['val'],
                    'tool_id' => $toolid,
                ]
            );
        }

        fclose($file);
    }
}
