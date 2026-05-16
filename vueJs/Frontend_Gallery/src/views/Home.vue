<template>
  <div :style="backgroundStyle" class="home-container">
    <div class="home-content">
      <h1 class="home-title">Explorez notre galerie d'architecture</h1>
      <router-link to="/login" class="home-link">Commencer</router-link>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Home',
  data() {
    return {
      backgroundUrl: 'https://source.unsplash.com/1920x1080/?architecture',
    };
  },
  computed: {
    backgroundStyle() {
      return {
        backgroundImage: `url(${this.backgroundUrl})`,
        height: '100vh',
        display: 'flex',
        justifyContent: 'center',
        alignItems: 'center',
        backgroundSize: 'cover',
        backgroundPosition: 'center',
        overflow: 'hidden',
      };
    },
  },
};
</script>

<style scoped>
.home-content {
  text-align: center;
}

.home-title {
  font-size: 3rem;
  color: #fff;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
  animation: fadeIn 1.5s ease-out;
}

.home-link {
  display: inline-block;
  padding: 10px 20px;
  margin-top: 20px;
  background-color: #57caff3b;
  color: #fff;
  font-size: 1.2rem;
  text-decoration: none;
  border-radius: 5px;
  transition: background-color 0.3s, transform 0.3s;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.home-link:hover {
  background-color: #ff9f00;
  transform: scale(1.05);
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
