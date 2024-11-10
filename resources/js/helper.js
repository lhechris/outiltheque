import axios from "axios";

export const request = async (method, url, data) => {
    const token = localStorage.getItem('APP_DEMO_USER_TOKEN')
    let headers = null;
    if (token !== undefined && token !== "" && token !== null) {
        headers = {
            headers: {
                Authorization: 'Bearer ' + token
            }
        }
    }
    let response = null;
    switch (method) {
        case 'get':
            response = await axios.get(url, headers)
            break;
        case 'post':
            response = await axios.post(url, data, headers)
            break;
        case 'put':
            response = await axios.put(url, data, headers)
            break;
        case 'delete':
            response = await axios.delete(url, headers)
            break;
        default:
            break;
    }
    return response
}

export const upload_file = async (url, file) => {
    const token = localStorage.getItem('APP_DEMO_USER_TOKEN')
    if (token !== undefined && token !== "" && token !== null) {
        const headers = {
            headers: {
                Authorization: 'Bearer ' + token,
                "Content-Type" : 'multipart/form-data'
            }
        }
        let formData = new FormData();
        formData.append("file", file);
        let response = null;
        response = await axios.post(url, formData, headers)
        return response
    }
    return false
}

export function getmenus(role) {
    if (role == "admin") {
        return { "Accueil" : "/",
                 "Outils" : "/admin",
                 "Reservations" : "/reservations",
                 "Categories" : "/categories",
                "Profile" : "/profile"}
    } else {
        return { "Accueil" : "/"                 
                 }
    }
}

export function validateEmail(email) {
    const res = /^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/;
    return res.test(String(email).toLowerCase());
}

export function validateTel(tel) {
    const res = /^0[1-6]\d{8}$/;
    return res.test(String(tel).toLowerCase().replaceAll(' ', '').trim());
}

