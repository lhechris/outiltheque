<template>
        <enveloppe>
      <!--  <profile user="user"></profile>-->
      <div>
            <div class="flex flex-col overflow-x-auto overflow-y-hidden py-10 ml-2">
                    <p>Bienvenue sur l'outilthèque de Labobinette</p>
                    <p class="mt-5 ">Ce site est réservé aux adhérents de Labobinette</p>
                    <p class="mt-2">Le principe d'une outilthèque est de pouvoir emprunter un outil ou un objet pour une durée de temps défnie à la place de l’acheter.</p>
                    <p class="mt-2">L'outilthèque fonctionne comme une bibliothèque, mais pour le prêt d'outils. Il est possible d'emprunter du matériel de cuisine, des outils variés, du matériel de jardinage. </p>
                    <p class="mt-2">Ici, vous pourrez emprunter les objets pour une durée d'une semaine.</p>
                    <p class="mt-2">Plutôt que d'accumuler un ensemble d'outils encombrants chez soi, l'outilthèque permet une mise en commun.</p>
                    <p class="mt-5">Vous réservez, vous récupérez votre réservation le jeudi et vous le retournez le mercredi</p>
                        <router-link class="mt-5 w-64 bg-blue-500 hover:bg-blue-900 text-white font-bold py-2 px-4 rounded" 
                                  to="/home" >Accéder aux produits</router-link>
                </div>
        </div>
    </enveloppe>
</template>

<script setup>
    import Enveloppe from '../components/Enveloppe.vue';

</script>
