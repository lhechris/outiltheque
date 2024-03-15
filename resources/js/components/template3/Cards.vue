<template>
<div class="w-full absolute z-10 right-0 h-full overflow-x-hidden transform translate-x-0 transition ease-in-out duration-700" id="checkout">

    <div class="flex items-end lg:flex-row flex-col justify-center" id="cart">
        <div class="lg:w-1/2 md:w-8/12 w-full lg:px-8 lg:py-14 md:px-6 px-4 md:py-8 py-4 bg-white dark:bg-gray-800 overflow-y-hidden overflow-x-hidden h-auto" id="scroll">

            <slot></slot>

        </div>

    </div>            
</div>
</template>