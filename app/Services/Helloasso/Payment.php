<?php

namespace App\Services\Helloasso;

use App\Models\Parameter;
use App\Models\Reservation;
use Illuminate\Support\Facades\Http;

class Payment
{
    // Initialise un ordre de paiement
    public static function init(Reservation $reservation, int $amount): bool
    {

        \Log::info($reservation->reference.' Demande encaissement Helloaso '.$reservation->tool->name.' pour '.$reservation->name.' '.$reservation->email);

        $fullname = trim($reservation->name); // remove double space
        $firstname = substr($fullname, 0, strpos($fullname, ' '));
        $lastname = substr($fullname, strpos($fullname, ' '), strlen($fullname));

        $baseurl = config('app.url');
        //pour pouvoir tester en local
        if (str_contains($baseurl,'localhost') || str_contains($baseurl,'127.0.0.1')) {
            $baseurl = 'https://outiltheque.labo-binette.fr';
        }

        $details = [
            'totalAmount' => $amount * 100,
            'initialAmount' => $amount * 100,
            'itemName' => 'Location '.$reservation->reference.'('.$reservation->tool->name.')',
            'backUrl' => $baseurl.'/payments/error/'.$reservation->reference,
            'errorUrl' => $baseurl.'/payments/error/'.$reservation->reference,
            'returnUrl' => $baseurl.'/payments/confirm/'.$reservation->reference,
            'containsDonation' => false,
            'payer' => [
                'firstName' => $firstname,
                'lastName' => $lastname,
                'email' => $reservation->email,
                'country' => 'FRA',
            ],
        ];

        Token::refresh();

        $data = Parameter::where('name', '=', config('helloasso.access_token_key'))->firstOrFail();
        $accesstoken = $data->val;
        $haresp = Http::withToken($accesstoken)->post(config('helloasso.encaissement_url'), $details);
        \Log::debug(config('helloasso.encaissement_url'));
        \Log::debug($details);
        \Log::debug('Reponse : '.$haresp->status());
        \Log::debug($haresp->body());

        if ($haresp->status() == 200) {
            // La demande de paiement HA est acceptée
            // Met à jour la réservation avec les elements
            $reservation->setPendingHA($haresp->json()['id']);

            // Redirige sur la page d'encaissement HA
            redirect($haresp->json()['redirectUrl']);

            return true;

        } else {
            \Log::info('Erreur helloasso');

            return false;
        }
    }

    public static function check(Reservation $reservation): bool
    {

        $data = Parameter::where('name', '=', config('helloasso.access_token_key'))->first();
        $accesstoken = $data->val;

        $haresp = Http::withToken($accesstoken)->get(config('helloasso.encaissement_url').'/'.$reservation->payment_id);
        \Log::debug(config('helloasso.encaissement_url').'/'.$reservation->payment_id);
        // \Log::debug($accesstoken);
        \Log::debug('Reponse : '.$haresp->status());
        \Log::debug($haresp->body());

        if ($haresp->status() == 200) {
            $response = $haresp->json();
            if (array_key_exists('id', $response) && array_key_exists('redirectUrl', $response)) {
                if (array_key_exists('order', $response)) {
                    $paiementok = false;

                    // est ce que l'ordre à été passé ?
                    $order = $response['order'];
                    // on cherche si les paiements sont dans l'état Authorized
                    if ($order) {
                        if (array_key_exists('payments', $order)) {
                            foreach ($order['payments'] as $item) {
                                if (array_key_exists('state', $item)) {
                                    if ($item['state'] == 'Authorized') {
                                        $paiementok = true;
                                    }
                                }
                            }
                        }
                    }

                    return $paiementok;

                } else {
                    // La demande n'a pas été finalisé, il faudrait la renvoyer
                    \Log::info($reservation->reference.' Paiement non effectué');
                }
            } else {
                \Log::info($reservation->reference.' Réponse reçue non prévue');
            }

        } else {
            \Log::info($reservation->reference.' Erreur helloasso');
            \Log::info($haresp->status());
            \Log::info($haresp->body());
        }

        return false;
    }
}
