<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ $title ?? 'Connexion' }} | {{ config('app.name', 'Laravel') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- Nucleo Icons (Option: tu peux basculer sur local si déjà copié) -->
    <link rel="stylesheet" href="{{ asset('assets/css/nucleo-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/nucleo-svg.css') }}">
    <!-- Argon CSS -->
    <link id="pagestyle" href="{{ asset('assets/css/argon-dashboard.css?v=2.1.0') }}" rel="stylesheet" />
    <!-- Overrides persos -->
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
    @livewireStyles
</head>
<body class="">
<main class="main-content mt-0">
    <section>
        <div class="page-header min-vh-100">
            <div class="container">
                <div class="row">
                    <!-- Colonne formulaire (gauche) -->
                    <div class="col-xl-4 col-lg-5 col-md-7 d-flex flex-column mx-lg-0 mx-auto">
                        @yield('content')
                    </div>
                    <!-- Colonne illustration (droite) - Salon Gobel -->
                    <div class="col-6 d-lg-flex d-none h-100 my-auto pe-0 position-absolute top-0 end-0 text-center justify-content-center flex-column">
                        <div class="position-relative h-100 m-3 px-7 border-radius-lg d-flex flex-column justify-content-center overflow-hidden"
                             style="background: linear-gradient(135deg, #4a5d23 0%, #556b2f 25%, #6b8e23 50%, #4a5d23 75%, #3d4f1c 100%);">
                            <!-- Motif décoratif -->
                            <div class="position-absolute w-100 h-100" style="top: 0; left: 0; opacity: 0.1;">
                                <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
                                    <defs>
                                        <pattern id="scissors-pattern" x="0" y="0" width="100" height="100" patternUnits="userSpaceOnUse">
                                            <text x="25" y="50" font-size="30" fill="white" transform="rotate(-15, 25, 50)">✂</text>
                                        </pattern>
                                    </defs>
                                    <rect width="100%" height="100%" fill="url(#scissors-pattern)"/>
                                </svg>
                            </div>

                            <!-- Logo / Icône -->
                            <div class="position-relative mb-4">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle shadow-lg"
                                     style="width: 120px; height: 120px; background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border: 3px solid rgba(255,255,255,0.3);">
                                    <img src="{{ asset('assets/img/logCobel.png') }}" alt="Salon GoBeL Logo" style="width: 60px; height: 60px; object-fit: contain;">
{{--                                    <svg width="60" height="60" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">--}}
{{--                                        <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2Z" fill="white"/>--}}
{{--                                        <path d="M21 9H15V22H13V16H11V22H9V9H3V7H21V9Z" fill="white"/>--}}
{{--                                        <path d="M6.5 6.5L3 10M17.5 6.5L21 10" stroke="white" stroke-width="2" stroke-linecap="round"/>--}}
{{--                                        <circle cx="6" cy="11" r="2" fill="white"/>--}}
{{--                                        <circle cx="18" cy="11" r="2" fill="white"/>--}}
{{--                                    </svg>--}}
                                </div>
                            </div>

                            <!-- Nom du salon -->
                            <h1 class="text-white font-weight-bolder position-relative mb-2" style="font-size: 3rem; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); letter-spacing: 3px;">
                                Salon GoBeL
                            </h1>

                            <!-- Ligne décorative -->
                            <div class="d-flex align-items-center justify-content-center position-relative mb-3">
                                <div style="width: 60px; height: 2px; background: rgba(255,255,255,0.5);"></div>
                                <div class="mx-3">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                                        <path d="M9.64 7.64C9.87 7.14 10.35 6.5 11.05 5.88C11.85 5.15 12.5 4.5 12.5 3.75C12.5 3.03 11.97 2.5 11.25 2.5C10.53 2.5 10 3.03 10 3.75H8.5C8.5 2.23 9.73 1 11.25 1C12.77 1 14 2.23 14 3.75C14 5.22 12.84 6.22 12 6.95C11.5 7.4 11 7.85 10.75 8.35L9.64 7.64ZM13 20V22H11V20H13ZM7.5 18V16H16.5V18H7.5ZM19.12 11.46L21.19 9.39L22.6 10.81L20.53 12.88L19.12 11.46ZM1.39 10.81L2.81 9.39L4.88 11.46L3.47 12.88L1.39 10.81Z"/>
                                    </svg>
                                </div>
                                <div style="width: 60px; height: 2px; background: rgba(255,255,255,0.5);"></div>
                            </div>

                            <!-- Slogan -->
                            <p class="text-white position-relative mb-4" style="font-size: 1.1rem; font-style: italic; opacity: 0.9;">
                                "Votre beauté, notre passion"
                            </p>

                            <!-- Services -->
                            <div class="d-flex justify-content-center gap-4 position-relative mt-3">
                                <div class="text-center px-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2"
                                         style="width: 50px; height: 50px; background: rgba(255,255,255,0.2);">
                                        <i class="ni ni-scissors text-white" style="font-size: 1.2rem;">✂️</i>
                                    </div>
                                    <small class="text-white" style="font-size: 0.75rem;">Coiffure</small>
                                </div>
                                <div class="text-center px-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2"
                                         style="width: 50px; height: 50px; background: rgba(255,255,255,0.2);">
                                        <span style="font-size: 1.2rem;">💅</span>
                                    </div>
                                    <small class="text-white" style="font-size: 0.75rem;">Manucure</small>
                                </div>
                                <div class="text-center px-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2"
                                         style="width: 50px; height: 50px; background: rgba(255,255,255,0.2);">
                                        <span style="font-size: 1.2rem;">💆</span>
                                    </div>
                                    <small class="text-white" style="font-size: 0.75rem;">Soins</small>
                                </div>
                            </div>

                            <!-- Footer décoratif -->
                            <div class="position-absolute bottom-0 start-0 end-0 p-4">
                                <p class="text-white mb-0 position-relative" style="font-size: 0.8rem; opacity: 0.7;">
                                    Bienvenue dans votre espace de gestion
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Core JS -->
<script src="{{ asset('assets/js/core/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
<script src="{{ asset('assets/js/argon-dashboard.min.js?v=2.1.0') }}"></script>
@livewireScripts
</body>
</html>
