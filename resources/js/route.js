import {createRouter, createWebHistory} from 'vue-router';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            component: () => import('./pages/Home.vue')
        },
        {
            path: '/register',
            component: () => import('./pages/Register.vue')
        },
        {
            path: '/login',
            component: () => import('./pages/Login.vue')
        },
        {
            path: '/home',
            component: () => import('./pages/Home.vue')
        },
        {
            path: '/editoutils',
            component: () => import('./pages/OutilsEditions.vue')
        },
        {
            path: '/reservations',
            component: () => import('./pages/ReservationsPage.vue')
        },
        {
            path: '/reservation/:outilid',
            component: () => import('./pages/Reservation.vue'),
            props: true
        },
        {
            path: '/profil',
            component: () => import('./pages/User.vue'),
            props: true
        }

    ],
})
router.beforeEach((to, from, next) => {
    if (to.path !== '/login' && to.path !== '/register' && !isAuthenticated()) {
        return next({path: '/login'})
    }
    return next()
})

function isAuthenticated() {
    return Boolean(localStorage.getItem('APP_DEMO_USER_TOKEN'))
}

export default router;