{{-- resources/views/welcome.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="welcome-wrap">
  <div class="card shadow-sm mb-3">
    <div class="card-body">
      <h1 class="h4 mb-1">
        Welcome
        @auth {{ Auth::user()->email }} @else Guest @endauth
      </h1>
      <p class="text-muted mb-0">Use the quick actions to jump into the app.</p>
    </div>
  </div>

  
</div>
@endsection
