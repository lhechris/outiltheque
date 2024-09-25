<template>
    <enveloppe>
        <div class="grid justify-center gap-5" >
            <h1 >Réservation</h1>

            <div v-if="affichepaiement" >
                <paiement :resa="resa" @onHA="paiementHA" @onCash="paiementCash"></paiement>
            </div>
            <div v-else>
                <div v-if="outil" class="flex flex-col gap-4">
                    <info-resa :outil="outil" 
                            v-model:nom="nom"
                            v-model:prenom="prenom"
                            v-model:debut="debut"
                            v-model:fin="fin"
                            v-model:email="email"></info-resa>
                    <div class="flex flex-row gap-4 pb-10">
                        <button type="button" 
                            class="rounded-lg border border-blue-500 bg-blue-500 px-5 py-2.5 text-center text-sm font-medium text-white shadow-sm transition-all hover:border-blue-700 hover:bg-blue-700 focus:ring focus:ring-blue-200 disabled:cursor-not-allowed disabled:border-blue-300 disabled:bg-blue-300"
                            @click="reserver"
                        >Réserver</button>
                        <button type="button" 
                            class="rounded-lg border border-blue-500 bg-blue-500 px-5 py-2.5 text-center text-sm font-medium text-white shadow-sm transition-all hover:border-blue-700 hover:bg-blue-700 focus:ring focus:ring-blue-200 disabled:cursor-not-allowed disabled:border-blue-300 disabled:bg-blue-300"
                            @click="annuler"
                        >Annuler</button>
                        <div>
                            <p v-if="message" class="rounded-md bg-green-50 p-4 text-sm text-green-500">{{ message }}</p>
                            <p v-if="erreur" class="rounded-md bg-red-50 p-4 text-sm text-red-500">{{ erreur }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </enveloppe>

</template>
<script setup>
    import {ref, onMounted} from 'vue'
    import {useRouter} from "vue-router";
    import {request,validateEmail} from '../helper'

    import Enveloppe from '../components/Enveloppe.vue'
    import InfoResa from '../components/Inforesa.vue'
    import Paiement from '../components/Paiement.vue'

    const props = defineProps(['outilid'])    

    const outil = ref()
    const debut = defineModel('debut')
    const fin = defineModel('fin')

    const nom = defineModel('nom')
    const prenom = defineModel('prenom')
    const email = defineModel('email')

    const resa = ref(0)
    const checkoutid = ref()
    const redirecturl = ref()

    const message = ref()
    const erreur = ref()

    const affichepaiement = ref(false)

    let router = useRouter();
    onMounted(() => {
        handleOutils()
    });


    const reserver = async () => {

        message.value = null
        erreur.value = null
        //Verification des champs
        if (! validateEmail(email.value)) {
            erreur.value = "Email mal renseigné"
            return;
        }
        if (nom.value) {
            erreur.value = "Nom obligatoire"
            return;
        }
        if (prenom.value) {
            erreur.value = "Prénom obligatoire"
            return;
        }
        
        
        let data = {"outil_id" :outil.value.id,
                    "nom" : nom.value,
                    "prenom" : prenom.value,
                    "email" : email.value,
                    "debut" : debut.value,
                    "fin"   : fin.value}
        
        
        try {
            const req = await request('post','/api/reservations',data)
            if (req.data && req.data.status) {
                resa.value = req.data.data
                affichepaiement.value = true
                erreur.value = null
                message.value = "Article réservé"

            } else {
                erreur.value = req.data.message
                message.value = null
            }
        } catch(e) {
            erreur.value = e.message
            message.value = null
        }

    }

    const paiementHA = async () => {
        try {
            const req = await request('get', '/api/encaissement/'+resa.value.id)

            if (req.status == 200) {
                checkoutid.value = req.data.data.id
                redirecturl.value = req.data.data.redirectUrl

                message.value = "Paiement encours"
                erreur.value = null

                window.location.href = redirecturl.value

            } else {
                message.value = null
                erreur.value = req.data.message
            }

        } catch (e) {
            erreur.value = e.message
            message.value = null
        }
        
        affichepaiement.value = false
    }

    const paiementCash = async () => {
        try {
            const req = await request('put', '/api/cash/'+resa.value.id)

            if (req.status == 202) {
                message.value = "Paiement à la livraison"
                erreur.value = null
                router.push("/confirmation/"+resa.value.id)

             } else {
                message.value = null
                erreur.value = req.data.message
            }

        } catch (e) {
            erreur.value = e.message
            message.value = null
        }
        
        affichepaiement.value = false
    }


    function annuler() {
        router.push('/')
    }

    const handleOutils = async () => {
        
        const url = '/api/outils/'+props.outilid
        //const url = '/api/outils/1'

        try {
            
            const req = await request('get', url)
            outil.value = req.data

        } catch (e) {
            //await router.push('/login')
        }
    }



</script>
