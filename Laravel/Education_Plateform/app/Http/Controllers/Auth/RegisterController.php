<?php

namespace App\Http\Controllers\Auth;

use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }
  public function register(Request $request)
{
    // Validation des données du formulaire
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:utilisateur',
        'password' => 'required|string|min:8|confirmed',
        'role' => 'required|in:etudiant,admin', // Assurez-vous que le rôle est valide
    ]);

    // Créer un nouvel utilisateur
    $user = Utilisateur::create([
        'Nom' => $request->name,
        'Email' => $request->email,
        'MotDePasse' => Hash::make($request->password),
        'Role' => $request->role, // Utilisez la valeur du champ 'role' du formulaire
    ]);

    // Authentifier l'utilisateur nouvellement enregistré
    Auth::login($user);

    return redirect()->route('login')->with('success', 'Registration successful!');
}

}
