<template>
    <nav class="fixed top-0 left-0 z-20 w-full border-b border-gray-200 bg-white py-2.5 px-6 sm:px-4">
    <div class="container mx-auto flex max-w-6xl flex-wrap items-center justify-between">
      <a href="#" class="hidden md:flex items-center md:w-auto">
        <img class="max-h-12" src="/storage/app/images/LB_logo_2.png" />
  
        <span class="self-center whitespace-nowrap text-xl font-semibold p-2">Outilthèque</span>
      </a>
      <div class="mt-2 sm:mt-0 sm:flex md:order-2">
        <span class="text-sm px-4 py-1.5"> {{ user }} </span>
        <!-- Login Button -->
        <button v-if="logout" type="button" 
            class="rounde mr-3 hidden border border-blue-700 py-1.5 px-6 text-center text-sm font-medium text-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 md:inline-block rounded-lg"
            @click="handleLogout"
            >Logout</button>

        <!-- Register Button -->
<!--        <button data-collapse-toggle="navbar-sticky" type="button" class="inline-flex items-center rounded-lg p-2 text-sm text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 md:hidden" aria-controls="navbar-sticky" aria-expanded="false">
          <span class="sr-only">Open main menu</span>
          <svg class="h-6 w-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path></svg>
        </button>-->
      </div>
      <div class="w-full items-center justify-between md:order-1 md:flex md:w-auto" id="navbar-sticky">
        <ul class="mt-4 flex flex-row rounded-lg border border-gray-100 bg-gray-50 p-4 md:mt-0 md:flex-row md:space-x-8 md:border-0 md:bg-white md:text-sm md:font-medium">
          <li v-for="(l,k) in menus" :index="k" >
            <router-link :to="l" class="block rounded pl-1 pr-4 md:bg-transparent md:p-0 text-blue-700" aria-current="page">{{k}}</router-link>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>

<script setup>
  const emits = defineEmits(['logout'])

  const props = defineProps(["user","menus","logout"])

  const handleLogout = () => {
        emits('logout')
  }

</script>