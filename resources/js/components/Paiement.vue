<template>
    <div>
        <p>Page Paiement de la réservation {{ resa.id }}</p>
        <button type="button" 
                class="rounded-lg px-5 py-2.5 text-center text-sm font-medium text-white shadow-sm transition-all hover:border-blue-700 focus:ring focus:ring-blue-200 disabled:cursor-not-allowed disabled:border-blue-300 disabled:bg-blue-300"
                @click="handleClick"
        >
        <img src="/storage/app/images/6337955-payer-avec-helloasso.svg" />
     </button>

    </div>
</template>

<script setup>

    const props = defineProps(['resa'])
    const emits = defineEmits(['onValid', 'onCancel'])

    function handleClick() {
        emits('onValid')
    }

</script>