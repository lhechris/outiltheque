<template>
    <div class="flex flex-col gap-4 font-['Arimo']">
        <div class="flex flex-row gap-4">
            <div class="size-32">
                <img src="/storage/app/images/LB_logo.png" />
            </div>
            <div class="w-full"> 
                <div class="text-[#1b716c] text-5xl font-bold font-['Comfortaa']" >{{ value.nom }}</div>
                <div class="border border-[#1b716c]"> </div>
                <div class="max-w-xl">{{ value.description }}</div>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <img class="h-60" :src="value.file_path" alt="" />
            <div class="flex flex-col">
                <div><img class="h-52" :src="value.file2_path" alt="" /></div>
                <table>
                    <tr v-for="(c,idx) in value.caracteristique" :class="{'bg-[#ebe5d1]' : (idx+1)%2}">
                    <td><b>{{ c.nom }}</b></td><td>{{ c.valeur }}</td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="w-full bg-[#ebe5d1] mb-1 mx-2">
            <div class="bg-[#1b716c] text-sm text-center py-2 font-bold font-['Comfortaa']">CONSEIL D'UTILISATION</div>
            <div class="py-2 " v-html="val.conseil"></div>
        </div>
        <div class="w-full bg-[#ebe5d1] mb-1 mx-2">
            <div class="bg-[#e74F10] text-sm text-center py-2 font-bold font-['Comfortaa']">PRECAUTION</div>
            <div class="py-2" v-html="value.precaution"></div>
        </div>

    </div>
</template>

<script setup>

    import {ref, onMounted} from 'vue'

    const props = defineProps({
    value:Object}
    )


    const val = ref(props.value)

    onMounted(() => {
        val.value.conseil = props.value.conseil.replace("<ul>", '<ul role="list" class="marker:text-black-400 list-disc pl-5 space-y-3">')
        val.value.precaution = props.value.precaution.replace("<ul>", '<ul role="list" class="marker:text-black-400 list-disc pl-5 space-y-3">')
    })


</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Arimo:ital,wght@0,400..700;1,400..700&family=Comfortaa:wght@300..700&display=swap');

</style>