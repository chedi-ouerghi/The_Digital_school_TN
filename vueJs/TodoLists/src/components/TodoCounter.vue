<template>
  <div class="todo-counter">
    <p>Total Todos: {{ totalTodos }}</p>
    <p>Completed Todos: {{ completedTodos }}</p>
    <p>Incomplete Todos: {{ incompleteTodos }}</p>
  </div>
</template>

<script>
export default {
  name: 'TodoCounter',
  props: {
    todos: Array
  },
  computed: {
    totalTodos() {
      return this.todos.length;
    },
    completedTodos() {
      return this.todos.filter(todo => todo.completed).length;
    },
    incompleteTodos() {
      return this.todos.filter(todo => !todo.completed).length;
    }
  }
};
</script>

<style>
.todo-counter {
  margin: 20px 0;
}
</style>
