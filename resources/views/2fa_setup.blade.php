@extends('layouts.app')

@section('title', 'Setup 2FA')

@section('content')
    <div class="card p-4">
        <h2 class="text-center mb-4">Two-Factor Authentication</h2>

        <!-- Display 2FA Status -->
        @if(auth()->user()->two_factor_enabled_at)
            <div class="alert alert-success text-center">
                <strong>2FA is Enabled</strong><br>
                Enabled on: {{ auth()->user()->two_factor_enabled_at->format('Y-m-d H:i:s') }}
            </div>
            <form method="POST" action="/2fa/disable" class="text-center">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Disable 2FA</button>
            </form>
        @else
            @if($qrCodeUrl ?? false)
                <div class="alert alert-info text-center">
                    <p>Scan this QR code with your authenticator app (e.g., Google Authenticator):</p>
                    <img src="https://api.qrserver.com/v1/create-qr-code/?data={{ urlencode($qrCodeUrl) }}&size=200x200" alt="2FA QR Code" class="mx-auto d-block mb-3">
                    <p>Or enter this secret manually: <code>{{ $secret }}</code></p>
                </div>
                <form method="POST" action="/2fa/setup" class="text-center">
                    @csrf
                    <input type="hidden" name="secret" value="{{ $secret }}">
                    <div class="mb-3">
                        <label for="code" class="form-label">Verify Code</label>
                        <input type="text" name="code" id="code" class="form-control" placeholder="Enter 6-digit code" required>
                        @error('code')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Enable 2FA</button>
                </form>
            @else
                <div class="alert alert-warning text-center">
                    <strong>2FA is Not Enabled</strong><br>
                    Set up 2FA to add an extra layer of security.
                </div>
                <form method="POST" action="/2fa/setup" class="text-center">
                    @csrf
                    <button type="submit" class="btn btn-primary">Generate 2FA QR Code</button>
                </form>
            @endif
        @endif
    </div>
@endsection
