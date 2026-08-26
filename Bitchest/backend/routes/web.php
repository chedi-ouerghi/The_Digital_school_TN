<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
// Routes de diagnostic disponibles dans l'environnement local
if (app()->environment('local')) {
    // Test 1 : Vérifier que la session fonctionne
    Route::get('/debug/session', function (\Illuminate\Http\Request $request) {
        $request->session()->put('test', 'OK');
        return response()->json([
            'session' => session('test'),
            'session_id' => session()->getId(),
            'cookies' => array_keys($request->cookies->all()),
        ])->cookie('test-cookie', 'test-value', 60);
    });

    // Test 2 : Vérifier les cookies reçus
    Route::get('/debug/cookies', function (\Illuminate\Http\Request $request) {
        return response()->json([
            'all_cookies' => $request->cookies->all(),
            'laravel_session' => $request->cookie('LARAVEL_SESSION') ? '✅ Present' : '❌ Missing',
            'xsrf_token' => $request->cookie('XSRF-TOKEN') ? '✅ Present' : '❌ Missing',
        ]);
    });

    // Test 3 : Vérifier l'authentification
    Route::get('/debug/auth', function (\Illuminate\Http\Request $request) {
        return response()->json([
            'user' => $request->user() ? $request->user()->only(['id', 'email', 'role']) : null,
            'authenticated' => auth()->check(),
            'session_data' => session()->all(),
        ]);
    });

    // Test 4 : Login de test
    Route::post('/debug/login', function (\Illuminate\Http\Request $request) {
        $user = \App\Models\User::first();
        if (!$user) {
            return response()->json(['error' => 'No user found'], 404);
        }
        
        auth()->login($user);
        
        return response()->json([
            'user' => $user->only(['id', 'email', 'name', 'role']),
            'message' => 'Debug login successful',
        ]);
    });

    // Test 5 : Logout
    Route::post('/debug/logout', function (\Illuminate\Http\Request $request) {
        auth()->logout();
        $request->session()->invalidate();
        
        return response()->json(['message' => 'Debug logout successful']);
    });
}