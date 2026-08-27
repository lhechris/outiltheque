<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Reservation;
use App\Models\User;
use App\Models\Tool;

class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = storage_path('app/csv/reservations.csv');

        $file = fopen($csvFile, 'r');

        $header = fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {
            $data = array_combine($header, $row);
            
            //reference,name,email,phone,tool,date_start,date_end,state,payment_state,comment

            $user = User::where("email",$data['email'])->first();
            if (!$user) {
                //l'utilisateur n'existe pas on le cree
                $user = User::create([
                    'name' => $data['name'],
                    'firstname' => '',
                    'phone' => $data['phone'],
                    'email' => $data['email'],                    
                ]);
            }

            $tool = Tool::where("name",$data["tool"])->first();            

            //"id" , "reference",  "user_id", "name" , "email", "phone" , "tool_id" , "date_start" , "date_end" , "state" , "payment_state" , "payment_id" integer, "comment" , "created_at" datetime, "updated_at" datetime, foreign key("tool_id") references "tools"("id") on delete cascade
            Reservation::updateOrCreate(
                ['reference' => $data['reference']],
                [
                    "user_id" => $user->id,
                    "name" => $data["name"],
                    "email" => $data["email"],
                    "phone" => $data["phone"],
                    "tool_id" => $tool?->id,
                    "date_start" => $data["date_start"],
                    "date_end" => $data["date_end"],
                    "state" => $data["state"],
                    "payment_state" => $data["payment_state"],
                    "comment" => $data["comment"],
                ]
            );
        }
    }
}
