const taskInput = document.getElementById('task');
const addTaskButton = document.getElementById('addTask');
const clearAllButton = document.getElementById('clearAll');
const deleteSelectedButton = document.getElementById('deleteSelected');
const filterSelect = document.getElementById('filter');
const taskList = document.getElementById('taskList');
const completedCount = document.getElementById('completedCount');
const totalCount = document.getElementById('totalCount');

let tasks = [];

const addTask = () => {
    const taskText = taskInput.value.trim();
    if (taskText) {
tasks.push({ text: taskText, completed: false });
        updateTaskList();
        taskInput.value = '';
    } else {
alert("😮 Task cannot be empty");
    }
};

const updateTaskList = () => {
    const filterValue = filterSelect.value;
    let filteredTasks = tasks.filter(task => {
        if (filterValue === 'all') return true;
        if (filterValue === 'pending') return !task.completed;
        if (filterValue === 'completed') return task.completed;
    });

    taskList.innerHTML = '';

    filteredTasks.forEach((task, index) => {
      const listItem = document.createElement('li');
  listItem.className = 'todo-item';
      listItem.id = Date.now();
       const taskParagraph = document.createElement('p');
        taskParagraph.textContent = task.text;
       taskParagraph.id = 'task';
        if (task.completed) {
      taskParagraph.classList.add('completed');
  }
      listItem.appendChild(taskParagraph);

   const todoActions = document.createElement('div');
   todoActions.className = 'todo-actions';

   const completeButton = createButton('complete', 'btn btn-success', '\u2713 ', () => {
  task.completed = !task.completed;
  updateTaskList();
   });

    const deleteButton = createButton('delete', 'btn btn-error', '\u2715 ', () => {
            tasks.splice(index, 1);
        updateTaskList();
    });
    todoActions.appendChild(completeButton);
    todoActions.appendChild(deleteButton);

    listItem.appendChild(todoActions);
    taskList.appendChild(listItem);
});

    completedCount.textContent = tasks.filter(task => task.completed).length;
    totalCount.textContent = tasks.length;
};

const createButton = (className, btnClass, innerHTML, clickHandler) => {
  const button = document.createElement('button');
    button.className = `${className} ${btnClass}`;
  button.innerHTML = innerHTML;
  button.addEventListener('click', clickHandler);
  return button;
};

const clearAllTasks = () => {
 const confirmed = confirm("😮 Are you sure you want to delete all tasks?");
    if (confirmed) {
     tasks = [];
     updateTaskList();
 }
};

const deleteSelectedTasks = () => {
const selectedTasks = tasks.filter(task => task.completed);
    if (selectedTasks.length === 0) {
    alert(" 😮 Please select at least one task to delete!");
} else {
    tasks = tasks.filter(task => !task.completed);
        updateTaskList();
    }
};

addTaskButton.addEventListener('click', addTask);
clearAllButton.addEventListener('click', clearAllTasks);
deleteSelectedButton.addEventListener('click', deleteSelectedTasks);
filterSelect.addEventListener('change', updateTaskList);
taskList.addEventListener('change', (event) => {
 const index = event.target.dataset.index;
    if (index !== undefined) {
     tasks[index].completed = event.target.checked;
     updateTaskList();
 }
});

updateTaskList();