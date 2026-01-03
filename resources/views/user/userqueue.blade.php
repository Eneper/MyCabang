@extends('layout.app')

@section('content')
    <div class="container py-4">
        <div class="mx-auto" style="max-width:720px;">
            <h1 class="h4 mb-3 text-bca">Antrian Anda</h1>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div id="not-in-queue" class="text-center text-muted py-5">
                        <p class="h6 mb-1">Anda tidak sedang dalam antrian</p>
                        <p class="small mb-0">Silakan tunggu sampai petugas keamanan memanggil Anda</p>
                    </div>

                    <div id="in-queue" class="d-none">
                        <div class="text-center mb-4">
                            <div class="small text-muted">Posisi Antrian Anda</div>
                            <div id="position" class="display-4 fw-bold text-bca">#-</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-6">
                                <div class="small text-uppercase text-muted">Status</div>
                                <div id="status" class="h5 fw-semibold mt-2">-</div>
                            </div>
                            <div class="col-6">
                                <div class="small text-uppercase text-muted">Total dalam antrian</div>
                                @extends('layouts.app')

                                @section('content')
                                    <div class="container py-5">
                                        <div class="mx-auto" style="max-width:900px;">
                                            <header class="mb-4">
                                                <h1 class="h3 mb-1">Antrian</h1>
                                                <p class="text-muted small">Pantau nomor antrian Anda secara real-time</p>
                                            </header>

                                            <div class="d-flex gap-3 flex-column flex-lg-row">
                                                <!-- Left: large card with current number -->
                                                <div class="flex-grow-1">
                                                    <div class="card shadow-sm h-100 border-0" style="overflow:visible;">
                                                        <div class="card-body position-relative">
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <div>
                                                                    <div class="small text-muted">Sedang dipanggil</div>
                                                                    <div id="current-number" class="display-5 fw-bold">-</div>
                                                                </div>
                                                                <div class="text-end">
                                                                    <div class="small text-muted">Estimasi</div>
                                                                    <div id="eta" class="h5 fw-semibold">-</div>
                                                                    <div id="reminder" class="small text-warning d-block mt-1">-</div>
                                                                </div>
                                                            </div>

                                                            <div class="my-3">
                                                                <div class="d-flex align-items-center gap-3">
                                                                    <div class="bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width:110px;height:110px;border:6px solid rgba(0,123,255,0.06);">
                                                                        <div id="my-number" class="fs-3 fw-bold text-primary">#-</div>
                                                                    </div>
                                                                    <div>
                                                                        <div class="small text-muted">Nomor Anda</div>
                                                                        <div id="position" class="fw-semibold">Posisi: -</div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="progress mt-3" style="height:10px;border-radius:8px;">
                                                                <div id="progress-bar" class="progress-bar bg-primary" role="progressbar" style="width:0%"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Right: queue list -->
                                                <aside style="min-width:320px; max-width:380px;">
                                                    <div class="card shadow-sm border-0 h-100">
                                                        <div class="card-body">
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <div>
                                                                    <div class="small text-muted">Daftar Antrian</div>
                                                                    <div class="fw-semibold">Sekitar 10 menit</div>
                                                                </div>
                                                                <div>
                                                                    <a href="{{ route('user.notifications') }}" class="btn btn-outline-secondary btn-sm">Notifikasi</a>
                                                                </div>
                                                            </div>

                                                            <ul id="queue-list" class="list-group list-group-flush">
                                                                <li class="list-group-item small text-muted">Tidak ada data antrian saat ini.</li>
                                                            </ul>

                                                            <div class="mt-3 text-end">
                                                                <button id="btn-simulate" class="btn btn-sm btn-primary">Simulasikan</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </aside>
                                            </div>
                                        </div>
                                    </div>

                                    <style>
                                        /* Clean, modern aesthetic — purely presentational */
                                        :root{ --accent:#007bff; --muted:#6c757d; }
                                        .card{ border-radius:12px; }
                                        #current-number{ color:var(--accent); }
                                        #my-number{ color:var(--accent); }
                                        .list-group-item.current{ background:linear-gradient(90deg, rgba(0,123,255,0.06), rgba(0,123,255,0.02)); font-weight:600; }
                                        .list-group-item.me{ background:linear-gradient(90deg, rgba(255,193,7,0.06), rgba(255,193,7,0.02)); }
                                        .list-group-item small{ color:var(--muted); }
                                        .fade-in{ animation:fadeIn .28s ease both; }
                                        @keyframes fadeIn{ from{ opacity:0; transform:translateY(6px);} to{ opacity:1; transform:none; } }
                                    </style>

                                    <script>
                                        // UI-only: keep existing IDs and route link intact.
                                        const state = {
                                            current: 10,
                                            myNumber: 14,
                                            queue: [10,11,12,13,14,15,16]
                                        };

                                        function updateProgress(pos){
                                            if (pos <= 0) return 100;
                                            const totalAhead = state.queue.length - 1;
                                            if (totalAhead <= 0) return 100;
                                            const pct = Math.max(5, Math.round(((totalAhead - pos) / totalAhead) * 100));
                                            return pct;
                                        }

                                        function render(){
                                            document.getElementById('current-number').innerText = state.current;
                                            document.getElementById('my-number').innerText = '#' + state.myNumber;
                                            const pos = state.queue.indexOf(state.myNumber);
                                            document.getElementById('position').innerText = pos === -1 ? 'Tidak terdaftar' : (pos === 0 ? 'Sedang dipanggil' : pos + ' orang sebelum Anda');
                                            document.getElementById('eta').innerText = pos <= 0 ? 'Segera' : (pos * 3) + ' menit';

                                            const reminderEl = document.getElementById('reminder');
                                            if (pos === 1) reminderEl.innerText = 'Reminder: 1 orang sebelum Anda.';
                                            else if (pos === 0) reminderEl.innerText = 'Sekarang giliran Anda!';
                                            else reminderEl.innerText = '-';

                                            // Queue list
                                            const list = document.getElementById('queue-list');
                                            list.innerHTML = '';
                                            if (!state.queue || state.queue.length === 0){
                                                const li = document.createElement('li');
                                                li.className = 'list-group-item small text-muted';
                                                li.innerText = 'Tidak ada data antrian saat ini.';
                                                list.appendChild(li);
                                            } else {
                                                state.queue.forEach((n, i) => {
                                                    const li = document.createElement('li');
                                                    li.className = 'list-group-item d-flex justify-content-between align-items-center fade-in';
                                                    li.style.transitionDelay = (i * 35) + 'ms';
                                                    const left = document.createElement('div');
                                                    left.innerHTML = '<strong>#' + n + '</strong><div class="small text-muted">' + (n === state.current ? 'Sedang dipanggil' : '') + '</div>';
                                                    const right = document.createElement('div');
                                                    if (n === state.current) {
                                                        right.innerHTML = '<span class="badge bg-info text-dark">Dipanggil</span>';
                                                        li.classList.add('current');
                                                    } else if (n === state.myNumber){
                                                        right.innerHTML = '<span class="badge bg-warning text-dark">Anda</span>';
                                                        li.classList.add('me');
                                                    }
                                                    li.appendChild(left);
                                                    li.appendChild(right);
                                                    list.appendChild(li);
                                                });
                                            }

                                            // progress
                                            const progress = updateProgress(pos);
                                            document.getElementById('progress-bar').style.width = progress + '%';
                                        }

                                        document.getElementById('btn-simulate').addEventListener('click', () => {
                                            if (state.queue.length) {
                                                state.queue.shift();
                                                state.current = state.queue[0] || '-';
                                            }
                                            render();
                                        });

                                        render();
                                    </script>
                                @endsection
