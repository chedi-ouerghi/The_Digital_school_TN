<template>
  <div class="gallery">
    <img :src="movie.image" alt="movie poster" class="image-card" />
    <div class="image-overlay">
    <h2>{{ movie.title }}</h2>
    <p>{{ movie.description }}</p>
  </div>
  </div>
</template>

<script>
export default {
  name: 'MovieCard',
  props: {
    movie: Object
  }
}
</script>

<style scoped>

/* Styles spécifiques pour la galerie d'images */
.gallery {
  scrollbar-width: none; /* Firefox */
  -ms-overflow-style: none; /* Internet Explorer et Edge */
  display: flex;
  flex-wrap: nowrap;
  justify-content: flex-start;
  margin: 20px;
  overflow-y: hidden;
  overflow-x: auto;
  width: calc(100% - 40px);
  height: 50vh;
  scrollbar-color: #888 transparent; /* Barre de défilement pour Firefox */
}

.gallery:hover {
  scrollbar-color: #555 transparent; /* Couleur de la barre de défilement lorsqu'elle est survolée */
}

/* Carte d'image */
.image-card {
  margin: 0 15px;
  position: relative;
  border: 3px solid #fff;
  border-radius: 15px;
}

.image-card img {
  width: 330px;
  height: 350px;
  object-fit: cover;
  transition: transform 0.3s, opacity 0.3s;
  border-radius: 15px;
}

/* Superposition de l'image */
.image-overlay {
  position: relative;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  color: #fff;
  width: 100%;
  text-align: center;
  padding: 10px;
  box-sizing: border-box;
  transition: background 0.3s;
  border-radius: 0 0 15px 15px;
}

.image-card:hover .image-overlay {
  background: rgba(0, 0, 0, 0.7);
}

/* Classe de marge supérieure */
.margin-top-15 {
  margin-top: 25px;
}

/* Bouton */
button {
  background: none;
  border: none;
}

</style>
