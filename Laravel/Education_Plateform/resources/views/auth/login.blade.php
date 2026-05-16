@extends('layouts.master')

@section('title', 'Login')

@section('content')
    @if(session('success'))
        <div style="color: green;">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div style="color: red;">
            @foreach($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </div>
    @endif
<div class="login">
    <div class="top-heading">
        <h2 class="login1">Login</h2>
    </div>
    <form class="form" action="{{ route('login.custom') }}" method="POST">
        @csrf
        <div class="button">
            <input class="placehoder" placeholder="Email or username*" type="text" name="email">
            {{-- <img class="icon" alt="" src="./public/icon.svg" /> --}}
        </div>
        <div class="button1">
            <input class="placehoder1" placeholder="Password*" type="password" name="password">
            <img class="icon1" alt="" src="./public/icon.svg" />
        </div>
        <div class="cate-1">
            <input class="component-2" type="checkbox" id="remember" name="remember">
            <label for="remember" class="home">Remember me</label>
            <label class="home1" for="remember">15</label>
            <img class="icon2" alt="" src="./public/icon.svg" />
        </div>
        <button class="button2" type="submit">
            <div class="posts-comment">Login</div>
        </button>
        <div class="lost-your-password">Lost your password?</div>
            <p class="lost-your-password">Vous n'avez pas de compte? <a href="{{ route('register') }}">Inscrivez-vous ici</a>.</p>

    </form>
</div>


@endsection
