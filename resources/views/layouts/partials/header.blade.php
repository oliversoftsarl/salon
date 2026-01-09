<aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4" id="sidenav-main">
  <div class="sidenav-header">
    <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
    <a class="navbar-brand m-0" href="{{ auth()->user()?->role === 'cashier' ? route('pos.checkout') : route('dashboard') }}">
      <img src="{{ asset('assets/img/logCobel.png') }}" width="26" height="26" class="navbar-brand-img h-100" alt="main_logo">
      <span class="ms-1 font-weight-bold">{{ config('app.name', 'Laravel') }}</span>
    </a>
  </div>
  <hr class="horizontal dark mt-0 mb-2">

  <div class="navbar-collapse w-auto h-auto" id="sidenav-collapse-main" style="overflow: visible;">
    <ul class="navbar-nav">

      {{-- Dashboard - Pas pour les caissiers --}}
      @if(auth()->user()?->role !== 'cashier')
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
          <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
            <i class="ni ni-tv-2 text-dark text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Dashboard</span>
        </a>
      </li>
      @endif

      {{-- Section Caisse - Pour admin et caissier --}}
      @if(in_array(auth()->user()?->role, ['admin', 'cashier']))
      <li class="nav-item mt-2">
        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Caisse</h6>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('pos.checkout') ? 'active' : '' }}" href="{{ route('pos.checkout') }}">
          <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
            <i class="ni ni-cart text-success text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Nouvelle vente</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('pos.transactions') ? 'active' : '' }}" href="{{ route('pos.transactions') }}">
          <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
            <i class="ni ni-bullet-list-67 text-info text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Liste des ventes</span>
        </a>
      </li>
      @endif

      {{-- Gestion de Caisse - Admin seulement --}}
      @if(auth()->user()?->role === 'admin')
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('cash.register') ? 'active' : '' }}" href="{{ route('cash.register') }}">
          <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
            <i class="ni ni-money-coins text-warning text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Gestion Caisse</span>
        </a>
      </li>
      @endif

      {{-- Section Gestion - Pas pour les caissiers --}}
      @if(auth()->user()?->role !== 'cashier')
      <li class="nav-item mt-3">
        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Gestion</h6>
      </li>

      {{-- Services --}}
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}" href="{{ route('services.index') }}">
          <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
            <i class="ni ni-scissors text-dark text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Services</span>
        </a>
      </li>

      {{-- Produits --}}
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
          <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
            <i class="ni ni-box-2 text-dark text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Produits</span>
        </a>
      </li>

      {{-- Clients --}}
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}" href="{{ route('clients.index') }}">
          <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
            <i class="ni ni-circle-08 text-dark text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Clients</span>
        </a>
      </li>

      {{-- Rendez-vous --}}
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('appointments.*') ? 'active' : '' }}" href="{{ route('appointments.calendar') }}">
          <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
            <i class="ni ni-calendar-grid-58 text-dark text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Rendez-vous</span>
        </a>
      </li>
      @endif

      {{-- Section Admin uniquement --}}
      @if(auth()->user()?->role === 'admin')
      <li class="nav-item mt-3">
        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Administration</h6>
      </li>

      {{-- Staff --}}
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('staff.schedule') ? 'active' : '' }}" href="{{ route('staff.schedule') }}">
          <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
            <i class="ni ni-single-02 text-dark text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Staff</span>
        </a>
      </li>

      {{-- Performance Prestataires --}}
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('staff.performance') ? 'active' : '' }}" href="{{ route('staff.performance') }}">
          <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
            <i class="ni ni-chart-bar-32 text-primary text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Performance Prestataires</span>
        </a>
      </li>

      {{-- Approvisionnements --}}
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('inventory.supplies') ? 'active' : '' }}" href="{{ route('inventory.supplies') }}">
          <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
            <i class="ni ni-delivery-fast text-dark text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Approvisionnements</span>
        </a>
      </li>

      {{-- Consommations --}}
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('inventory.consumptions') ? 'active' : '' }}" href="{{ route('inventory.consumptions') }}">
          <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
            <i class="ni ni-archive-2 text-dark text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Consommations</span>
        </a>
      </li>

      {{-- Fiche de Stock --}}
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('inventory.stock-sheet') ? 'active' : '' }}" href="{{ route('inventory.stock-sheet') }}">
          <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
            <i class="ni ni-single-copy-04 text-info text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Fiche de Stock</span>
        </a>
      </li>

      {{-- Utilisateurs --}}
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
          <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
            <i class="ni ni-badge text-dark text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Utilisateurs</span>
        </a>
      </li>

      {{-- Taux de Change --}}
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('settings.exchange-rates') ? 'active' : '' }}" href="{{ route('settings.exchange-rates') }}">
          <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
            <i class="ni ni-money-coins text-success text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Taux de Change</span>
        </a>
      </li>
      @endif

      {{-- Section Inventaire pour staff --}}
      @if(auth()->user()?->role === 'staff')
      <li class="nav-item mt-3">
        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Inventaire</h6>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('inventory.supplies') ? 'active' : '' }}" href="{{ route('inventory.supplies') }}">
          <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
            <i class="ni ni-delivery-fast text-dark text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Approvisionnements</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('inventory.consumptions') ? 'active' : '' }}" href="{{ route('inventory.consumptions') }}">
          <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
            <i class="ni ni-archive-2 text-dark text-sm opacity-10"></i>
          </div>
          <span class="nav-link-text ms-1">Consommations</span>
        </a>
      </li>
      @endif

      {{-- Séparateur / Account pages --}}
      <li class="nav-item mt-3">
        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Mon compte</h6>
      </li>

      @auth
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('profile.show') ? 'active' : '' }}" href="{{ route('profile.show') }}">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-settings-gear-65 text-dark text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Mon profil</span>
          </a>
        </li>

       <li class="nav-item">
          <form method="POST" action="{{ route('logout') }}" class="d-inline w-100">
            @csrf
            <button type="submit" class="nav-link btn btn-link px-3 text-start w-100 text-decoration-none d-flex align-items-center">
              <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                <i class="ni ni-button-power text-danger text-sm opacity-10"></i>
              </div>
              <span class="nav-link-text ms-1">Se déconnecter</span>
            </button>
          </form>
        </li>
      @endauth

      @guest
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}" href="{{ route('login') }}">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-single-copy-04 text-dark text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Se connecter</span>
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('register') ? 'active' : '' }}" href="{{ route('register') }}">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-collection text-dark text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Créer un compte</span>
          </a>
        </li>
      @endguest
    </ul>
  </div>
</aside>
