@extends('layouts.app')

@section('title', 'SSO Login')

@section('content')
    <div class="card p-4">
        <h2 class="text-center mb-4">SSO Login</h2>
        @if(session('success'))
            <div class="alert alert-success text-center">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->has('sso'))
            <div class="alert alert-danger text-center">
                {{ $errors->first('sso') }}
            </div>
        @endif
        <form method="POST" action="/sso/login" class="text-center">
            @csrf
            <div class="mb-3">
                <label for="sso_token" class="form-label">SSO Token</label>
                <input type="text" name="sso_token" id="sso_token" class="form-control" placeholder="Enter your SSO token" value="{{ request('sso_token') }}" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Login with SSO</button>
            </div>
        </form>
    </div>
@endsection
