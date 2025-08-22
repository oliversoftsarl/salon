@extends('layouts.auth')

@section('content')
    <div class="card card-plain">
        <div class="card-header pb-0 text-start">
            <h4 class="font-weight-bolder">Se connecter</h4>
            <p class="mb-0">Entre ton email et ton mot de passe</p>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form role="form" method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <input type="email" class="form-control form-control-lg" placeholder="Email" name="email" value="{{ old('email') }}" required autofocus>
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control form-control-lg" placeholder="Mot de passe" name="password" required>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="rememberMe" name="remember">
                    <label class="form-check-label" for="rememberMe">Se souvenir de moi</label>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-lg btn-primary w-100 mt-4 mb-0">Se connecter</button>
                </div>
            </form>
        </div>
        <div class="card-footer text-center pt-0 px-lg-2 px-1">
            <p class="mb-4 text-sm mx-auto">
                Pas encore de compte ?
                <a href="{{ route('register') }}" class="text-primary text-gradient font-weight-bold">Créer un compte</a>
            </p>
        </div>
    </div>
@endsection
