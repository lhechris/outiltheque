<template>

<div class="md:flex items-strech py-8 md:py-10 lg:py-8 border-t border-white-50">
    <div class="md:w-4/12 2xl:w-1/4 w-full">
        <img :src="value.file_path" alt="Black Leather Bag" class="h-full object-center object-cover md:block hidden" />
        <img src="https://i.ibb.co/g9xsdCM/Rectangle-37.pngg" alt="Black Leather Bag" class="md:hidden w-full h-full object-center object-cover" />
    </div>
    <div class="md:pl-3 md:w-8/12 2xl:w-3/4 flex flex-col justify-center">
        <div class="flex items-center justify-between w-full pt-1">
            <p class="text-xl font-black leading-none text-gray-800 dark:text-white">{{value.nom}}</p>
        </div>
        <p class="text-xl leading-3 text-gray-600 dark:text-white pt-2">{{value.description}}</p>
        <p class="text-xl leading-3 text-gray-600 dark:text-white py-4">Prix: {{value.prix}}</p>
        <p class="w-96 text-xl leading-3 text-gray-600 dark:text-white">Nombre restant: {{ value.nombre }}</p>
    </div>
</div>
<button id="reservation" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    <p>Je réserve</p>
                </button>
</template>

<script>

export default {
    
    props : ['value'],

    setup() {

        return {
        }
    },
}
</script>
