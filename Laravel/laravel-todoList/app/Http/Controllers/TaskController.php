<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Validation\Rule;


class TaskController extends Controller
{

    public function index()
{
    $tasks = Task::all();
    return view('home.Home', compact('tasks'));
}

public function getById($id)
{
    $task = Task::findOrFail($id);
    return response()->json($task);
}


    public function todolist()
    {
        $tasks = Task::all();
        return view('tasks.todolist', compact('tasks'));
    }
    
    public function create()
    {
        return view('tasks.create');
    }

    public function store(Request $request)
    {
       $request->validate([
    'name' => [
        'required',
        'string',
        'max:150',
        function ($attribute, $value, $fail) {
            // Vérifier si le nom commence par une lettre majuscule
            if (mb_substr($value, 0, 1) !== mb_strtoupper(mb_substr($value, 0, 1))) {
                $fail('Le nom doit commencer par une lettre majuscule.');
            }
            // Vérifier s'il n'y a pas d'espace à la fin du nom
            if (mb_substr($value, -1) === ' ') {
                $fail('Le nom ne doit pas se terminer par un espace.');
            }
            // Vérifier si le nom contient des caractères invalides
            if (preg_match('/[^a-zA-Z\s]/', $value)) {
                $fail('Le nom ne doit contenir que des lettres et des espaces.');
            }
        },
        'unique:tasks,name',
    ],
    'description' => 'required|string|max:250',
]);


        $task = new Task();
        $task->name = $request->input('name');
$task->isComplited = $request->has('isComplited');
        $task->description = $request->input('description');
        $task->save();

 return redirect()->route('tasks.index');
    }

    public function edit(Task $task)
    {
        return view('tasks.edit', compact('task'));
    }

public function update(Request $request, Task $task)
{
    $request->validate([
        'name' => [
            'required',
            'string',
            'max:150',
            function ($attribute, $value, $fail) use ($task) {
                // Vérifier si le nom commence par une lettre majuscule
                if (mb_substr($value, 0, 1) !== mb_strtoupper(mb_substr($value, 0, 1))) {
                    $fail('Le nom doit commencer par une lettre majuscule.');
                }
                // Vérifier s'il n'y a pas d'espace à la fin du nom
                if (mb_substr($value, -1) === ' ') {
                    $fail('Le nom ne doit pas se terminer par un espace.');
                }
                // Vérifier si le nom contient des caractères invalides
                if (preg_match('/[^a-zA-Z\s]/', $value)) {
                    $fail('Le nom ne doit contenir que des lettres et des espaces.');
                }
            },
            // Exclure le nom de la tâche actuelle de la règle d'unicité
            Rule::unique('tasks')->ignore($task->id),
        ],
        'description' => 'required|string|max:250',
    ]);

    $task->name = $request->input('name');
    $task->isComplited = $request->has('isComplited');
    $task->description = $request->input('description');
    $task->save();

    return redirect()->route('tasks.index');
}


    public function destroy(Task $task)
    {
        $task->delete();
 return redirect()->route('tasks.index');

       }
}
