<?php

namespace App\Services\Helloasso;

use App\Models\Parameter;
use Illuminate\Support\Facades\Http;

class Token {
    public static function refresh()
    {
        $data = Parameter::where('name','=',env('HELLOASSO_KEY_CLIENT_ID',''))->first();
        $clientId = $data->val;

        $data = Parameter::where('name','=',env('HELLOASSO_KEY_REFRESH_TOKEN',''))->first();
        $refreshtoken = $data->val;

        $details = ['grant_type' => 'refresh_token',
                    'client_id' => $clientId,
                    'refresh_token' => $refreshtoken];
        try {
            $response = Http::asForm()->post(env('HELLOASSO_AUTH_URL',''), $details);
            \Log::debug(env('HELLOASSO_AUTH_URL',''));
            \Log::debug($details);
            \Log::debug("Reponse : ".$response->status());
            \Log::debug($response->body());
        
            if ($response->ok()) {
                \Log::info("Helloasso refresh token is ok. Replace new token");

                $token = $response->json();
                $data = Parameter::where('name','=',env('HELLOASSO_KEY_ACCESS_TOKEN',''))->first();
                $data->val = $token['access_token'];
                $data->update();
                $data = Parameter::where('name','=',env('HELLOASSO_KEY_REFRESH_TOKEN',''))->first();
                $data->val = $token['refresh_token'];
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


    public static function init()
    {
        $data = Parameter::where('name','=',env('HELLOASSO_KEY_CLIENT_ID',''))->first();
        $clientId = $data->val;

        $data = Parameter::where('name','=',env('HELLOASSO_KEY_CLIENT_SECRET',''))->first();
        $clientSecret = $data->val;
        
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
                $data = Parameter::where('name','=',env('HELLOASSO_KEY_ACCESS_TOKEN',''))->first();
                $data->val = $token['access_token'];
                $data->update();
                $data = Parameter::where('name','=',env('HELLOASSO_KEY_REFRESH_TOKEN',''))->first();
                $data->val = $token['refresh_token'];
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