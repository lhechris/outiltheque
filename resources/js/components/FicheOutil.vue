<template>
    <div class="flex flex-col gap-4 ">
        <div class="flex flex-row gap-4">
            <div class="w-32 h-32">
                <img src="/storage/app/images/LB_logo.png" />
            </div>
            <div class="w-full"> 
                <div class="text-[#1b716c] text-5xl" >{{ value.nom }}</div>
                <div class="border border-[#1b716c]"> </div>
                <div class="max-w-xl">{{ value.description }}</div>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <img class="w-24" :src="value.file_path" alt="Photo manquante" />
            <div class="flex flex-col">
                <div class="max-w-32"><img :src="value.file2_path" alt="Photo manquante" /></div>
                <div v-for="(c,idx) in value.caracteristique" >
                    <div :class="{'bg-[#ebe5d1]' : (idx+1)%2}">{{ c.nom }} : {{ c.valeur }}</div>
                </div>
            </div>
        </div>
        <div class="w-full bg-[#ebe5d1] mb-1 mx-2">
            <div class="bg-[#1b716c] text-sm text-center py-2">CONSEIL D'UTILISATION</div>
            <div class="py-2">{{value.conseil}}</div>
        </div>
        <div class="w-full bg-[#ebe5d1] mb-1 mx-2">
            <div class="bg-[#e74F10] text-sm text-center py-2">PRECAUTION</div>
            <div class="py-2">{{value.precaution}}</div>
        </div>

    </div>
</template>

<script setup>
    const props = defineProps({
    value:Object}
    )
</script>