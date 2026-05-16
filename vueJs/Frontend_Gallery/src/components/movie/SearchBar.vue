<template>
  <div class="search-bar">
    <input type="text" v-model="query" @input="onSearch" placeholder="Search for a movie..." />
  </div>
</template>

<script>
export default {
  name: 'SearchBar',
  data() {
    return {
      query: ''
    }
  },
  methods: {
    onSearch() {
      this.$emit('search', this.query);
    }
  }
}
</script>

<style scoped>
.search-bar {
  margin: 16px;
  text-align: center;
}
.search-bar input {
  padding: 8px;
  width: 80%;
  max-width: 400px;
}
</style>
