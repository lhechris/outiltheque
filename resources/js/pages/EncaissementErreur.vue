<template>
    <enveloppe>
        <div class="grid justify-center gap-5 py-4" >
            <h1 >Erreur encaissement</h1>        
            <div v-if="resa">
                <p>Code réservation : {{ resa.id }}</p>
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
    </enveloppe>
</template>

<script setup>
import {useRoute,useRouter} from "vue-router";
import {onMounted,ref} from "vue"
import {request} from '../helper.js'
import Enveloppe from '../components/Enveloppe.vue'

const props=defineProps(['resaid'])

const resa = ref()


onMounted(() => {
        //handleResa()
});

let route = useRoute();
let router = useRouter();

const handleResa = async () => {
        
        const url = '/api/reservations/'+props.resaid
        try {
            
            const req = await request('get', url)
            if (req.status == 200 ){
                resa.value = req.data.data
            }

        } catch (e) {
            
        }
    }

    function Annuler() {
        
        try {
            
            const req = await request('delete', '/api/reservations/'+props.resaid)
            if (req.status == 200 ){
                router.push("/")
            }

        } catch (e) {
            
        }
    }
    
    function Recommencer() {
        await handleResa()
        if (resa) {
            router.push("/")
        } else {
            router.push("/")
        }
        
        
    }

console.log(route.query)

</script>
