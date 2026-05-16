<?php

namespace App\Http\Controllers\Admin;

use App\Models\Instructor;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class InstructorController extends Controller
{
    // Afficher tous les instructeurs
    public function index()
    {
        $instructors = Instructor::all();
        return view('admin.instructors.index', compact('instructors'));
    }

    // Afficher le formulaire de création d'instructeur
    public function create()
    {
        return view('admin.instructors.create');
    }

    // Enregistrer un nouvel instructeur
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'Nom' => 'required|string',
            'Prenom' => 'required|string',
        ]);

        Instructor::create($validatedData);
        
        return redirect()->route('admin.instructors.index')->with('success', 'Instructeur créé avec succès.');
    }

    // Afficher les détails d'un instructeur
    public function show($id)
    {
        $instructor = Instructor::findOrFail($id);
        return view('admin.instructors.show', compact('instructor'));
    }

    // Afficher le formulaire d'édition d'instructeur
    public function edit($id)
    {
        $instructor = Instructor::findOrFail($id);
        return view('admin.instructors.edit', compact('instructor'));
    }

    // Mettre à jour un instructeur existant
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'Nom' => 'required|string',
            'Prenom' => 'required|string',
        ]);

        $instructor = Instructor::findOrFail($id);
        $instructor->update($validatedData);
        
        return redirect()->route('admin.instructors.index')->with('success', 'Instructeur mis à jour avec succès.');
    }

    // Supprimer un instructeur
    public function destroy($id)
    {
        $instructor = Instructor::findOrFail($id);
        $instructor->delete();

        return redirect()->route('admin.instructors.index')->with('success', 'Instructeur supprimé avec succès.');
    }
}


