<template>
  <li class="list-group-item d-flex justify-content-between align-items-center">
    <span :class="{ 'text-decoration-line-through': localTodo.completed }">{{ localTodo.title }}</span>
    <div>
      <input type="checkbox" v-model="localTodo.completed" @change="updateTodo" />
      <i class="far fa-trash-alt delete" @click="$emit('delete-todo')"></i>
    </div>
  </li>
</template>

<script>
export default {
  name: 'TodoItem',
  props: {
    todo: Object
  },
  data() {
    return {
      localTodo: { ...this.todo }
    };
  },
  methods: {
    updateTodo() {
      this.$emit('update-todo', this.localTodo);
    }
  },
  watch: {
    todo: {
      handler(newTodo) {
        this.localTodo = { ...newTodo };
      },
      deep: true,
      immediate: true
    }
  }
};
</script>

<style>
.text-decoration-line-through {
  text-decoration: line-through;
}
</style>
