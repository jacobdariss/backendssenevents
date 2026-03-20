@extends('backend.layouts.app')

@section('title') {{ __('partner::partner.lbl_partner') }} @endsection

@section('content')
<div class="text-center py-5">
    <i class="ph ph-handshake" style="font-size:4rem;opacity:.3;"></i>
    <h5 class="mt-3 text-muted">{{ __('partner::partner.no_account_linked') }}</h5>
    <p class="text-muted small">{{ __('messages.contact_admin') }}</p>
</div>
@endsection
