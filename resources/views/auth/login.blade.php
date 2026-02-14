@extends('layouts.guest')

@section('title', 'Login')
@section('card-title', 'Welcome Back')

@section('content')
@if ($errors->any())
    <div class="alert mb-4">
        <i class="fas fa-exclamation-circle mr-1"></i>
        @foreach ($errors->all() as $error)<span>{{ $error }}</span>@endforeach
    </div>
@endif

<form action="{{ route('login.store') }}" method="POST">
    @csrf
    <div class="form-group">
        <label for="email">Email Address</label>
        <div class="input-wrap">
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus class="form-control" placeholder="Enter your email">
        </div>
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <div class="input-wrap">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" id="password" required class="form-control" placeholder="Enter your password">
        </div>
    </div>
    <div class="form-group d-flex align-items-center justify-content-between">
        <div class="custom-control custom-checkbox">
            <input type="checkbox" name="remember" id="remember" class="custom-control-input" {{ old('remember') ? 'checked' : '' }}>
            <label class="custom-control-label" for="remember">Remember me</label>
        </div>
    </div>
    <button type="submit" class="btn-login">
        <i class="fas fa-sign-in-alt mr-2"></i>Sign In
    </button>
</form>
@endsection
