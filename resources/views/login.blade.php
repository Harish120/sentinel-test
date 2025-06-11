@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <div class="card p-4">
        <h2 class="text-center mb-4">Login</h2>
        @if($errors->has('email') || $errors->has('geo'))
            <div class="alert alert-danger text-center">
                {{ $errors->first('email') ?: $errors->first('geo') }}
                @if(app('sentinel.brute_force')->getAttempts(request()->ip()) > 0)
                    <br>Attempts remaining: {{ config('sentinel-log.brute_force.threshold', 5) - app('sentinel.brute_force')->getAttempts(request()->ip()) }}
                @endif
            </div>
        @endif
        <form method="POST" action="/login">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" value="{{ old('email') }}" required>
                @error('email')
                <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
        </form>
    </div>
@endsection
