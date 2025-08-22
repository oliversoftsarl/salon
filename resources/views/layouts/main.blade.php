<?php /** @var \Illuminate\Support\ViewErrorBag $errors */ ?>
    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'Laravel') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Argon Dashboard CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/nucleo-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/nucleo-svg.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/argon-dashboard.css?v=2.1.0') }}">

    <!-- Optionnel: icônes Font Awesome (si utilisées) -->
{{--    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SOMEHASH" crossorigin="anonymous" referrerpolicy="no-referrer" />--}}
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- Nucleo Icons -->
    <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-svg.css" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <!-- Tes overrides -->
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
    @livewireStyles
</head>
<body class="g-sidenav-show bg-gray-100">
<div class="min-height-300 bg-dark position-absolute w-100"></div>
@includeWhen(View::exists('layouts.partials.header'), 'layouts.partials.header')
<main class="main-content position-relative border-radius-lg ">
    @includeIf('layouts.partials.navbar', ['title' => $title ?? null])
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
    <div class="container-fluid py-4">
        {{ $slot ?? '' }}
        @yield('content')
    </div>
</main>

<!-- Core JS Argon -->
<script src="{{ asset('assets/js/core/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
<script>
  // Initialisation du scrollbar comme dans le template Argon
  var win = navigator.platform.indexOf('Win') > -1;
  if (win && document.querySelector('#sidenav-scrollbar')) {
    Scrollbar.init(document.querySelector('#sidenav-scrollbar'), { damping: '0.5' });
  }
</script>
<script src="{{ asset('assets/js/argon-dashboard.min.js') }}"></script>

<!-- Optionnel: ton script -->
<script src="{{ asset('assets/js/app.js') }}"></script>
@livewireScripts
</body>
</html>
