@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success text-center mb-4">{{ session('success') }}</div>
    @endif

    {{-- Welcome card --}}
    <div class="card p-4 mb-4">
        <h2 class="mb-1">Welcome, {{ auth()->user()->name }}!</h2>
        <p class="text-muted mb-0">{{ auth()->user()->email }}</p>
    </div>

    {{-- Action buttons --}}
    <div class="card p-4 mb-4">
        <h5 class="mb-3">Actions</h5>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('sso.generate') }}" class="btn btn-secondary">Generate SSO Token</a>
            <a href="{{ route('sso.login') }}" class="btn btn-info text-white">SSO Login</a>
            <a href="{{ route('two-factor.setup') }}" class="btn btn-warning">
                {{ auth()->user()->two_factor_enabled_at ? 'Manage 2FA' : 'Setup 2FA' }}
            </a>
            <a href="{{ route('changelog') }}" class="btn btn-outline-primary">v0.1 → v0.2 Changelog</a>
        </div>
    </div>

    {{-- Device Recognition (v0.2.0) --}}
    <div class="card p-4 mb-4">
        <h5 class="mb-3">
            Current Device
            <span class="badge bg-success ms-1" style="font-size:.7rem;">v0.2.0</span>
        </h5>
        @php
            $deviceToken = request()->cookie('sentinel_device_token');
            $logs = \Harryes\SentinelLog\Models\AuthenticationLog::where('authenticatable_id', auth()->id())
                ->where('authenticatable_type', \App\Models\User::class)
                ->whereNotNull('device_info')
                ->latest('event_at')
                ->take(5)
                ->get();
            $latestLog = $logs->first();
        @endphp

        @if($deviceToken)
            <div class="mb-2">
                <small class="text-muted d-block mb-1">Device Token (HttpOnly cookie — visible only server-side)</small>
                <code style="word-break:break-all;font-size:.78rem;">{{ $deviceToken }}</code>
            </div>
        @endif

        @if($latestLog && ($info = $latestLog->device_info))
            <div class="row g-2 mt-1" style="font-size:.85rem;">
                @if(!empty($info['token']))
                    <div class="col-12">
                        <strong>Token from last log:</strong>
                        <code style="word-break:break-all;">{{ $info['token'] }}</code>
                    </div>
                @endif
                @if(!empty($info['platform']))
                    <div class="col-sm-4"><strong>Platform:</strong> {{ $info['platform'] }}</div>
                @endif
                @if(!empty($info['browser']))
                    <div class="col-sm-8">
                        <strong>Browser UA:</strong>
                        <span class="text-truncate d-inline-block" style="max-width:340px;" title="{{ $info['browser'] }}">
                            {{ $info['browser'] }}
                        </span>
                    </div>
                @endif
                @if(!empty($info['hash']))
                    <div class="col-12">
                        <strong>Header hash:</strong> <code>{{ $info['hash'] }}</code>
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- Recent Authentication Logs --}}
    <div class="card p-4 mb-4">
        <h5 class="mb-3">Recent Authentication Logs</h5>

        @if($logs->isEmpty())
            <p class="text-muted mb-0">No logs recorded yet.</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0" style="font-size:.83rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Event</th>
                            <th>IP</th>
                            <th>Location</th>
                            <th>Device token</th>
                            <th>When</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            @php
                                $loc  = $log->location  ?? [];
                                $dev  = $log->device_info ?? [];
                                $tok  = $dev['token'] ?? null;
                            @endphp
                            <tr>
                                <td>
                                    <span class="badge {{ $log->is_successful ? 'bg-success' : 'bg-danger' }}">
                                        {{ $log->event_name }}
                                    </span>
                                </td>
                                <td>{{ $log->ip_address ?? '—' }}</td>
                                <td>
                                    {{ $loc['city'] ?? '' }}
                                    @if(!empty($loc['country'])), {{ $loc['country'] }}@endif
                                </td>
                                <td>
                                    @if($tok)
                                        <code title="{{ $tok }}">{{ substr($tok, 0, 8) }}…</code>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $log->event_at?->diffForHumans() ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

@endsection
