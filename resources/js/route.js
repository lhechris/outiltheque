import {createRouter, createWebHistory} from 'vue-router';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            component: () => import('./pages/Home.vue')
        },
        {
            path: '/home',
            component: () => import('./pages/Home.vue')
        },
        {
            path: '/reservation/:outilid',
            component: () => import('./pages/Reservation.vue'),
            props: true
        },
        {
           // path: '/confirmation/:resaid/:checkoutIntentId/:code/:orderId',
            path: '/confirmation/:resaid',
            component: () => import('./pages/Confirmation.vue'),
            props: true
        },
        {
            // path: '/confirmation/:resaid/:checkoutIntentId/:code/:orderId',
             path: '/encaissementerreur/:resaid',
             component: () => import('./pages/EncaissementErreur.vue'),
             props: true
         },
 

        /** ADMIN */
        {
            path: '/register',
            component: () => import('./pages/admin/Register.vue')
        },
        {
            path: '/login',
            component: () => import('./pages/admin/Login.vue')
        },
        {
            path: '/admin',
            component: () => import('./pages/admin/Edit.vue')
        },
        {
            path: '/editoutil/:outilid',
            component: () => import('./pages/admin/OutilsEditions.vue'),
            props:true
        },
        {
            path: '/reservations',
            component: () => import('./pages/admin/Reservations.vue')
        },
        {
            path: '/categories',
            component: () => import('./pages/admin/Categories.vue'),
            props: true
        },
        {
            path: '/profile',
            component: () => import('./pages/admin/User.vue'),
            props: true
        }

    ],
})


router.beforeEach((to, from, next) => {
    if ( !isAuthenticated() && (to.path in ['/admin','/editoutil','/reservations','/categories'])) {
        return next({path: '/login'})
    }
    return next()
})

function isAuthenticated() {
    return Boolean(localStorage.getItem('APP_DEMO_USER_TOKEN'))
}

export default router;