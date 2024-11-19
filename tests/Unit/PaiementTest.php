<?php

namespace Tests\Unit;

//use PHPUnit\Framework\TestCase;
use Tests\TestCase;

use App\Models\Paiement;
use App\Models\Reservations;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Mockery;
use Mockery\MockInterface;
use App\Models\ConfirmResa;

class PaiementTest extends TestCase
{

    use RefreshDatabase;


    /**
     * Test que l'on doit verifier l'encaissement sur helloasso
     */
    public function test_needCheckHa_true(): void
    {
        $resa = Reservations::factory()->create((['paiement_state'=> Reservations::PAIEMENT_STATE_HA_ENCOURS]));        
        $paiement = new Paiement($resa->reference); 
        $this->assertTrue($paiement->needCheckHA());
    }

    /**
     * Test que l'on ne doit pas verifier l'encaissement sur helloasso (paiement en cash)
     */
    public function test_needCheckHa_false(): void
    {
        $resa = Reservations::factory()->create((['paiement_state'=> Reservations::PAIEMENT_STATE_A_PAYER]));        
        $paiement = new Paiement($resa->reference);        
        $this->assertFalse($paiement->needCheckHA());
    }

    /**
     * Test needCheckHA avec une reservation qui n'existe pas
     */
    public function test_needCheckHa_unknown_resa(): void
    {
        $paiement = new Paiement(2);        
        $this->assertFalse($paiement->needCheckHA());
    }


    /**
     * Test la vérification d'un paiement cache. L'etat doit passer à confirmé et un mail est envoyé
     */
    public function test_checkCash() :void 
    {
        $resa = Reservations::factory()->create((['paiement_state'=> Reservations::PAIEMENT_STATE_A_PAYER,
                                                  'state' => Reservations::STATE_PAIEMENT]));        
        $paiement = new Paiement($resa->reference);
        
        $this->assertTrue($paiement->checkCash());
        $this->assertEquals(Reservations::STATE_CONFIRME,$paiement->getResa()->state);
        $this->assertEquals("Succès",$paiement->getLastError());
    }


    /**
     * Test la vérification d'un paiement cache mais le status n'est pas "A payer"
     * On retourne true mais le status de la reservation n'a pas été modifié et le mail 
     * n'a pas été envoyé.
     */
    public function test_checkCash_avec_un_status_non_prevue() :void 
    {
        $resa = Reservations::factory()->create((['paiement_state'=> Reservations::PAIEMENT_STATE_NON_PAYE,
                                                  'state' => Reservations::STATE_PAIEMENT]));        
        $paiement = new Paiement($resa->reference);
        $this->assertTrue($paiement->checkCash());
        $this->assertEquals(Reservations::STATE_PAIEMENT,$paiement->getResa()->state);
        $this->assertEquals("Succès",$paiement->getLastError());
    }


    /**
     * Test si l'encaissement helloasso est bon
     */
    public function test_checkHa_ok() : void
    {
        $str= '{"order":
                  {"payer": {"email":"lhechris@gmail.com","country":"FRA","firstName":"Rafa","lastName":"Nadal"},
                   "items":[{"payments":[{"id":19609,"shareAmount":200}],"name":"Location outil Nadal","priceCategory":"Fixed","qrCode":"MjkxOTM6NjM4NjUxMjcwNjkyOTcyNTEw","id":29193,"amount":200,"type":"Payment","state":"Processed"}],
                   "payments":[{"items":[{"id":29193,"shareAmount":200,"shareItemAmount":200}],"cashOutState":"MoneyIn","paymentReceiptUrl":"https://www.helloasso-sandbox.com/associations/labobinette/checkout/paiement-attestation/29193","id":19609,"amount":200,"date":"2024-10-21T17:04:55.3020839+02:00","paymentMeans":"Card","installmentNumber":1,"state":"Authorized","meta":{"createdAt":"2024-10-21T17:04:29.297251+02:00","updatedAt":"2024-10-21T17:04:55.3533333+02:00"},"refundOperations":[]}],"amount":{"total":200,"vat":0,"discount":0},"id":29193,"date":"2024-10-21T17:04:55.3020839+02:00","formSlug":"default","formType":"Checkout","organizationName":"labobinette","organizationSlug":"labobinette","organizationType":"Association1901Rig","organizationIsUnderColucheLaw":false,"checkoutIntentId":30674,"meta":{"createdAt":"2024-10-21T17:04:29.297251+02:00","updatedAt":"2024-10-21T17:04:55.442711+02:00"},"isAnonymous":false,"isAmountHidden":false},
             "id":30674,
             "redirectUrl":"https://www.helloasso-sandbox.com/associations/labobinette/checkout/a5228b6e2bb74b11e0a108dcf1cc3516"}';

