<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CategoryController extends Controller
{
    // Afficher toutes les catégories
    public function index()
    {
        $categories = Category::all();
        return view('admin.categories.categoriesCrud', compact('categories'));
    }

    // Afficher le formulaire de création de catégorie
    public function create()
    {
        return view('admin.categories.create');
    }

    // Enregistrer une nouvelle catégorie
    public function store(Request $request)
    {
        // Valider les données du formulaire
        $request->validate([
            'name' => 'required|unique:categories|max:255',
            // Autres règles de validation...
        ]);

        // Créer une nouvelle catégorie
        Category::create([
            'name' => $request->name,
            // Autres champs...
        ]);

        return redirect()->route('categories.index')->with('success', 'Catégorie créée avec succès.');
    }

    // Afficher les détails d'une catégorie
    public function show($id)
    {
        $category = Category::findOrFail($id);
        return view('admin.categories.show', compact('category'));
    }

    // Afficher le formulaire d'édition de catégorie
    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return view('admin.categories.edit', compact('category'));
    }

    // Mettre à jour une catégorie existante
    public function update(Request $request, $id)
    {
        // Valider les données du formulaire
        $request->validate([
            'name' => 'required|unique:categories|max:255',
            // Autres règles de validation...
        ]);

        // Mettre à jour la catégorie
        $category = Category::findOrFail($id);
        $category->update([
            'name' => $request->name,
            // Autres champs...
        ]);

        return redirect()->route('categories.index')->with('success', 'Catégorie mise à jour avec succès.');
    }

    // Supprimer une catégorie
    public function destroy($id)
    {
        // Rechercher la catégorie
        $category = Category::findOrFail($id);

        // Supprimer la catégorie
        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Catégorie supprimée avec succès.');
    }
}
