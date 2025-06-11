@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <div class="card p-4 text-center">
        <h1>Welcome to Sentinel Test</h1>
        <p>A Laravel project to test the SentinelLog package.</p>
        @auth
            <a href="/dashboard" class="btn btn-primary">Go to Dashboard</a>
        @else
            <a href="/login" class="btn btn-primary">Login</a>
        @endauth
    </div>
@endsection
