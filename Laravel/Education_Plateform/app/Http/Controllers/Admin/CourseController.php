<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Cours;
use Illuminate\Routing\Controller;

class CourseController extends Controller
{
    // Afficher tous les cours

      public function indexstudent()
    {
        $courses = Cours::all();
        return view('cours.cours', compact('courses'));
    }
    
    public function index()
    {
        $courses = Cours::all();
        return view('admin.courses.index', compact('courses'));
    }

    // Afficher le formulaire de création de cours
    public function create()
    {
        return view('admin.courses.create');
    }

    // Enregistrer un nouveau cours
    public function store(Request $request)
    {
        // Logique de validation et de sauvegarde
        $validatedData = $request->validate([
            'Titre' => 'required|string|max:255',
            'Description' => 'required|string',
            // Ajoutez d'autres règles de validation au besoin
        ]);

        Cours::create($validatedData);

        return redirect()->route('courses.index')
            ->with('success', 'Le cours a été créé avec succès.');
    }

    // Afficher les détails d'un cours
    public function show($id)
    {
        $course = Cours::findOrFail($id);
        return view('admin.courses.show', compact('course'));
    }

    // Afficher le formulaire d'édition d'un cours
    public function edit($id)
    {
        $course = Cours::findOrFail($id);
        return view('admin.courses.edit', compact('course'));
    }

    // Mettre à jour un cours existant
    public function update(Request $request, $id)
    {
        // Logique de validation et de mise à jour
        $validatedData = $request->validate([
            'Titre' => 'required|string|max:255',
            'Description' => 'required|string',
            // Ajoutez d'autres règles de validation au besoin
        ]);

        Cours::whereId($id)->update($validatedData);

        return redirect()->route('courses.index')
            ->with('success', 'Le cours a été mis à jour avec succès.');
    }

    // Supprimer un cours
    public function destroy($id)
    {
        // Logique de suppression
        $course = Cours::findOrFail($id);
        $course->delete();

        return redirect()->route('courses.index')
            ->with('success', 'Le cours a été supprimé avec succès.');
    }
}
