<template>
  <div id="app">
    <SearchBar @search="handleSearch" />
    <div class="movie-list">
      <MovieCard v-for="movie in filteredMovies" :key="movie.id" :movie="movie" />
    </div>
  </div>
</template>

<script>
import MovieCard from './components/movie/MovieCard.vue'
import SearchBar from './components/movie/SearchBar.vue'

export default {
  name: 'App',
  components: {
    MovieCard,
    SearchBar
  },        
  data() {      
    return {        
      movies: [     
        { id: 1, title: 'Inception', description: 'A thief who steals corporate secrets...', image: 'https://m.media-amazon.com/images/M/MV5BMjAxMzY3NjcxNF5BMl5BanBnXkFtZTcwNTI5OTM0Mw@@._V1_.jpg' },      
                { id: 2, title: 'Interstellar', description: 'A team of explorerstravels through a wormhole in space...', image: 'https://upload.wikimedia.org/wikipedia/en/b/bc/Interstellar_film_poster.jpg' }        ,
        { id: 3, title: 'The Dark Knight', description: 'When the menace known as the Joker emerges...', image: ' https://m.media-amazon.com/images/M/MV5BMTMxNTMwODM0NF5BMl5BanBnXkFtZTcwODAyMTk2Mw@@._V1_.jpg'         }
        ],      
        query: ''       
        }       
        },
        computed: {
        filteredMovies() {
        return this.movies.filter(movie =>
        movie.title.toLowerCase().includes(this.query.toLowerCase())
);
}
},
    methods: {
    handleSearch(query) {
    this.query = query;
    }
}
}
</script>

<style>
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  text-align: center;
  margin-top: 60px;
}
.movie-list {
  display: flex;
  justify-content: center;
width: 100%;
}
</style>
