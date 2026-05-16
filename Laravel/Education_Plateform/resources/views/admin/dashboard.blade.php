@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Statistiques</div>

                    <div class="card-body">
                        <p>Total des utilisateurs : {{ $totalUsers }}</p>
                        <p>Total des cours : {{ $totalCourses }}</p>
                        <!-- Ajoutez d'autres statistiques si nécessaire -->
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Actions Rapides</div>

                    <div class="card-body">
                        <a href="{{ route('admin.courses.index') }}" class="btn btn-primary">Gérer les cours</a>
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-primary">Gérer les catégories</a>
                        <!-- Ajoutez d'autres liens rapides si nécessaire -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
