<template>
  <nav>
    <router-link to="/">Accueil</router-link>
    <router-link to="/login">Connexion</router-link>
    <router-link to="/gallery">Galerie</router-link>
  </nav>
</template>

<script>
export default {
  name: 'Navbar',
};
</script>

<style scoped>
nav {
  padding: 15px;
  position: absolute;
  display: flex;
  justify-content: space-around;
  width: 97%;
  background-color: rgb(4 39 129 / 18%);
  backdrop-filter: blur(5px); 
  z-index: 1000;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  top: 0;
  height: 5vh;
}

nav a {
  font-weight: bold;
  color: #198fc6e8;
  text-decoration: none;
  padding: 10px;
  border-radius: 5px;
  transition: color 0.3s;
}

nav a.router-link-exact-active {
  color: #082961dd;
}
</style>
