<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;



class UserController extends Controller
{
    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
/**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
     public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            // If validation fails, throw a ValidationException
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
        ]);

        // Redirect to the login page after successful registration
        return redirect()->route('login')->with('success', 'Registration successful. Please login.');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
public function login(Request $request): RedirectResponse
{
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        // Vérifier si l'utilisateur authentifié est administrateur
        if (Auth::user()->email === 'admin@gmail.com') {
            // Rediriger l'administrateur vers le chemin spécifié après la connexion
            return redirect('/');
        } else {
            // Si l'utilisateur authentifié n'est pas administrateur, rediriger vers le profil
            return redirect()->route('profile');
        }
    }

    // Si l'authentification échoue, rediriger vers le formulaire de connexion avec un message d'erreur
    return redirect()->route('login')->with('error', 'Invalid email or password.');
}



    /**
     * Get the authenticated User's profile.
     *
     * @return \Illuminate\Http\JsonResponse
     */
   public function getProfile(): View
    {
        $user = Auth::user();
        return view('profile', ['user' => $user]);
    }

    public function showRegistrationForm()
    {
        return view('Auth.register');
    }
    
    public function showLoginForm()
    {
        return view('Auth.login');
    }

// UserController.php


    /**
     * Supprimer un utilisateur.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteUser($id)
    {
        // Vérifier si l'utilisateur authentifié est administrateur
        if (!Auth::check() || Auth::user()->email !== 'admin@gmail.com') {
            return redirect()->route('login')->with('error', 'Vous n\'êtes pas autorisé à effectuer cette action.');
        }

        // Supprimer l'utilisateur par son ID
        User::destroy($id);

        // Rediriger vers une page ou envoyer une réponse JSON appropriée
        return redirect()->back()->with('success', 'Utilisateur supprimé avec succès.');
    }

    /**
     * Obtenir tous les utilisateurs enregistrés.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAllUsers()
    {
        // Vérifier si l'utilisateur authentifié est administrateur
        if (!Auth::check() || Auth::user()->email !== 'admin@gmail.com') {
            return redirect()->route('login')->with('error', 'Vous n\'êtes pas autorisé à effectuer cette action.');
        }

        // Récupérer tous les utilisateurs enregistrés
        $users = User::all();

        // Vous pouvez renvoyer une vue ou une réponse JSON selon vos besoins
        return view('all_users', ['users' => $users]);
    }


}
