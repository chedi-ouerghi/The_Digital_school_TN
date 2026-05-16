<template>
  <div class="container-todoList">
    <header class="header text-center text-light my-4">
      <h1 class="title mb-4">Todo List</h1>
    </header>
    
    <div class="controls mb-4">
      <form class="search mb-4">
        <input
          class="form-control search-input"
          type="text"
          name="search"
          placeholder="Search todos"
          v-model="searchTerm"
        />
      </form>

      <div style="display: flex; width: 100%; justify-content: space-around; align-items: center; height: auto; gap: 2%;">
        <div class="filter mb-4">
          <label for="filter" class="form-label">Filter Todos:</label>
          <select id="filter" v-model="filterStatus" class="form-control">
            <option value="all">All</option>
            <option value="completed">Completed</option>
            <option value="incomplete">Incomplete</option>
          </select>
        </div>

        <div class="todo-add-section my-4">
          <form class="add-form" @submit.prevent="addTodo">
            <div style="display: flex; flex-direction: column;">
              <label class="add-label text-light">Add a new todo...</label>
              <input v-model="newTodo" class="form-control add-input" placeholder="Add a new todo..." type="text" name="add" />
            </div>
            <button type="submit" class="btn btn-light mt-2 add-button_add" value="Add">Add</button>
          </form>
        </div>
      </div>
    </div>
    
    <ul class="list-group todos text-light">
      <todo-item 
        v-for="(todo, index) in filteredTodos" 
        :key="index" 
        :todo="todo" 
        @delete-todo="deleteTodo(index)"
        @update-todo="updateTodoStatus(index, $event.completed)"
      />
    </ul>

    <todo-counter :todos="todos" class="todo-counter" />
  </div>
</template>

<script>
import TodoItem from './Todo.vue';
import TodoCounter from './TodoCounter.vue';

export default {
  name: 'TodoList',
  components: {
    TodoItem,
    TodoCounter
  },
  data() {
    return {
      todos:[
        { title: 'Javascript', completed: false },
        { title: 'React Js', completed: false },
        { title: 'Nest Js', completed: false },
        { title: 'Vue Js', completed: false }
      ],
      newTodo: '',
      searchTerm: '',
      filterStatus: 'all'
    };
  },
  computed: {
    filteredTodos() {
      let filtered = this.todos.filter(todo =>
        todo.title.toLowerCase().includes(this.searchTerm.toLowerCase())
      );
      if (this.filterStatus === 'completed') {
        filtered = filtered.filter(todo => todo.completed);
      } else if (this.filterStatus === 'incomplete') {
        filtered = filtered.filter(todo => !todo.completed);
      }
      return filtered;
    }
  },
  methods: {
    addTodo() {
      if (this.newTodo.trim()) {
        this.todos.push({ title: this.newTodo, completed: false });
        this.newTodo = '';
        this.saveTodos();
      }
    },
    deleteTodo(index) {
      this.todos.splice(index, 1);
      this.saveTodos();
    },
    updateTodoStatus(index, completed) {
      this.todos[index].completed = completed;
      this.saveTodos();
    },
    saveTodos() {
     this.todos;
    }
  }
};
</script>

<style>
</style>
