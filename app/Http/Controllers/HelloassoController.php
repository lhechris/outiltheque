<?php

namespace App\Http\Controllers;

use App\Models\Helloasso;
use App\Models\Reservations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

use App\Mail\ConfirmResa;
use Illuminate\Support\Facades\Mail;
use App\Models\Paiement;


class HelloassoController extends Controller
{

   /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        //Pour test il n'ya plus de route 
 
        /*$this->refreshToken();
        //$this->initToken();
        $data = Helloasso::get();        
        return response()->json(['status' => true, 'data' => $data]);*/
        return response()->json(['status' => false, 'data' => 'Not implemented']);
    }


    /**
     * Encaissement
     *
     * @param id l'identifiant de la reservation 
     * @return \Illuminate\Http\JsonResponse
     */
    public function encaissement($reference) : \Illuminate\Http\JsonResponse
    {

        $resa = Reservations::leftjoin("outils","reservations.outil_id","=","outils.id")
                            ->select("reservations.*","outils.nom as nomoutil","outils.prix")
                            ->where("reservations.reference","=",$reference)
                            ->first();

        \Log::info("Demande encaissement Helloaso :[".$reference.'] '.$resa['nomoutil'].' pour '.$resa->nom.' '.$resa->email);

        $fullname = trim($resa->nom); // remove double space
        $firstname = substr($fullname, 0, strpos($fullname, ' '));
        $lastname = substr($fullname, strpos($fullname, ' '), strlen($fullname));


        $details= [
            "totalAmount" => $resa->prix*100,
            "initialAmount" => $resa->prix*100,
            "itemName" => "Location ".$resa['reference']."(".$resa['nomoutil'].")",
            "backUrl" => "https://outiltheque.labo-binette.fr/encaissementerreur/".$resa->reference,
            "errorUrl" => "https://outiltheque.labo-binette.fr/encaissementerreur/".$resa->reference,
            "returnUrl" => "https://outiltheque.labo-binette.fr/confirmation/".$resa->reference,
            "containsDonation" => false,
            "payer"=> [
              "firstName"=> $firstname,
              "lastName"=> $lastname,
              "email"=> $resa->email,
              "country"=> "FRA"
            ]            
        ];

        $this->refreshToken();
        $data = Helloasso::where('nom','=',env('HELLOASSO_KEY_ACCESS_TOKEN',''))->first();
        $accesstoken = $data->valeur;

        $haresp = Http::withToken($accesstoken)->post(env('HELLOASSO_ENCAISSEMENT_URL',''), $details);
        \Log::info("Reponse : ".$haresp->status());
        
        if ($haresp->status() == 200) {
            \Log::info($haresp->body());
            //store status
            $resa->paiement_state = Reservations::PAIEMENT_STATE_HA_ENCOURS;
            $resa->state = Reservations::STATE_PAIEMENT;
            $resa->paiement_id = $haresp->json()["id"];
            $resa->update();

            return response()->json(['status' => true, 'data' => $haresp->json()]);
        
        } else {
            \Log::info("Erreur helloasso");
            \Log::info($haresp->body());
            return response()->json(['status' => false, 'data' => "Erreur communication helloasso"]);
        }
    }


    /**
     * CAsh
     *
     * @param id l'identifiant de la reservation 
     * @return \Illuminate\Http\JsonResponse
     */
    public function cash(Request $request, $reference) : \Illuminate\Http\JsonResponse
    {
        $resa = Reservations::where('reference','=',$reference)->first();

        \Log::info("Paiement en cash :[".$reference.'] '.$resa['nomoutil'].' pour '.$resa->nom.' '.$resa->email);
        \Log::info("Status = ".Reservations::STATE_PAIEMENT." Paiement status=".Reservations::PAIEMENT_STATE_A_PAYER);
        $resa->paiement_state = Reservations::PAIEMENT_STATE_A_PAYER;
        $resa->state = Reservations::STATE_PAIEMENT;
        $resa->update();
        return response()->json(['status' => true, 'data' => $resa], 202);
    }


    /**
     * Encaissement
     *
     * @param id l'identifiant de la reservation 
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkPaiement($reference) : \Illuminate\Http\JsonResponse
    {
        //"https://www.silouso.fr/encaissementreturn?checkoutIntentId=28963&code=succeeded&orderId=27479"


       

        $paiement = new Paiement($reference);      

        if ($paiement->needCheckHA()) {

            //$this->refreshToken();
            $data = Helloasso::where('nom','=',env('HELLOASSO_KEY_ACCESS_TOKEN',''))->first();
            $accesstoken = $data->valeur;

            \Log::info(env('HELLOASSO_ENCAISSEMENT_URL','')."/".$paiement->getResa()->paiement_id);
            $haresp = Http::withToken($accesstoken)->get(env('HELLOASSO_ENCAISSEMENT_URL','')."/".$paiement->getResa()->paiement_id);
            \Log::info("Reponse : ".$haresp->status());

            if ($haresp->status() == 200) {
                \Log::info($haresp->body());

                if ($paiement->checkHA($haresp->json())) {
                    return response()->json(['status' => true, 'data' => $paiement->getResa()]);
                } else {
                    return response()->json(['status' => false, 'data' => $paiement->getLastErreur()]);
                }
        
            } else {
                \Log::info("Erreur helloasso");
                \Log::info($haresp->status());
                \Log::info($haresp->body());
                return response()->json(['status' => false, 'data' => "Erreur communication helloasso"]);
            }
        
        } else if ($paiement->checkCash()) {
            return response()->json(['status' => true, 'data' => $paiement->getResa()]);  

        } else {
            return response()->json(['status' => false, 'data' => $paiement->getLastErreur()]);  
        }        

}    



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
       
        return response()->json(['status' => true, 'data' => "Not implemented"], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id): \Illuminate\Http\JsonResponse
    {
        return response()->json("Not implemented", 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        return response()->json(['status' => true, 'data' => "Not implemented"], 202);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function destroy(int $id): \Illuminate\Http\JsonResponse
    {
        return response()->json(['status' => true, 'message' => 'Not implemented'],200);
    }



    /**
     * Create the initial Token from helloasso
     * 
     */
    private function initToken()
    {
        $data = Helloasso::where('nom','=',env('HELLOASSO_KEY_CLIENT_ID',''))->first();
        $clientId = $data->valeur;

        $data = Helloasso::where('nom','=',env('HELLOASSO_KEY_CLIENT_SECRET',''))->first();
        $clientSecret = $data->valeur;
        
        $details = ['grant_type' => 'client_credentials',
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret];
        try {
            \Log::info("Init Token");
            \Log::info($details);
            \Log::info(env('HELLOASSO_AUTH_URL',''));
            $response = Http::asForm()->post(env('HELLOASSO_AUTH_URL',''), $details);
        
            \Log::info("réponse : ".$response->status());
            \Log::info($response->body());

            if ($response->ok()) {
                $token = $response->json();
                $data = Helloasso::where('nom','=',env('HELLOASSO_KEY_ACCESS_TOKEN',''))->first();
                $data->valeur = $token['access_token'];
                $data->update();
                $data = Helloasso::where('nom','=',env('HELLOASSO_KEY_REFRESH_TOKEN',''))->first();
                $data->valeur = $token['refresh_token'];
                $data->update();
            }
            else {
                echo 'Unexpected HTTP status: ' . $response->status() . ' ' . $response->body();
            }
        }
        catch(\HTTP_Request2_Exception $e)
        {
            echo 'Error: ' . $e->getMessage();
        }
    }

    /**
     * Refresh the current Token from helloasso
     * 
     */    
    private function refreshToken()
    {
        $data = Helloasso::where('nom','=',env('HELLOASSO_KEY_CLIENT_ID',''))->first();
        $clientId = $data->valeur;

        $data = Helloasso::where('nom','=',env('HELLOASSO_KEY_REFRESH_TOKEN',''))->first();
        $refreshtoken = $data->valeur;

        $details = ['grant_type' => 'refresh_token',
                    'client_id' => $clientId,
                    'refresh_token' => $refreshtoken];
        try {
            $response = Http::asForm()->post(env('HELLOASSO_AUTH_URL',''), $details);
        
            \Log::info("réponse : ".$response->status());
            \Log::info($response->body());

            if ($response->ok()) {
                $token = $response->json();
                $data = Helloasso::where('nom','=',env('HELLOASSO_KEY_ACCESS_TOKEN',''))->first();
                $data->valeur = $token['access_token'];
                $data->update();
                $data = Helloasso::where('nom','=',env('HELLOASSO_KEY_REFRESH_TOKEN',''))->first();
                $data->valeur = $token['refresh_token'];
                $data->update();
            }
            else {
                echo 'Unexpected HTTP status: ' . $response->status() . ' ' . $response->body();
            }
        }
        catch(\HTTP_Request2_Exception $e)
        {
            echo 'Error: ' . $e->getMessage();
        }
    }


}
