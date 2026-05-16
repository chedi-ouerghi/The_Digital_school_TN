@extends('layouts.app')

@section('content')
   {{-- navbar  --}}
   <nav class="navbarTop">
    <a class="brand" href="#">TodoList</a>
    <div class="navList" id="navbarNav">
        <ul class="navItems">
            <a class="navLink" href="/">Home</a>
            @auth
                <a class="navLink" href="profile">Profile</a>
                <a class="navLink" href="login">Logout</a>
            @else
                <a class="navLink" href="login">Login</a>
                <a class="navLink" href="register">Register</a>
            @endauth
        </ul>
    </div>
</nav>


    
    <div class="containerfluid">
        <div class="rowss">
          <nav>
		<ul class="mcd-menu">
			<li>
				<a href="" class="active">
					<i class="fa fa-home"></i>
					<strong>Home</strong>
					<small> home</small>
				</a>
			</li>
			<li>
<a href="#" data-url="calendar.html" id="calendarLink">
					<i class="fa fa-edit"></i>
					<strong>News</strong>
					<small>news </small>
				</a>
			</li>
		</ul>
	</nav>

<main role="main" class="main-dahboard" id="mainContent">
    <div class="dashboard-header">
        {{-- <h1 class="h2">Home</h1> --}}
                <a href="{{ route('tasks.create') }}" class="btnn">Ajouter une tâche</a>

    </div>

    @if(optional($tasks)->count() > 0)
        <table class="table-dashboard">
            <tr>
                <th>Task Name</th>
                <th>Completed</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
           @foreach($tasks as $task)
    <tr data-task-id="{{ $task->id }}">
        <td>{{ $task->name }}</td>
        <td>{{ $task->isComplited ? 'Yes' : 'No' }}</td>
        <td>{{ $task->description }}</td>
       <td class="task-buttons">
<a href="{{ route('tasks.edit', $task->id) }}" class="edit-button">Edit</a>
    <form action="{{ route('tasks.destroy', $task->id) }}" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit" onclick="return confirm('Are you sure?')">Delete</button>
    </form>
</td>

    </tr>
@endforeach
        </table>
    @else
        <p>No tasks found.</p>
    @endif
</main>

        </div>
    </div>


    <!-- Modal -->
<div class="modal task-modal fade" id="taskModal" tabindex="-1" role="dialog" aria-labelledby="taskModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content task-modal-content">
   <div class="modal-header task-modal-header">
  <h5 class="modal-title" id="taskModalLabel">Task Details (ID: <span id="taskId"></span>)</h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>

      <div class="modal-body task-modal-body">
        <strong>Task Name:</strong> <span id="taskName"></span><br>
        <strong>Completed:</strong> <span id="taskCompleted"></span><br>
        <strong>Description:</strong> <span id="taskDescription"></span><br>
      </div>
    </div>
  </div>
</div>



@section('scripts')
    <script>
      $(document).ready(function() {
  $('.table-dashboard tr').dblclick(function() {
    var taskId = $(this).data('task-id');

    // Faites une requête AJAX pour obtenir les détails de la tâche par ID
    $.get('/tasks/' + taskId, function(data) {
      // Remplissez les données de la tâche dans la modal
      $('#taskId').text(taskId);
      $('#taskName').text(data.name);
      $('#taskCompleted').text(data.isComplited ? 'Yes' : 'No');
      $('#taskDescription').text(data.description);

      // Affichez le modal
      $('#taskModal').modal('show');
    });
  });
});

    </script>
    <script>
$(document).ready(function() {
    $('#calendarLink').click(function(event) {
        event.preventDefault();
        var url = $(this).data('url');
        
        $.get(url, function(data) {
            $('#mainContent').html(data);
        });
    });
});
</script>
@endsection


@endsection
