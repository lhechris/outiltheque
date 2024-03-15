<template>
    <div class="md:justify-between border-lineColor flex flex-row py-6">
        <div class="flex md:w-[45%] md:justify-between flex-col md:flex-row md:items-center w-[60%] ">
            <div class="flex flex-row gap-x-2">
                <img class="max-w-[7.5rem] h-auto" :src="value.file_path" alt="" srcset="" />
                <div class="md:pr-6 md:flex md:flex-col md:justify-center" >
                <p class="text-lightGrey text-sm md:text-base">{{value.nom}}</p>
                <p  style="white-space:wrap;overflow:hidden;width:100%" class="item-description md:text-lg text-base">
                {{ value.description }}
                </p>
            </div>
        </div>
        <p class="self-end md:self-center text-xl md:text-2xl font-bold mr-4 md:mr-0">{{value.prix}}</p>
    </div>
    <div class="w-[40%] md:w-[30%] flex md:flex-row-reverse md:items-center flex-col items-end justify-between">
        <p class="self-end md:self-center text-xl md:text-2xl font-bold mr-4 md:mr-0">{{value.duree}}</p>
        <p class="self-end md:self-center text-xl md:text-2xl font-bold mr-4 md:mr-0">{{value.nombre}}</p>

        <!--<p id="total-item-cost" class="text-xl md:text-2xl text-purple font-bold"></p>
        <div
            class="flex flex-row gap-x-2 items-center border-y-[2px] border-lineColor"
        >
            <button id="decrease" class="increment-btn">-</button>
            <p class="px-2">1</p>
            <button id="increase" class="increment-btn ">+</button>
        </div>-->
    </div>    
  </div>
  <button id="reservation" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    <p>Je réserve</p>
                </button>
</template>

<script>
import {ref} from 'vue'

export default {
    
    props : ['value'],

    setup() {


        return {
        }
    },
}
</script>
