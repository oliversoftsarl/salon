@extends('layouts.auth')

@section('content')
    <div class="card card-plain">
        <div class="card-header pb-0 text-start">
            <h4 class="font-weight-bolder">Créer un compte</h4>
            <p class="mb-0">Renseigne les informations ci-dessous</p>
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

            <form role="form" method="POST" action="{{ route('register') }}">
                @csrf
                <div class="mb-3">
                    <input type="text" class="form-control form-control-lg" placeholder="Nom complet" name="name" value="{{ old('name') }}" required autofocus>
                </div>
                <div class="mb-3">
                    <input type="email" class="form-control form-control-lg" placeholder="Email" name="email" value="{{ old('email') }}" required>
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control form-control-lg" placeholder="Mot de passe" name="password" required>
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control form-control-lg" placeholder="Confirmer le mot de passe" name="password_confirmation" required>
                </div>
                <div class="mb-3">
                    <select class="form-select form-select-lg" name="role" required>
                        <option value="staff" @selected(old('role')==='staff')>Staff</option>
                        <option value="cashier" @selected(old('role')==='cashier')>Cashier</option>
                        <option value="admin" @selected(old('role')==='admin')>Admin</option>
                    </select>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-lg w-100 mt-4 mb-0" style="background: linear-gradient(135deg, #4a5d23 0%, #6b8e23 100%); border: none; color: white;">Créer le compte</button>
                </div>
            </form>
        </div>
        <div class="card-footer text-center pt-0 px-lg-2 px-1">
            <p class="mb-4 text-sm mx-auto">
                Déjà inscrit ?
                <a href="{{ route('login') }}" class="font-weight-bold" style="color: #4a5d23;">Se connecter</a>
            </p>
        </div>
    </div>
@endsection
