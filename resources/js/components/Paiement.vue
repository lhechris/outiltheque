<template>
    <div class="flex flex-col">
        <p>Page Paiement de la réservation {{ resa.id }}</p>
        <button type="button" 
                class="rounded-lg px-5 py-2.5 text-center text-sm font-medium text-white shadow-sm transition-all hover:border-blue-700 focus:ring focus:ring-blue-200 disabled:cursor-not-allowed disabled:border-blue-300 disabled:bg-blue-300"
                @click="handleHA"
        >
        <img src="/storage/app/images/6337955-payer-avec-helloasso.svg" />
     </button>

     <button type="button" 
                class="bg-blue-500 hover:bg-blue-900 text-white font-bold py-2 px-4 rounded"
                @click="handleCash"
        >
        Payer plus tard
     </button>

     <button type="button" 
                class="bg-red-500 hover:bg-blue-900 text-white font-bold py-2 px-4 rounded"
                @click="handleCancel"
        >
        Annuler la réservation
     </button>



    </div>
</template>

<script setup>

    const props = defineProps(['resa'])
    const emits = defineEmits(['onHA', 'onCash','onCancel'])

    function handleCash() {
        emits('onCash')
    }
    function handleHA() {
        emits('onHA')
    }
    function handleCancel() {
        emits('onCancel')
    }
</script>