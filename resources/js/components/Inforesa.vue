<template>
    <div v-if="outil" class="flex flex-col justify-start gap-4 max-w-sm sm:max-xl md:max-w-2xl lg:max-w-4xl">     
        <FicheOutil :value="props.outil" />
        <div class="flex flex-row gap-4 py-4" >
            <div>
                <label for="debut" class="mb-1 block text-sm font-medium text-gray-700">Date de récupération</label>
                <select id="selectdebut" 
                        class="px-4 py-2 rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:cursor-not-allowed bg-blue-50"
                        v-model="datedebut"
                        @change="changedebut"
                        >
                            <option v-for="l in ldebut" :value="l">{{ moment(l).format('DD/MM') }}</option>
                        </select>

            </div>
            <div>
                <label for="fin" class="mb-1 block text-sm font-medium text-gray-700">Date de retour </label>
                <label  
                        class="px-4 py-2 block border rounded-md border-gray-300 shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500" 
                        >{{ moment(fin).format('DD/MM') }}</label>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 py-4" >
            <div>
                <label for="nom" class="mb-1 block text-sm font-medium text-gray-700">Nom</label>
                <input  type="text" 
                        id="nom" 
                        v-model="nom" 
                        class="block border rounded-md border-gray-300 shadow-sm focus:border-blue-400 pl-1 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500" 
                        placeholder="Votre prénom et nom" />
            </div>
            <div>
                <label for="nom" class="mb-1 block text-sm font-medium text-gray-700">Téléphone</label>
                <input  type="text" 
                        id="telephone" 
                        v-model="telephone" 
                        class="block border rounded-md border-gray-300 shadow-sm focus:border-blue-400 pl-1 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500" 
                        placeholder="Numéro de téléphone" />
            </div>
            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-gray-700">Email</label>
                <input  type="email" 
                        id="email" 
                        v-model="email" 
                        class="block border rounded-md border-gray-300 shadow-sm focus:border-blue-400 pl-1 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500" 
                        placeholder="Votre Email" />
            </div>       
        </div>
    </div>
    <div v-else>Rien</div>

</template>
<script setup>
    import FicheOutil from './FicheOutil.vue';
    import moment from 'moment'
    import {ref,onMounted} from 'vue'
    

    let dec=0
    if (moment().day() >=3 ) { dec = 7}
    let mindebut = moment().startOf('isoweek').add(3+dec,'days')
    const minfin = ref(moment().startOf('isoweek').add(9+dec,'days').format("YYYY-MM-DD"))

    let ld = [mindebut.format("YYYY-MM-DD")]
    
    for (let i=0;i<8;i++) {
        ld.push(mindebut.add(7,'days').format("YYYY-MM-DD"))
    }
    const ldebut = ref(ld)

    const debut = defineModel('debut')
    const fin = defineModel('fin')

    const nom = defineModel('nom')
    const telephone = defineModel("telephone")
    const email = defineModel('email')

    const props = defineProps(['outil'])

    const datedebut = ref(ldebut.value[0])

    onMounted(() => {
        debut.value = ldebut.value[0]
        fin.value = minfin.value
        nom.value = ""
        telephone.value=""
        email.value = ""
    });

    function changedebut() {
        console.log(datedebut.value)
        debut.value = datedebut.value
        fin.value = moment(datedebut.value).add(6, 'days').format("YYYY-MM-DD")

        console.log(fin.value)
        
    }

</script>