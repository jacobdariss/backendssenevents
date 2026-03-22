<!-- Horizontal Menu Start -->
<nav id="navbar_main" class="offcanvas mobile-offcanvas nav navbar navbar-expand-xl hover-nav horizontal-nav py-xl-0">
  <div class="container-fluid p-lg-0">
    <div class="offcanvas-header">
      <div class="navbar-brand p-0">
        @include('frontend::components.partials.logo')
      </div>
      <button type="button" class="btn-close p-0" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <ul class="navbar-nav iq-nav-menu list-unstyled" id="header-menu">

      {{-- Accueil --}}
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('user.login') ? 'active text-primary' : '' }}" href="{{ route('user.login') }}">
          <span class="item-name">Accueil</span>
        </a>
      </li>

      {{-- Séries TV --}}
      @if(isenablemodule('tvshow'))
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs(['tv-shows', 'tvshow-details', 'episode-details']) ? 'active text-primary' : '' }}" href="{{ route('tv-shows') }}">
          <span class="item-name">Séries TV</span>
        </a>
      </li>
      @endif

      {{-- Emissions --}}
      @if(isenablemodule('movie'))
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs(['movies', 'movie-details']) ? 'active text-primary' : '' }}" href="{{ route('movies') }}">
          <span class="item-name">Emissions</span>
        </a>
      </li>
      @endif

      {{-- Vidéos --}}
      @if(isenablemodule('video'))
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs(['videos', 'video-details', 'video-detail']) ? 'active text-primary' : '' }}" href="{{ route('videos') }}">
          <span class="item-name">Vidéos</span>
        </a>
      </li>
      @endif

      {{-- À Venir --}}
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('comingsoon') ? 'active text-primary' : '' }}" href="{{ route('comingsoon') }}">
          <span class="item-name">À Venir</span>
        </a>
      </li>

      {{-- TV en Direct --}}
      @if(isenablemodule('livetv'))
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('livetv') ? 'active text-primary' : '' }}" href="{{ route('livetv') }}">
          <span class="item-name">TV en Direct</span>
        </a>
      </li>
      @endif

    </ul>
  </div>
</nav>
<!-- Horizontal Menu End -->

