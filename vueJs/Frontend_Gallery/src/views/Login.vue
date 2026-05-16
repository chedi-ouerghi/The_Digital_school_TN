<template>
  <div style="width: 100%; height:100%;">
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
import { reactive, toRefs } from 'vue';
import { useRouter } from 'vue-router';

export default {
  name: 'LoginForm',
  setup() {
    const initialEmail = 'chedi@chedi.com';
    const initialPassword = 'chedi123';
    const router = useRouter();

    const state = reactive({
      email: initialEmail,
      password: initialPassword,
    });

    const login = () => {
      if (state.email === initialEmail && state.password === initialPassword) {
        console.log('Login successful!');
        router.push('/gallery');
      } else {
        console.log('Invalid email or password.');
      }
    };

    return {
      ...toRefs(state),
      login,
    };
  },
};
</script>

<style src="./loginForm.css"></style>
