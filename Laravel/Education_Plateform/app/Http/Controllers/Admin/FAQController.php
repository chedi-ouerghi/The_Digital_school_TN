<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use App\Models\FAQ;
use Illuminate\Http\Request;

class FAQController extends Controller
{
    public function index()
    {
        $faqs = FAQ::all();
        return view('admin.faqs.index', compact('faqs'));
    }

    public function create()
    {
        // Méthode pour afficher le formulaire de création d'une nouvelle FAQ
    }

    public function store(Request $request)
    {
        // Méthode pour sauvegarder une nouvelle FAQ dans la base de données
    }
}
