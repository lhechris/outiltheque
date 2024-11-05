<?php

namespace App\Models;
use App\Models\Helloasso;
use App\Models\Reservations;
use App\Mail\ConfirmResa;
use Illuminate\Support\Facades\Mail;

class Paiement {

    protected $resa;
    protected $messageErreur;

    public function __construct($resaid) {
        $this->resa = Reservations::leftjoin("outils","reservations.outil_id","=","outils.id")
                            ->select("reservations.*","outils.nom as nomoutil","outils.prix")
                            ->where("reservations.id","=",$resaid)
                            ->first();

        $this->messageErreur = "";
    }


    public function getResa() { return $this->resa;}
    public function getLastError() { return $this->messageErreur;}

    /**
     * retourne si le controlleur doit effectuer une demande à helloasso
     * sinon c'est que le paiement est en cash
     */
    public function needCheckHA() {

        if (! $this->resa) { 
            return false;
        }
  
        return ($this->resa->paiement_state != Reservations::PAIEMENT_STATE_A_PAYER) ;
        
    }


    public function checkCash() {

        if (! $this->resa) { 
            $this->messageErreur = "Pas de réservation";
            return false;
        }

        \Log::info("Verification paiement :[".$this->resa->id.'] '.$this->resa['nomoutil'].' pour '.$this->resa->nom.' '.$this->resa->email);                            

        if ($this->resa->paiement_state == Reservations::PAIEMENT_STATE_A_PAYER) {
            \Log::info("Paiement cash");
            if ($this->resa->state==Reservations::STATE_PAIEMENT) {
                \Log::info("On envoi le mail a ".$this->resa->email);
                \Log::info("Status = ".Reservations::STATE_CONFIRME." Paiement status=".Reservations::PAIEMENT_STATE_A_PAYER);
                Mail::to($this->resa->email)->send(new ConfirmResa($this->resa));
                $this->resa->state=Reservations::STATE_CONFIRME;
                $this->resa->update();
            }
        }
        $this->messageErreur = "Succès";
        return true;
    }


    public function checkHA($responseHA) {

        if (! $this->resa) { 
            $this->messageErreur = "Pas de réservation";
            return false;
        }

            //Analyse de la réponse
            /**
             * {"order":
             *      {"payer": {"email":"lhechris@gmail.com","country":"FRA","firstName":"Rafa","lastName":"Nadal"},
             *       "items":[{"payments":[{"id":19609,"shareAmount":200}],"name":"Location outil Nadal","priceCategory":"Fixed","qrCode":"MjkxOTM6NjM4NjUxMjcwNjkyOTcyNTEw","id":29193,"amount":200,"type":"Payment","state":"Processed"}],
             *       "payments":[{"items":[{"id":29193,"shareAmount":200,"shareItemAmount":200}],"cashOutState":"MoneyIn","paymentReceiptUrl":"https://www.helloasso-sandbox.com/associations/labobinette/checkout/paiement-attestation/29193","id":19609,"amount":200,"date":"2024-10-21T17:04:55.3020839+02:00","paymentMeans":"Card","installmentNumber":1,"state":"Authorized","meta":{"createdAt":"2024-10-21T17:04:29.297251+02:00","updatedAt":"2024-10-21T17:04:55.3533333+02:00"},"refundOperations":[]}],"amount":{"total":200,"vat":0,"discount":0},"id":29193,"date":"2024-10-21T17:04:55.3020839+02:00","formSlug":"default","formType":"Checkout","organizationName":"labobinette","organizationSlug":"labobinette","organizationType":"Association1901Rig","organizationIsUnderColucheLaw":false,"checkoutIntentId":30674,"meta":{"createdAt":"2024-10-21T17:04:29.297251+02:00","updatedAt":"2024-10-21T17:04:55.442711+02:00"},"isAnonymous":false,"isAmountHidden":false},
             * "id":30674,
             * "redirectUrl":"https://www.helloasso-sandbox.com/associations/labobinette/checkout/a5228b6e2bb74b11e0a108dcf1cc3516"}
             */

            if (array_key_exists("id",$responseHA) && array_key_exists("redirectUrl",$responseHA)) {
                if (array_key_exists("order",$responseHA)) {
                    $paiementok = false;

                    //est ce que l'ordre à été passé ?
                    $order = $responseHA['order'];
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
                    if ($paiementok) {

                        if ($this->resa->state==Reservations::STATE_PAIEMENT) {
                            \Log::info("On envoi le mail a ".$this->resa->email);
                            \Log::info("Status = ".Reservations::STATE_CONFIRME." Paiement status=".Reservations::PAIEMENT_STATE_HA_PAYE);
                            $this->resa->paiement_state = Reservations::PAIEMENT_STATE_HA_PAYE;
                            $this->resa->state=Reservations::STATE_CONFIRME;                
                            $this->resa->update();
                            //Envoi du mail
                            Mail::to($this->resa->email)->send(new ConfirmResa($this->resa));
                        }
                        $this->messageErreur = "Succès";
                        return true;  
                    } else {
                        $this->messageErreur = "Paiement non validé";
                        return false;  
                    }       
                    
                
                } else {
                    //La demande n'a pas été finalisé, il faudrait la renvoyer
                    $this->messageErreur = "Paiement non effectué";
                    return false;
                }
            }
            $this->messageErreur = "Réponse reçue non prévue";
            return false;        
    }



}