import {request} from './helper'
import {useRouter} from "vue-router";

export const paiementHA = async (resaid,message, erreur) => {
    try {
        const req = await request('get', '/api/encaissement/'+resaid)

        if (req.status == 200) {
            let redirecturl = req.data.data.redirectUrl

            message = "Paiement encours"
            erreur = null

            window.location.href = redirecturl.value

        } else {
            message = null
            erreur = req.data.message
        }

    } catch (e) {
        erreur = e.message
        message = null
        console.log(e)
    }    
}

export const paiementCash = async (resaid,message, erreur) => {
    try {
        const req = await request('put', '/api/cash/'+resaid)

        if (req.status == 202) {
            message = "Paiement à la livraison"
            erreur = null
            let router = useRouter();
            router.push("/confirmation/"+resa.value.id)

         } else {
            message = null
            erreur = req.data.message
        }

    } catch (e) {
        erreur = e.message
        message = null
        console.log(e)
    }
}

export const paiementCancel = async (resaid,message, erreur) => {
    message = null
    erreur = null

    try {
        const req = await request('delete', '/api/reservations/'+resaid)

        if (req.status == 200) {
            let router = useRouter();
            message = "Article annulé"

         } else {
            erreur = req.data.message
        }

    } catch (e) {
        erreur = e.message
        console.log(e)
    }
}
