<?php

namespace App\Http\Controllers;

use App\Models\Helloasso;
use App\Models\Reservations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

use App\Mail\ConfirmResa;
use Illuminate\Support\Facades\Mail;

class HelloassoController extends Controller
{

/*     private $authurl = 'https://api.helloasso.com/oauth2/token';
    private $encaissementurl = 'https://api.helloasso.com/v5/organizations/labo-binette/checkout-intents';
    private $keyClientId = 'client_id';
    private $keyClientSecret = 'client_secret';
    private $keyAccessToken = 'access_token';
    private $keyRefreshToken = 'refresh_token';  */

    //MODE TEST
    private $authurl = 'https://api.helloasso-sandbox.com/oauth2/token';
    private $encaissementurl = 'https://api.helloasso-sandbox.com/v5/organizations/labobinette/checkout-intents';
    private $keyClientId = 'Testclient_id';
    private $keyClientSecret = 'Testclient_secret';
    private $keyAccessToken = 'Testaccess_token';
    private $keyRefreshToken = 'Testrefresh_token'; 


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
 
        $this->refreshToken();
        //$this->initToken();
        $data = Helloasso::get();        
        return response()->json(['status' => true, 'data' => $data]);
    }


    /**
     * Encaissement
     *
     * @param id l'identifiant de la reservation 
     * @return \Illuminate\Http\JsonResponse
     */
    public function encaissement($id) : \Illuminate\Http\JsonResponse
    {

        $resa = Reservations::leftjoin("outils","reservations.outil_id","=","outils.id")
                            ->select("reservations.*","outils.nom as nomoutil","outils.prix")
                            ->where("reservations.id","=",$id)
                            ->first();


        $details= [
            "totalAmount" => $resa->prix*100,
            "initialAmount" => $resa->prix*100,
            "itemName" => "Location outil ".$resa['nom'],
            "backUrl" => "https://outiltheque.labo-binette.fr/reservation/".$resa->id,
            "errorUrl" => "https://outiltheque.labo-binette.fr/encaissementerreur".$resa->id,
            "returnUrl" => "https://outiltheque.labo-binette.fr/confirmation/".$resa->id,
            "containsDonation" => false,
            "payer"=> [
              "firstName"=> $resa->prenom,
              "lastName"=> $resa->nom,
              "email"=> $resa->email,
              "country"=> "FRA"
            ]            
        ];

        $this->refreshToken();
        $data = Helloasso::where('nom','=',$this->keyAccessToken)->first();
        $accesstoken = $data->valeur;

        $haresp = Http::withToken($accesstoken)->post($this->encaissementurl, $details);
        
        if ($haresp->status() == 200) {
            \Log::info("OK helloasso");
            \Log::info($haresp->status());
            \Log::info($haresp->body());
            //store status
            $resa->paiement_state = Reservations::PAIEMENT_STATE_HA_ENCOURS;
            $resa->state = Reservations::STATE_PAIEMENT;
            $resa->paiement_id = $haresp->json()["id"];
            $resa->update();

            return response()->json(['status' => true, 'data' => $haresp->json()]);
        
        } else {
            \Log::info("Erreur helloasso");
            \Log::info($haresp->status());
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
    public function cash(Request $request, $id) : \Illuminate\Http\JsonResponse
    {
        $resa = Reservations::find($id);

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
    public function checkPaiement($id) : \Illuminate\Http\JsonResponse
    {
        //"https://www.silouso.fr/encaissementreturn?checkoutIntentId=28963&code=succeeded&orderId=27479"

        //$this->refreshToken();
        $data = Helloasso::where('nom','=',$this->keyAccessToken)->first();
        $accesstoken = $data->valeur;

        $resa = Reservations::leftjoin("outils","reservations.outil_id","=","outils.id")
                            ->select("reservations.*","outils.nom as nomoutil","outils.prix")
                            ->where("reservations.id","=",$id)
                            ->first();
                            
        if ($resa->paiement_state == Reservations::PAIEMENT_STATE_A_PAYER) {
            if ($resa->state==Reservations::STATE_PAIEMENT) {
                Mail::to($resa->email)->send(new ConfirmResa($resa));
                $resa->state=Reservations::STATE_CONFIRME;
                $resa->update();
            }
            return response()->json(['status' => true, 'data' => $resa]);            
        }


        $haresp = Http::withToken($accesstoken)->get($this->encaissementurl."/".$resa->paiement_id);
        
        if ($haresp->status() == 200) {
            \Log::info($haresp->body());
            if ($resa->state==Reservations::STATE_PAIEMENT) {
                $resa->paiement_state = Reservations::PAIEMENT_STATE_HA_PAYE;
                $resa->state=Reservations::STATE_CONFIRME;
                $resa->update();
                //Envoi du mail
                Mail::to($resa->email)->send(new ConfirmResa($resa));
            }
            return response()->json(['status' => true, 'data' => $resa]);
        
        } else {
            \Log::info("Erreur helloasso");
            \Log::info($haresp->status());
            \Log::info($haresp->body());
            return response()->json(['status' => false, 'data' => "Erreur communication helloasso"]);
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
        $data = Helloasso::where('nom','=',$this->keyClientId)->first();
        $clientId = $data->valeur;

        $data = Helloasso::where('nom','=',$this->keyClientSecret)->first();
        $clientSecret = $data->valeur;
        
        $details = ['grant_type' => 'client_credentials',
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret];
        try {
            
            \Log::info($details);
            \Log::info($this->authurl);
            $response = Http::asForm()->post($this->authurl, $details);
        
            \Log::info("réponse : ".$response->status());
            \Log::info($response->body());

            if ($response->ok()) {
                $token = $response->json();
                $data = Helloasso::where('nom','=',$this->keyAccessToken)->first();
                $data->valeur = $token['access_token'];
                $data->update();
                $data = Helloasso::where('nom','=',$this->keyRefreshToken)->first();
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
        $data = Helloasso::where('nom','=',$this->keyClientId)->first();
        $clientId = $data->valeur;

        $data = Helloasso::where('nom','=',$this->keyRefreshToken)->first();
        $refreshtoken = $data->valeur;
        
        $details = ['grant_type' => 'refresh_token',
                    'client_id' => $clientId,
                    'refresh_token' => $refreshtoken];
        try {
            $response = Http::asForm()->post($this->authurl, $details);
        
            \Log::info("réponse : ".$response->status());
            \Log::info($response->body());

            if ($response->ok()) {
                $token = $response->json();
                $data = Helloasso::where('nom','=',$this->keyAccessToken)->first();
                $data->valeur = $token['access_token'];
                $data->update();
                $data = Helloasso::where('nom','=',$this->keyRefreshToken)->first();
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
