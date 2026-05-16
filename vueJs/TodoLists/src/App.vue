<template>
  <div id="app">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container-fluid">
        <h1 style="color: aliceblue;">todolist</h1>
        <router-link class="navbar-brand" to="/">Login</router-link>
      </div>
    </nav>
    <div class="container mt-4">
      <router-view />
    </div>
  </div>
</template>

<script>
export default {
  name: 'App'
};
</script>

<style src="./style.css"></style>
