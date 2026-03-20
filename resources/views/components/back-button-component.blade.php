@php
    // Si $route commence par http ou /, c'est déjà une URL — sinon c'est un nom de route
    $href = (str_starts_with($route, 'http') || str_starts_with($route, '/'))
        ? $route
        : route($route);
@endphp
<a href="{{ $href }}" class="btn btn-link d-inline-flex align-items-center gap-1 p-0 mb-3 fs-3"><i class="ph ph-caret-double-left"></i>{{ __('messages.back') }}</a>
