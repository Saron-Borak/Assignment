@extends('layouts.kiosk')
@section('title', 'Check-in · '.$session->classSection->label())

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center gap-3 mb-4">
        <span class="brand-mark">{{ config('attendance.university_short_name') }}</span>
        <div class="me-auto">
            <h1 class="h4 mb-0">{{ $session->classSection->fullLabel() }}</h1>
            <div class="text-white-50 small">
                {{ $session->session_date->format('l, d F Y') }} · {{ $session->timeRange() }}
                @if ($session->classSection->room) · Room {{ $session->classSection->room }} @endif
            </div>
        </div>
        <a href="{{ route('lecturer.sessions.show', $session) }}" class="btn btn-outline-light btn-sm">
            <i class="bi bi-x-lg me-1"></i>Exit
        </a>
    </div>

    @if ($localhostWarning)
        <div class="alert alert-warning d-flex align-items-start gap-2">
            <i class="bi bi-exclamation-triangle-fill mt-1"></i>
            <div class="small">
                <strong>Phones cannot reach this address.</strong>
                <code>APP_URL</code> is set to a loopback address, so a scanned code resolves to the student's own
                phone. Set <code>APP_URL</code> to this machine's network address (for example
                <code>http://192.168.1.10:8000</code>) and serve with
                <code>php artisan serve --host=0.0.0.0</code>. The six-character code below works either way.
            </div>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="d-flex flex-column align-items-center">
                <div class="kiosk-qr mb-3" id="qrHolder">{!! $payload['qr_svg'] !!}</div>

                <div class="text-center">
                    <div class="text-white-50 text-uppercase small" style="letter-spacing:.12em">Or enter this code</div>
                    <div class="kiosk-code" id="checkinCode">{{ $payload['code'] }}</div>
                    <div class="text-white-50 small">
                        at <span class="font-monospace">{{ url('/student/check-in') }}</span>
                    </div>
                </div>

                <div class="mt-3 w-100" style="max-width:340px">
                    <div class="d-flex justify-content-between small text-white-50 mb-1">
                        <span>Code refreshes automatically</span>
                        <span><span id="countdown">{{ $payload['expires_in'] }}</span>s</span>
                    </div>
                    <div class="progress" style="height:5px; background:rgba(255,255,255,.15)">
                        <div class="progress-bar bg-warning" id="countdownBar" style="width:100%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="row g-3 mb-3">
                <div class="col-4 text-center">
                    <div class="display-6 fw-bold text-success" id="statPresent">{{ $payload['present'] }}</div>
                    <div class="text-white-50 small text-uppercase" style="letter-spacing:.08em">Present</div>
                </div>
                <div class="col-4 text-center">
                    <div class="display-6 fw-bold text-warning" id="statLate">{{ $payload['late'] }}</div>
                    <div class="text-white-50 small text-uppercase" style="letter-spacing:.08em">Late</div>
                </div>
                <div class="col-4 text-center">
                    <div class="display-6 fw-bold text-white-50">
                        <span id="statCheckedIn">{{ $payload['checked_in'] }}</span><span class="fs-5">/{{ $payload['total'] }}</span>
                    </div>
                    <div class="text-white-50 small text-uppercase" style="letter-spacing:.08em">Checked in</div>
                </div>
            </div>

            <div class="card bg-transparent border-light border-opacity-25">
                <div class="card-header bg-transparent text-white border-light border-opacity-25 py-2">
                    <i class="bi bi-people me-1"></i>Just checked in
                </div>
                <div class="list-group list-group-flush kiosk-checkin-list" id="recentList">
                    @forelse ($payload['recent'] as $entry)
                        <div class="list-group-item bg-transparent text-white border-light border-opacity-10 d-flex align-items-center gap-2 py-2">
                            <span class="flex-grow-1">{{ $entry['name'] }}</span>
                            <span class="badge text-bg-{{ $entry['status'] === 'late' ? 'warning' : 'success' }}">{{ $entry['label'] }}</span>
                            <span class="text-white-50 small font-monospace">{{ $entry['at'] }}</span>
                        </div>
                    @empty
                        <div class="list-group-item bg-transparent text-white-50 text-center py-4">
                            Waiting for the first student to check in...
                        </div>
                    @endforelse
                </div>
            </div>

            <form method="POST" action="{{ route('lecturer.sessions.close', $session) }}" class="mt-3"
                  onsubmit="return confirm('Close this session? Anyone who has not checked in will be marked absent.')">
                @csrf @method('PUT')
                <button class="btn btn-danger w-100"><i class="bi bi-lock me-1"></i>Close session and mark the rest absent</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const refreshEndpoint = @json(route('lecturer.sessions.qr.refresh', $session));
    const summaryUrl = @json(route('lecturer.sessions.show', $session));
    const refreshSeconds = {{ $refreshSeconds }};
    const ttlSeconds = {{ (int) config('attendance.qr_ttl_seconds') }};

    const qrHolder = document.getElementById('qrHolder');
    const codeEl = document.getElementById('checkinCode');
    const countdownEl = document.getElementById('countdown');
    const barEl = document.getElementById('countdownBar');
    const listEl = document.getElementById('recentList');

    let remaining = {{ $payload['expires_in'] }};

    function renderRecent(recent) {
        listEl.replaceChildren();

        if (recent.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'list-group-item bg-transparent text-white-50 text-center py-4';
            empty.textContent = 'Waiting for the first student to check in...';
            listEl.appendChild(empty);
            return;
        }

        recent.forEach(function (entry) {
            const row = document.createElement('div');
            row.className = 'list-group-item bg-transparent text-white border-light border-opacity-10 d-flex align-items-center gap-2 py-2';

            const name = document.createElement('span');
            name.className = 'flex-grow-1';
            // textContent, not innerHTML - a student's name is untrusted input.
            name.textContent = entry.name;

            const badge = document.createElement('span');
            badge.className = 'badge text-bg-' + (entry.status === 'late' ? 'warning' : 'success');
            badge.textContent = entry.label;

            const time = document.createElement('span');
            time.className = 'text-white-50 small font-monospace';
            time.textContent = entry.at;

            row.append(name, badge, time);
            listEl.appendChild(row);
        });
    }

    function paint(data) {
        // The SVG comes from our own QR renderer, so inlining it is safe.
        qrHolder.innerHTML = data.qr_svg;
        codeEl.textContent = data.code;

        document.getElementById('statPresent').textContent = data.present;
        document.getElementById('statLate').textContent = data.late;
        document.getElementById('statCheckedIn').textContent = data.checked_in;

        renderRecent(data.recent);
        remaining = data.expires_in;
    }

    async function refresh() {
        try {
            const response = await fetch(refreshEndpoint, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();

            // The register was closed from another tab or device.
            if (data.closed) {
                window.location = summaryUrl;
                return;
            }

            paint(data);
        } catch (error) {
            // A dropped connection must not blank the projected screen; the next
            // tick simply tries again.
        }
    }

    setInterval(refresh, refreshSeconds * 1000);

    setInterval(function () {
        remaining = Math.max(0, remaining - 1);
        countdownEl.textContent = remaining;
        barEl.style.width = Math.min(100, (remaining / ttlSeconds) * 100) + '%';
    }, 1000);
})();
</script>
@endpush
