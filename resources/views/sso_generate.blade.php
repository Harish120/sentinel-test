@extends('layouts.app')

@section('title', 'Generate SSO Token')

@section('content')
    <div class="card p-4">
        <h2 class="text-center mb-4">Generate SSO Token</h2>
        <p class="text-center">Use this token to log in via SSO.</p>
        @if($token ?? false)
            <div class="alert alert-success text-center">
                <strong>Your SSO Token:</strong><br>
                <code>{{ $token }}</code>
                <p class="mt-2">Use it at: <a href="/sso/login?sso_token={{ $token }}">SSO Login</a></p>
            </div>
        @endif
        <form method="POST" action="/sso/generate" class="text-center">
            @csrf
            <button type="submit" class="btn btn-primary">Generate New Token</button>
        </form>
    </div>
@endsection
