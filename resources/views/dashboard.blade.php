@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="card p-4">
        <h2 class="text-center mb-4">Dashboard</h2>
        @if(session('success'))
            <div class="alert alert-success text-center">
                {{ session('success') }}
            </div>
        @endif
        <p class="text-center">Welcome, {{ auth()->user()->name }}!</p>
        <div class="text-center">
            <a href="{{ route('sso.generate') }}" class="btn btn-secondary">Generate SSO Token</a>
            <a href="{{ route('sso.login') }}" class="btn btn-info">SSO Login</a>
            <a href="{{ route('2fa.setup') }}" class="btn btn-warning">
                {{ auth()->user()->two_factor_enabled_at ? 'Manage 2FA' : 'Setup 2FA' }}
            </a>
        </div>
    </div>
@endsection
