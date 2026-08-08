<?php

namespace App\Services\Helloasso;

use App\Models\Parameter;
use App\Models\Reservation;
use Illuminate\Support\Facades\Http;

class Payment {

    //Initialise un ordre de paiement
    static public function init(Reservation $reservation ) : bool {

        \Log::info($reservation->reference." Demande encaissement Helloaso ".$reservation->tool->name.' pour '.$reservation->name.' '.$reservation->email);

        $fullname = trim($reservation->name); // remove double space
        $firstname = substr($fullname, 0, strpos($fullname, ' '));
        $lastname = substr($fullname, strpos($fullname, ' '), strlen($fullname));

        //$baseurl = env('APP_URL');
        $baseurl = "https://outiltheque.labo-binette.fr";

        $details= [
            "totalAmount" => $reservation->tool->contract->price*100,
            "initialAmount" => $reservation->tool->contract->price*100,
            "itemName" => "Location ".$reservation->reference."(".$reservation->tool->name.")",
            "backUrl" => $baseurl."/payments/error/".$reservation->reference,
            "errorUrl" => $baseurl."/payments/error/".$reservation->reference,
            "returnUrl" => $baseurl."/payments/confirm/".$reservation->reference,
            "containsDonation" => false,
            "payer"=> [
              "firstName"=> $firstname,
              "lastName"=> $lastname,
              "email"=> $reservation->email,
              "country"=> "FRA"
            ]            
        ];

        Token::refresh();

        $data = Parameter::where('name','=',env('HELLOASSO_KEY_ACCESS_TOKEN',''))->firstOrFail();
        $accesstoken = $data->val;
        $haresp = Http::withToken($accesstoken)->post(env('HELLOASSO_ENCAISSEMENT_URL',''), $details);
        \Log::debug(env('HELLOASSO_ENCAISSEMENT_URL'));
        \Log::debug($details);
        \Log::debug("Reponse : ".$haresp->status());
        \Log::debug($haresp->body());
        
        if ($haresp->status() == 200) {
            //La demande de paiement HA est acceptée
            //Met à jour la réservation avec les elements
            $reservation->setPendingHA($haresp->json()["id"]);
            
            //Redirige sur la page d'encaissement HA
            redirect($haresp->json()["redirectUrl"]);
            return true;
        
        } else {
            \Log::info("Erreur helloasso");
            return false;
        }
    }


    static function check(Reservation $reservation) : bool {

            $data = Parameter::where('name','=',env('HELLOASSO_KEY_ACCESS_TOKEN',''))->first();
            $accesstoken = $data->val;

            $haresp = Http::withToken($accesstoken)->get(env('HELLOASSO_ENCAISSEMENT_URL','')."/".$reservation->payment_id);
            \Log::debug(env('HELLOASSO_ENCAISSEMENT_URL','')."/".$reservation->payment_id);
            //\Log::debug($accesstoken);
            \Log::debug("Reponse : ".$haresp->status());
            \Log::debug($haresp->body());

            if ($haresp->status() == 200) {
                $response=$haresp->json();
                if (array_key_exists("id",$response) && array_key_exists("redirectUrl",$response)) {
                    if (array_key_exists("order",$response)) {
                        $paiementok = false;

                        //est ce que l'ordre à été passé ?
                        $order = $response['order'];
                        //on cherche si les paiements sont dans l'état Authorized
                        if ($order) {
                            if (array_key_exists("payments",$order)) {
                                foreach($order["payments"] as $item) {
                                    if (array_key_exists("state",$item)) {
                                        if ($item["state"] == "Authorized") {
                                            $paiementok = true;
                                        }
                                    }
                                }
                            }
                        }
                        return $paiementok;                        
                    
                    } else {
                        //La demande n'a pas été finalisé, il faudrait la renvoyer
                        \Log::info($reservation->reference." Paiement non effectué");                        
                    }
                } else {
                    \Log::info($reservation->reference." Réponse reçue non prévue");
                }
        
            } else {
                \Log::info($reservation->reference." Erreur helloasso");
                \Log::info($haresp->status());
                \Log::info($haresp->body());                 
            } 
            
            return false;
    }

}