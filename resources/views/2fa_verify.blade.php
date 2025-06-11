@extends('layouts.app')

@section('title', '2FA Verification')

@section('content')
    <div class="card p-4">
        <h2 class="text-center mb-4">Two-Factor Authentication</h2>
        <p class="text-center">Enter the code from your authenticator app.</p>
        <form method="POST" action="/2fa/verify">
            @csrf
            <div class="mb-3">
                <label for="code" class="form-label">2FA Code</label>
                <input type="text" name="code" id="code" class="form-control" placeholder="Enter 6-digit code" required>
                @error('code')
                <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Verify</button>
            </div>
        </form>
    </div>
@endsection
