<template>
    <main class="md:p-6 md:pt-10 px-2 pt-6">
      <div class="md:flex md:flex-row md:items-center md:justify-between">
        <header class=" flex md:flex flex-row gap-x-2 pb-3 items-center">
          <img src="./images/bag-2.png" alt="" srcset="" />
          <p class="text-darkBlue text-2xl font-bold">Outils disponibles</p>
        </header>
      </div>
      <section class="lg:flex lg:flex-row lg:justify-start">
        <section class="lg:w-[65%] lg:pr-4">
          <div
           class="md:w-full border-y-2 py-4 border-lineColor flex flex-row justify-between text-grey font-medium"
            >
              <div class="md:flex md:w-[45%] md:flex-row md:justify-between ">
                <p>PRODUIT</p>
                <p>PRIX</p>
              </div>
              <div class="md:flex md:w-[40%] md:flex-row md:justify-between">
                <p >NB RESTANT</p>
                <p >DUREE</p>
              </div>
          </div>
          <section id="add-items" class="add-cart-items">

            <slot></slot>

          </section>
        </section>
      </section>
    </main>

</template>