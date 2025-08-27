@extends('layouts.main')

@section('content')
    {{-- Styles d’adaptation ciblés pour les composants Jetstream dans CETTE page uniquement --}}
    <style>
        /* Conteneur générique des sections Jetstream */
        .jet-section { margin-bottom: 1rem; }
        .jet-section .section-title { margin-bottom: .25rem; font-weight: 600; }
        .jet-section .section-desc { margin: 0; color: #6c757d; font-size: .875rem; }

        /* Label */
        .jet-form label, .jet-label, label[for] {
            display: inline-block;
            margin-bottom: .25rem;
            font-weight: 500;
        }

        /* Inputs, Selects, Textareas */
        .jet-form input[type="text"],
        .jet-form input[type="email"],
        .jet-form input[type="password"],
        .jet-form input[type="file"],
        .jet-form input[type="tel"],
        .jet-form input[type="number"],
        .jet-form select,
        .jet-form textarea {
            display: block;
            width: 100%;
            padding: .5rem .75rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #212529;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            appearance: none;
            border-radius: .375rem;
            transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        }
        .jet-form input:focus,
        .jet-form select:focus,
        .jet-form textarea:focus {
            color: #212529;
            background-color: #fff;
            border-color: #86b7fe;
            outline: 0;
            box-shadow: 0 0 0 .25rem rgba(13,110,253,.25);
        }

        /* Checkbox / Switch basiques */
        .jet-form .form-check { display: flex; align-items: center; gap: .5rem; }
        .jet-form .form-check-input { width: 1rem; height: 1rem; margin: 0; }
        .jet-form .form-check-label { margin: 0; }

        /* Boutons */
        .jet-btn { display: inline-block; font-weight: 500; border: 1px solid transparent; padding: .5rem .75rem; border-radius: .375rem; cursor: pointer; }
        .jet-btn-primary { color: #fff; background-color: #0d6efd; border-color: #0d6efd; }
        .jet-btn-primary:disabled { opacity: .65; }
        .jet-btn-outline { color: #0d6efd; background-color: transparent; border-color: #0d6efd; }
        .jet-btn-danger { color: #fff; background-color: #dc3545; border-color: #dc3545; }
        .jet-btn-secondary { color: #6c757d; background-color: transparent; border-color: #6c757d; }

        /* Messages d’erreurs */
        .jet-error { color: #dc3545; font-size: .875rem; display: block; margin-top: .25rem; }

        /* Alertes */
        .jet-alert { position: relative; padding: .75rem 1rem; border: 1px solid transparent; border-radius: .375rem; }
        .jet-alert-success { color: #0f5132; background-color: #d1e7dd; border-color: #badbcc; }
        .jet-alert-warning { color: #664d03; background-color: #fff3cd; border-color: #ffecb5; }

        /* QR/Recovery box */
        .jet-box { padding: .75rem; border: 1px solid #dee2e6; border-radius: .375rem; background: #fff; display: inline-block; }
        .jet-box-muted { background: #f8f9fa; }

        /* Modal simplifiée */
        .jet-modal-mask { position: fixed; inset: 0; background: rgba(0,0,0,.5); display: flex; align-items: center; justify-content: center; z-index: 1050; }
        .jet-modal { background: #fff; border-radius: .5rem; width: 100%; max-width: 520px; overflow: hidden; box-shadow: 0 .5rem 1rem rgba(0,0,0,.15); }
        .jet-modal-header { padding: .75rem 1rem; border-bottom: 1px solid #dee2e6; display: flex; align-items: center; justify-content: space-between; }
        .jet-modal-body { padding: 1rem; }
        .jet-modal-footer { padding: .75rem 1rem; border-top: 1px solid #dee2e6; display: flex; gap: .5rem; justify-content: flex-end; }
    </style>

    <div>
        @if (session('status'))
            <div class="jet-alert jet-alert-success mb-3">
                {{ session('status') }}
            </div>
        @endif

        <div class="row">
            {{-- En-tête profil --}}
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <img src="{{ auth()->user()->profile_photo_url ?? asset('assets/img/placeholder.jpg') }}" alt="avatar" class="border-radius-lg" width="64" height="64" style="object-fit: cover;">
                            </div>
                            <div class="col">
                                <h6 class="mb-1">{{ auth()->user()->name }}</h6>
                                <p class="text-sm text-secondary mb-0">{{ auth()->user()->email }}</p>
                            </div>
                            <div class="col-auto text-end">
                                @if(property_exists(auth()->user(), 'role') || array_key_exists('role', auth()->user()->getAttributes()))
                                    <span class="badge bg-dark me-2 text-capitalize">{{ auth()->user()->role ?? 'staff' }}</span>
                                @endif
                                @if(property_exists(auth()->user(), 'active') || array_key_exists('active', auth()->user()->getAttributes()))
                                    <span class="badge {{ auth()->user()->active ? 'bg-success' : 'bg-secondary' }}">{{ auth()->user()->active ? 'Actif' : 'Inactif' }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Informations de profil (Jetstream Livewire) --}}
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header pb-0">
                        <div class="jet-section">
                            <h6 class="section-title">Informations de profil</h6>
                            <p class="section-desc">Nom, email, photo</p>
                        </div>
                    </div>
                    <div class="card-body jet-form">
                        {{-- On garde la logique Livewire Jetstream, on ne change que le skin via CSS ci-dessus --}}
                        @livewire('profile.update-profile-information-form')
                    </div>
                </div>
            </div>

            {{-- Mot de passe (Jetstream Livewire) --}}
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header pb-0">
                        <div class="jet-section">
                            <h6 class="section-title">Sécurité</h6>
                            <p class="section-desc">Modifier le mot de passe</p>
                        </div>
                    </div>
                    <div class="card-body jet-form">
                        @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                            @livewire('profile.update-password-form')
                        @else
                            <p class="text-sm text-secondary mb-0">La mise à jour du mot de passe est désactivée.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- 2FA (Jetstream Livewire) --}}
            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <div class="col-lg-6 mt-4">
                    <div class="card h-100">
                        <div class="card-header pb-0">
                            <div class="jet-section">
                                <h6 class="section-title">Authentification à deux facteurs</h6>
                                <p class="section-desc">Renforce l’accès au compte</p>
                            </div>
                        </div>
                        <div class="card-body jet-form">
                            @livewire('profile.two-factor-authentication-form')
                        </div>
                    </div>
                </div>
            @endif

            {{-- Sessions navigateur (Jetstream Livewire) --}}
            <div class="col-lg-6 mt-4">
                <div class="card h-100">
                    <div class="card-header pb-0">
                        <div class="jet-section">
                            <h6 class="section-title">Sessions de navigateur</h6>
                            <p class="section-desc">Déconnecter d’autres appareils</p>
                        </div>
                    </div>
                    <div class="card-body jet-form">
                        @livewire('profile.logout-other-browser-sessions-form')
                    </div>
                </div>
            </div>

            {{-- Suppression du compte (Jetstream Livewire) --}}
            @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                <div class="col-12 mt-4">
                    <div class="card">
                        <div class="card-header pb-0">
                            <div class="jet-section">
                                <h6 class="section-title text-danger">Supprimer le compte</h6>
                                <p class="section-desc">Action irréversible</p>
                            </div>
                        </div>
                        <div class="card-body jet-form">
                            @livewire('profile.delete-user-form')
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
