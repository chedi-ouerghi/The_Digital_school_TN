<!-- profile.blade.php -->
@extends('layouts.master')

@section('title', 'Profile')

@section('content')
    <div class="profile-container">
        <h2 class="profile-heading">Welcome to Your Profile {{ $user->Nom }}</h2>
        @if ($user)
            <p class="profile-info"><strong>Name:</strong> {{ $user->Nom }} </p>
            <p class="profile-info"><strong>Prenom:</strong>  {{ $user->Prenom }}</p>
            <p class="profile-info"><strong>Email:</strong> {{ $user->Email }}</p>
            <p class="profile-info"><strong>Role:</strong> {{ $user->Role }}</p>
            <form action="{{ route('logout') }}" method="POST" class="logout-form">
                @csrf
                <button type="submit" class="logout-button">Logout</button>
            </form>
        @else
            <p class="not-logged-in">You are not logged in.</p>
        @endif
    </div>
@endsection
