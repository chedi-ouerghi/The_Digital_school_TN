window.addEventListener('load', startJs)

function startJs() {
  const inputTodo = document.getElementById('todo-add');
  const btnAdd = document.getElementById('btn-add');
  const todosContainer = document.getElementById('list-container')
  const resultsTasks = document.getElementById('result-tasks');
  const resultsCompleted = document.getElementById('result-completed');
  const btnDeleteAll = document.getElementById('btn-delete-all');
  const btnDeleteSelected = document.getElementById('btn-delete-selected');
  const allFilter = document.getElementById('filter-list-completed-all')
  const pendingFilter = document.getElementById('filter-list-completed-pending')
  const completedFilter = document.getElementById('filter-list-completed-option')

  
  fetch('http://localhost:6532/tasks/get')
    .then(response => response.json())
    .then(tasks => {
      tasks.forEach(task => addTaskToDOM(task));
      updateTotalTasks();
      updateTotalCompletedTasks();
    })
    .catch(error => console.error(error));

  btnAdd.addEventListener('click', addTodoItem);
  inputTodo.addEventListener('keyup', function (event) {
    if (event.code == 'Enter') {
      addTodoItem();
    }
  })

  function addTodoItem() {
    const todoObject = {
      title: inputTodo.value,
      description: "", 
      dueDate: null, 
      completed: false
    }

    if (inputTodo.value.trim() == "") {
      return
    }

    
    fetch('http://localhost:6532/tasks/post', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(todoObject),
    })
      .then(response => response.json())
      .then(newTask => {
        addTaskToDOM(newTask);
        updateTotalTasks();
        updateTotalCompletedTasks();
      })
      .catch(error => console.error(error));

    inputTodo.value = "";
  }

function addTaskToDOM(task) {
    const element = document.createElement('div');
    element.classList.add('todolist-items');
    element.id = task._id;

    const listElement = `
        <div class="task-title">${task.title}</div>
        <div class="todolist-items-btns">
            <div id="completed-item">Complete</div>
            <div id="delete-item">Delete</div>
        </div>`;
    element.innerHTML += listElement;
    todosContainer.append(element);

    const completeItem = element.querySelector('#completed-item');
    completeItem.addEventListener('click', function () {
        const completed = !element.classList.contains('completed-task');

        fetch(`http://localhost:6532/tasks/edit/${task._id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ completed }),
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(updatedTask => {
                element.classList.toggle('completed-task', updatedTask.completed);
                updateTotalCompletedTasks();
            })
            .catch(error => {
                console.error(error);
            });
    });

    const deleteItem = element.querySelector('#delete-item');
    deleteItem.addEventListener('click', function () {
        fetch(`http://localhost:6532/tasks/delete/${task._id}`, {
            method: 'DELETE',
        })
            .then(() => {
                element.remove();
                updateTotalTasks();
                updateTotalCompletedTasks();
            })
            .catch(error => console.error(error));
    });

    updateTotalTasks();
}

  function updateTotalTasks() {
    const itemsAll = document.querySelectorAll('.todolist-items')
    resultsTasks.innerHTML = itemsAll.length;
  }

function updateTotalCompletedTasks() {
    const itemsAllCompleted = document.querySelectorAll('.todolist-items.completed-task');
    resultsCompleted.innerHTML = itemsAllCompleted.length;
}

  btnDeleteAll.addEventListener('click', function () {
    
    fetch('http://localhost:6532/tasks/delete', {
      method: 'DELETE',
    })
      .then(() => {
        todosContainer.innerHTML = "";
        updateTotalTasks();
        updateTotalCompletedTasks();
      })
      .catch(error => console.error(error));
  })

  btnDeleteSelected.addEventListener('click', function () {
    const itemsAll = document.querySelectorAll('.todolist-items');
    itemsAll.forEach(el => {
      if (el.classList.contains('completed-task')) {
        
        fetch(`http://localhost:6532/tasks/delete/${el.id}`, {
          method: 'DELETE',
        })
          .then(() => {
            el.remove();
            updateTotalTasks();
            updateTotalCompletedTasks();
          })
          .catch(error => console.error(error));
      }
    });
  })

  allFilter.addEventListener('click', function () {
    const itemsAll = document.querySelectorAll('.todolist-items');
    itemsAll.forEach(el => {
      el.classList.remove('hidden');
    })
  })

  pendingFilter.addEventListener('click', function () {
    const itemsAll = document.querySelectorAll('.todolist-items');
    itemsAll.forEach(el => {
      if (el.classList.contains('completed-task')) {
        el.classList.add('hidden')
      } else {
        el.classList.remove('hidden')
      }
    })
  })

  completedFilter.addEventListener('click', function () {
    const itemsAll = document.querySelectorAll('.todolist-items');
    itemsAll.forEach(el => {
      if (el.classList.contains('completed-task')) {
        el.classList.remove('hidden')
      } else {
        el.classList.add('hidden')
      }
    })
  })
}
