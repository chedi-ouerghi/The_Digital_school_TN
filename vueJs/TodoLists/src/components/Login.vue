<template>
  <div style=" width: 34%;">
    <div class="login-container">
      <form @submit.prevent="login" class="login-form">
        <h1>Login</h1>
        <input
          type="text"
          placeholder="Email"
          v-model="email"
          class="login-input"
        />
        <input
          type="password"
          placeholder="Mot de passe"
          v-model="password"
          class="login-input"
        />
        <button type="submit" class="login-button">Se connecter</button>
      </form>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

export default {
  name: 'LoginForm',
  setup() {
    const email = ref('');
    const password = ref('');
    const router = useRouter();

    const login = () => {
      console.log('Login successful!');
      router.push('/todolist');
    };

    return {
      email,
      password,
      login
    };
  },
};
</script>

<style src="./loginForm.css"></style>
