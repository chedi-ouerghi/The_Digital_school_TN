import { createRouter, createWebHistory } from 'vue-router';
import TodoList from '../components/TodoList.vue';
import Login from '../components/Login.vue';

const routes = [
  {
    path: '/',
    name: 'Login',
    component: Login,
  },
  {
    path: '/todolist',
    name: 'TodoList',
    component: TodoList,
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
