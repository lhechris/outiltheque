import {request} from './helper'
import {useRouter} from "vue-router";

export const paiementHA = async (resaid) => {
        const req = await request('get', '/api/encaissement/'+resaid)

        if (req.status == 200) {
            let redirecturl = req.data.data.redirectUrl
            return redirecturl

        } else {
            throw new Error(req.data.message)
        }
}

export const paiementCash = async (resaid) => {
        const req = await request('put', '/api/cash/'+resaid)

        if (req.status == 202) {

         } else {
            throw new Error(req.data.message)
        }

}

export const paiementCancel = async (resaid) => {
        const req = await request('delete', '/api/reservations/'+resaid)

        if (req.status == 200) {

         } else {
            throw new Error(req.data.message)
        }
}
