import axios from "axios";

export const request = async (method, url, data) => {
    const token = localStorage.getItem('APP_DEMO_USER_TOKEN')
    if (token !== undefined || token !== "") {
        const headers = {
            headers: {
                Authorization: 'Bearer ' + token
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
    return false
}

export const upload_file = async (url, file) => {
    const token = localStorage.getItem('APP_DEMO_USER_TOKEN')
    if (token !== undefined || token !== "") {
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

