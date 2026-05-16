@extends('layouts.app')

@section('content')
    <div class="container-todoList">
        <h1>Liste des tâches</h1>
        <a href="{{ route('tasks.create') }}" class="btn btn-primary">Ajouter une tâche</a>
        <div class="todo-list"> 
            @foreach($tasks as $task)
                <div class="todo"> 
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">{{ $task->name }}</h5>
                            <p class="card-text">Description: {{ $task->description }}</p>
                            <p class="card-text">Complétée: {{ $task->isComplited ? 'Oui' : 'Non' }}</p>
                            <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-primary">Modifier</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
