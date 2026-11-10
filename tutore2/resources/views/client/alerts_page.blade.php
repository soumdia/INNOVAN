@extends('layouts.client')

@section('title', 'CIE — Mes alertes')

@section('content')

{{-- Hero minimal --}}
<div class="client-hero-v2" style="padding-bottom:32px">
  <div class="hero-dots"></div>
  <div style="position:relative;z-index:2;max-width:1100px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px">
    <div>
      <div class="hero-v2-greeting">
        <span class="hero-v2-greeting-dot"></span>
        Vos alertes personnalisées
      </div>
      <h1 class="hero-v2-name" style="font-size:1.5rem;margin-bottom:0">
        @auth {{ Auth::user()->zone ?? 'Votre zone' }} @endauth
      </h1>
    </div>
    <a href="/client" class="btn-ghost" style="color:rgba(255,255,255,.7);border-color:rgba(255,255,255,.2)">
      <i class="fas fa-arrow-left"></i> Retour au tableau de bord
    </a>
  </div>
</div>

<div class="client-body">
  @include('client.alerts')
</div>

@endsection
