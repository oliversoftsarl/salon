<aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4 " id="sidenav-main">
    <div class="sidenav-header">
      <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
      <a class="navbar-brand m-0" href="{{ route('dashboard') }}">
        <img src="{{ asset('assets/img/logCobel.png') }}" width="26" height="26" class="navbar-brand-img h-100" alt="main_logo">
        <span class="ms-1 font-weight-bold">{{ config('app.name', 'Laravel') }}</span>
      </a>
    </div>
    <hr class="horizontal dark mt-0">
    <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-tv-2 text-dark text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Dashboard</span>
          </a>
        </li>

        {{-- Domaine: Services --}}
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}" href="{{ route('services.index') }}">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-bullet-list-67 text-dark text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Services</span>
          </a>
        </li>

        {{-- Domaine: Produits --}}
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-box-2 text-dark text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Produits</span>
          </a>
        </li>

        {{-- Domaine: Clients --}}
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}" href="{{ route('clients.index') }}">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-circle-08 text-dark text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Clients</span>
          </a>
        </li>

        {{-- Domaine: Rendez-vous --}}
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('appointments.*') ? 'active' : '' }}" href="{{ route('appointments.calendar') }}">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-calendar-grid-58 text-dark text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Rendez-vous</span>
          </a>
        </li>

        {{-- Domaine: Staff --}}
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('staff.*') ? 'active' : '' }}" href="{{ route('staff.schedule') }}">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-single-02 text-dark text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Staff</span>
          </a>
        </li>

        {{-- Domaine: Caisse / POS --}}
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('pos.*') ? 'active' : '' }}" href="{{ route('pos.checkout') }}">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-credit-card text-dark text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Caisse</span>
          </a>
        </li>

        {{-- Séparateur / Account pages --}}
        <li class="nav-item mt-3">
          <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Compte</h6>
        </li>

        @auth
          {{-- Profil (Jetstream/Fortify) --}}
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('profile.show') ? 'active' : '' }}" href="{{ route('profile.show') }}">
              <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                <i class="ni ni-settings-gear-65 text-dark text-sm opacity-10"></i>
              </div>
              <span class="nav-link-text ms-1">Mon profil</span>
            </a>
          </li>
          <li class="nav-item">
                  @php $active = request()->routeIs('users.*'); @endphp
                  <a class="nav-link {{ request()->routeIs('user.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                      <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                          <i class="ni-single-02 text-dark text-sm opacity-10"></i>
                      </div>
                      <span class="nav-link-text ms-1">Utilisateurs</span>
                  </a>
          </li>
          {{-- Déconnexion --}}
          <li class="nav-item">
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
              @csrf
              <button type="submit" class="nav-link btn btn-link px-0 text-start">
                <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-inline-flex align-items-center justify-content-center">
                  <i class="ni ni-button-power text-dark text-sm opacity-10"></i>
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
