<template>
    <div class="mx-auto max-w-md rounded-lg bg-white shadow">
  <div class="p-4">
    <div v-if="resa">
        <h3 class="text-xl font-medium text-gray-900">Liste de vos réservations en cours</h3>
        <p v-for="r in resa">{{ r.nom }} à partir du {{ displaydate(r.debut) }} au {{ displaydate(r.fin) }} </p>
    </div>
    <h3 v-else class="text-xl font-medium text-gray-900">Pas de réservations en cours</h3>
  </div>
</div>
</template>

<script>
import { request } from '../helper'
import {ref, onMounted} from 'vue'
import moment from 'moment'
import 'moment/dist/locale/fr';

export default {
    props: ["user"],
    setup(props) {
        const resa=ref()


        onMounted(() => {
            handleReservations()
        });

        function displaydate(d) {
            var momentfr=moment(d)
            momentfr.locale('fr')
            return momentfr.format("D MMMM")
        }

        const handleReservations = async () => {
            try {
                const req = await request('get', '/api/reservations')
                if (req.data.data.length==0) {
                    resa.value = null
                } else {
                    resa.value = req.data.data
                }

            } catch (e) {
                
            }
        }

        return {
            resa, displaydate
        }
    }
}

</script>