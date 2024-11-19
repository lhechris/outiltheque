import {test, expect,vi} from "vitest";
import {mount} from "@vue/test-utils"

import Enveloppe from "../components/Enveloppe.vue"
import InfoResa from '../components/Inforesa.vue'

import {request} from '../helper.js'

import Reservation from '../pages/Reservation.vue'


vi.mock('vue-router')


vi.mock('../helper.js',() => {
    return {
        request : vi.fn()
    }
})

test("mount component", async () => {
    expect(Reservation).toBeTruthy();


    request.mockReturnValue({
        data: {
            status : 200,
            data : {"id" : "1", "nom" : "un outil", "description" : "une description"}
        }
    })

    const wrapper = mount(Reservation, {
        props: {
            outilid: 1
        },
        global : {
            stubs : [ "Paiement", "router-link", "FicheOutil"],
            components : [Enveloppe, InfoResa]
        }
    });

  //  console.log(wrapper.html())

  //  expect(wrapper.find('[data-test="inforesa"]').exists()).toBe(true)
    //expect(wrapper.find('paiement').exists()).toBe(false)

  
});