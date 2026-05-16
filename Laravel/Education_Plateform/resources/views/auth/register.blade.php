<!-- resources/views/auth/register.blade.php -->

@extends('layouts.master')

@section('title', 'Inscription')

@section('content')

  <div class="register">
    <div class="top-heading">
        <h2 class="login1">Inscription</h2>
    </div>
    <form class="form_register" action="{{ route('register') }}" method="POST">
        @csrf
        @if ($errors->any())
            <div>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="button">
            {{-- <label for="nom">Nom:</label> --}}
            <input type="text" id="nom" name="Nom" value="{{ old('Nom') }}" placeholder="nom*" class="placehoder">
        </div>
        <div class="button">
            {{-- <label for="prenom">Prénom:</label> --}}
            <input type="text" id="prenom" name="Prenom" value="{{ old('Prenom') }}" placeholder="Prenom*" class="placehoder">
        </div>
        <div class="button">
            {{-- <label for="email">Email:</label> --}}
            <input type="email" id="email" name="Email" value="{{ old('Email') }}" class="placehoder" placeholder="email*">
        </div>
        <div class="button">
            {{-- <label for="password">Mot de passe:</label> --}}
            <input type="password" id="password" name="MotDePasse" class="placehoder" placeholder="MotDePasse*">
        </div>
        <div class="button">
            {{-- <label for="password_confirmation">Confirmer le mot de passe:</label> --}}
            <input type="password" id="password_confirmation" name="MotDePasse_confirmation" placeholder=" confiramation du Mot De Passe*" class="placehoder">
        </div>
        <div class="button">
            {{-- <label for="role">Rôle:</label> --}}
            <select id="role" name="Role" class="placehoder">
                <option value="admin">Admin</option>
                <option value="etudiant">Étudiant</option>
            </select>
        </div>
        <button class="button2" type="submit">
            <div class="posts-comment">S'inscrire</div>
        </button>
                    <p class="lost-your-password">Vous avez un compte? <a href="{{ route('login') }}">Login ici</a>.</p>

    </form>
</div>

@endsection
