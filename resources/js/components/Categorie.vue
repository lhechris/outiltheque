<template>

 
  
    <!-- Title -->
    <div class="pt-14  bg-white">
    <h1 class="text-center text-2xl font-bold text-gray-600 text-sm dark:text-gray-500"> {{ value.description }}</h1>
    </div>
    <section class="py-10 bg-gray-100">
      <div class="mx-auto grid max-w-6xl  grid-cols-1 gap-6 p-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
  
        <slot></slot>
  
      </div>
    </section>
  
  </template>
  <script>

  export default {
      
      props : ['value','link'],
  
      setup() {
  
          return {
          }
      },
  }
  </script>
  