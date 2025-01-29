<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class Helloasso extends Model
{
    use HasFactory;

    protected $table = 'helloasso';

    protected $fillable = [
        'nom',
        'valeur'
    ];


    public static function refreshToken()
    {
        $data = self::where('nom','=',env('HELLOASSO_KEY_CLIENT_ID',''))->first();
        $clientId = $data->valeur;

        $data = self::where('nom','=',env('HELLOASSO_KEY_REFRESH_TOKEN',''))->first();
        $refreshtoken = $data->valeur;

        $details = ['grant_type' => 'refresh_token',
                    'client_id' => $clientId,
                    'refresh_token' => $refreshtoken];
        try {
            $response = Http::asForm()->post(env('HELLOASSO_AUTH_URL',''), $details);
        
            if ($response->ok()) {
                \Log::info("Helloasso refresh token is ok. Replace new token");

                $token = $response->json();
                $data = self::where('nom','=',env('HELLOASSO_KEY_ACCESS_TOKEN',''))->first();
                $data->valeur = $token['access_token'];
                $data->update();
                $data = self::where('nom','=',env('HELLOASSO_KEY_REFRESH_TOKEN',''))->first();
                $data->valeur = $token['refresh_token'];
                $data->update();
            }
            else {
                \Log::info("POST : ".env('HELLOASSO_AUTH_URL',''));
                echo 'Unexpected HTTP status: ' . $response->status() . ' ' . $response->body();
                \Log::info("réponse : ".$response->status());
                \Log::info($response->body());
            }
        }
        catch(\HTTP_Request2_Exception $e)
        {
            echo 'Error: ' . $e->getMessage();
        }
    }



}