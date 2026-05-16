@extends('layouts.app')

@section('content')
<div class="edit-page">
    <div class="form-container">
        <h1>Modifier une tâche</h1>
        <form action="{{ route('tasks.update', $task) }}" method="POST" class="form">
            @csrf
            @method('PUT') 
            <div class="form-group">
                <label>Nom de la tâche:</label>
                <input type="text" name="name" class="form-control" value="{{ $task->name }}">
                @error('name')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="checked-box-form">
                <label>
                    Tâche complétée
                </label>
                    <input type="checkbox" name="isComplited" {{ $task->isComplited ? 'checked' : '' }}>

            </div>
            <div class="form-group">
                <label>Description de la tâche:</label>
                <textarea name="description" class="form-control">{{ $task->description }}</textarea>
                @error('description')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary form-submit-btn">Modifier</button>
        </form>
    </div>
</div>
@endsection