        $responseHA = json_decode($str,true);
        
        $resa = Reservations::factory()->create((['state'=> Reservations::STATE_PAIEMENT]));        
        $paiement = new Paiement($resa->reference);        
        $this->assertTrue($paiement->checkHA($responseHA));
        $this->assertEquals(Reservations::STATE_CONFIRME,$paiement->getResa()->state);
        $this->assertEquals(Reservations::PAIEMENT_STATE_HA_PAYE,$paiement->getResa()->paiement_state);
        $this->assertEquals("Succès",$paiement->getLastError());

    }

    /**
     * Test si l'encaissement helloasso est bon
     */
    public function test_checkHa_invalide() : void
    {
        $str= '{"order":
                  {"payer": {"email":"lhechris@gmail.com","country":"FRA","firstName":"Rafa","lastName":"Nadal"},
                   "items":[{"payments":[{"id":19609,"shareAmount":200}],"name":"Location outil Nadal","priceCategory":"Fixed","qrCode":"MjkxOTM6NjM4NjUxMjcwNjkyOTcyNTEw","id":29193,"amount":200,"type":"Payment","state":"Processed"}],
                   "payments":[{"items":[{"id":29193,"shareAmount":200,"shareItemAmount":200}],"cashOutState":"MoneyIn","paymentReceiptUrl":"https://www.helloasso-sandbox.com/associations/labobinette/checkout/paiement-attestation/29193","id":19609,"amount":200,"date":"2024-10-21T17:04:55.3020839+02:00","paymentMeans":"Card","installmentNumber":1,"state":"Canceled","meta":{"createdAt":"2024-10-21T17:04:29.297251+02:00","updatedAt":"2024-10-21T17:04:55.3533333+02:00"},"refundOperations":[]}],"amount":{"total":200,"vat":0,"discount":0},"id":29193,"date":"2024-10-21T17:04:55.3020839+02:00","formSlug":"default","formType":"Checkout","organizationName":"labobinette","organizationSlug":"labobinette","organizationType":"Association1901Rig","organizationIsUnderColucheLaw":false,"checkoutIntentId":30674,"meta":{"createdAt":"2024-10-21T17:04:29.297251+02:00","updatedAt":"2024-10-21T17:04:55.442711+02:00"},"isAnonymous":false,"isAmountHidden":false},
             "id":30674,
             "redirectUrl":"https://www.helloasso-sandbox.com/associations/labobinette/checkout/a5228b6e2bb74b11e0a108dcf1cc3516"}';

        $responseHA = json_decode($str,true);
        
        $resa = Reservations::factory()->create((['state'=> Reservations::STATE_PAIEMENT]));        
        $paiement = new Paiement($resa->reference);        
        $this->assertFalse($paiement->checkHA($responseHA));
        $this->assertEquals(Reservations::STATE_PAIEMENT,$paiement->getResa()->state);
        $this->assertEquals("Paiement non validé",$paiement->getLastError());

    }

    /**
     * Test si l'encaissement helloasso est bon
     */
    public function test_checkHa_non_fait() : void
    {
        $str= '{"id":30674,
             "redirectUrl":"https://www.helloasso-sandbox.com/associations/labobinette/checkout/a5228b6e2bb74b11e0a108dcf1cc3516"}';

        $responseHA = json_decode($str,true);
        
        $resa = Reservations::factory()->create((['state'=> Reservations::STATE_PAIEMENT]));        
        $paiement = new Paiement($resa->reference);        
        $this->assertFalse($paiement->checkHA($responseHA));
        $this->assertEquals(Reservations::STATE_PAIEMENT,$paiement->getResa()->state);
        $this->assertEquals("Paiement non effectué",$paiement->getLastError());

    }

    /**
     * Test si l'encaissement helloasso est bon
     */
    public function test_checkHa_reponse_non_prevue() : void
    {
        $resa = Reservations::factory()->create((['state'=> Reservations::STATE_PAIEMENT]));        
        $paiement = new Paiement($resa->reference);        
        $this->assertFalse($paiement->checkHA([]));
        $this->assertEquals(Reservations::STATE_PAIEMENT,$paiement->getResa()->state);
        $this->assertEquals("Réponse reçue non prévue",$paiement->getLastError());

    }


}
