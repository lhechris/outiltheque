<?php

use Livewire\Component;

new class extends Component {

};
?>

<div class="min-h-screen bg-gray-50">
    <livewire:reservations.tab :historique="false" :mine="true">
    </livewire:reservations.tab>

    <livewire:reservations.tab :historique="true" :mine="true">
    </livewire:reservations.tab>
</div>