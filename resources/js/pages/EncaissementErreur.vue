<template>
    <enveloppe>
        <div class="grid justify-center gap-5 py-4" >
            <div v-if="affichepaiement" >
                <paiement 
                    :resa="resa" 
                    @onHA="pHA" 
                    @onCash="pCash" 
                    @onCancel="pCancel">
                </paiement>
            </div>
            <div v-else>
                <h1 >Erreur encaissement</h1>        
                <div v-if="resa">
                    <p>Code réservation : {{ resa.reference }}</p>
                    <p>Date d'emprunt : {{ resa.debut }}</p>
                    <button class="bg-blue-500 hover:bg-blue-900 text-white font-bold py-2 px-4 rounded" 
                                id="confirm" 
                                type="button"
                                @click="Recommencer"
                        >Recommencer</button>
                        <button class="bg-blue-500 hover:bg-blue-900 text-white font-bold py-2 px-4 rounded" 
                                id="confirm" 
                                type="button"
                                @click="Annuler"
                        >Annuler</button>
                </div>
                <div v-else>
                    <img src="/storage/app/images/icons8-iphone-spinner.gif" />
                </div>
            </div>
        </div>
    </enveloppe>
</template>

<script setup>
    import {useRouter} from "vue-router";
    import {onMounted,ref} from "vue"
    import {request} from '../helper.js'
    import Enveloppe from '../components/Enveloppe.vue'
    import Paiement from "../components/Paiement.vue";
    import {paiementHA, paiementCash,paiementCancel} from '../paiement'

    const props=defineProps(['resaid'])

    const resa = ref()
    const affichepaiement = ref(false)

    const message = ref()
    const erreur = ref()

    onMounted(() => {
            handleResa()
    });

    let router = useRouter();

    const handleResa = async () => {
        
        const url = '/api/reservations/'+props.resaid
        try {            
            const req = await request('get', url)
            if (req.status == 200 ){
                resa.value = req.data
            }

        } catch (e) {
            console.log(e)
        }
    }

    const Annuler = async() =>  {
        
        try {            
            const req = await request('delete', '/api/reservations/'+props.resaid)
            if (req.status == 200 ){
                router.push("/home")
            }

        } catch (e) {
            console.log(e)
        }
    }
    
    const Recommencer= async() =>{        
        affichepaiement.value = true       
    }

    function pHA() {
        erreur.value = null
        message.value = null
        paiementHA(resa.value.reference)
            .then(result => {
                console.log("Redirection vers :" + result)
                message.value = "Paiement ok"                
                window.location.href = result                
            
            }).catch(err => {
                console.log(err)
                erreur.value = err.message
            })

        affichepaiement.value = false
    }

    function pCash() {
        erreur.value = null
        message.value = null
        paiementCash(resa.value.reference,message.value,erreur.value)
        .then(() => {
            router.push("/home")    
        }).catch(err => {
            console.log(err)
            erreur.value = err.message
        })
        affichepaiement.value = false
    }

    function pCancel() {
        erreur.value = null
        message.value = null
        paiementCancel(resa.value.reference)
        .then(() => {
            router.push("/home")    
        }).catch(err => {
            console.log(err)
            erreur.value = err.message
        })
        affichepaiement.value = false
    }   

</script>
