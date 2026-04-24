import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/Login.vue'
import Results from '../views/Results.vue'

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', component: Login },
  {
    path: '/results',
    component: Results,
    beforeEnter: () => {
      if (!localStorage.getItem('token')) {
        return '/login'
      }
    },
  },
]

export default createRouter({
  history: createWebHistory(),
  routes,
})
