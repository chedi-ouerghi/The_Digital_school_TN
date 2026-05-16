<?php
// app/Http/Controllers/CustomAuthController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Utilisateur;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CustomAuthController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

public function customLogin(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = Utilisateur::where('Email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->MotDePasse)) {
        return redirect()->route('login')->withErrors(['email' => 'Adresse e-mail ou mot de passe incorrect.']);
    }

    Auth::login($user);

    session(['user' => $user]); // Stocker l'utilisateur dans la session Laravel
    
    session()->flash('success', 'Connexion réussie');
    
    return redirect()->route('profile');
}



    public function registration()
    {
        return view('auth.register');
    }

    public function customRegistration(Request $request)
    {
        $request->validate([
            'Nom' => 'required|string|max:50',
            'Prenom' => 'required|string|max:50',
            'Email' => 'required|string|email|max:100|unique:utilisateur,Email',
            'MotDePasse' => 'required|string|min:6|confirmed',
            'Role' => 'required|in:admin,etudiant',
        ]);

        $data = $request->all();
        $this->create($data);

        return redirect()->route('login')->withSuccess('Vous êtes inscrit.');
    }

    public function create(array $data)
    {
        return Utilisateur::create([
            'Nom' => $data['Nom'],
            'Prenom' => $data['Prenom'],
            'Email' => $data['Email'],
            'MotDePasse' => Hash::make($data['MotDePasse']),
            'Role' => $data['Role']
        ]);
    }

    public function signOut()
    {
        Auth::logout();
        session()->forget('user'); 
        return redirect()->route('login');
    }
public function profile()
{
    // Récupérer l'utilisateur à partir de la session Laravel
    $user = session('user');

    if ($user) {
        return view('auth.profile', compact('user'));
    } else {
        return redirect()->route('login');
    }
}



}
