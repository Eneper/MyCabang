@extends('layout.app')

@section('content')
    <div class="container py-4">
        <div class="mx-auto" style="max-width:720px;">
            <h1 class="h4 mb-3 text-bca">Antrian Anda</h1>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div id="not-in-queue" class="text-center text-muted py-5 d-none">
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
                                <div id="total" class="h5 fw-semibold mt-2">-</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="small text-muted">Sedang dipanggil</div>
                            <div id="current-number" class="display-5 fw-bold">-</div>
                        </div>

                        <div class="progress mt-3" style="height:10px;border-radius:8px;">
                            <div id="progress-bar" class="progress-bar bg-primary" role="progressbar" style="width:0%"></div>
                        </div>

                        <div class="mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <div class="small text-muted">Daftar Antrian</div>
                                </div>
                                <div>
                                    <a href="{{ route('user.notifications') }}" class="btn btn-outline-secondary btn-sm">Notifikasi</a>
                                </div>
                            </div>

                            <ul id="queue-list" class="list-group list-group-flush">
                                <li class="list-group-item small text-muted">Memuat...</li>
                            </ul>
                        </div>
                    </div>

                    <div id="loading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Poll the same URL (this page) for JSON; the controller returns JSON when Accept header requests it.
        const POLL_INTERVAL = 3000;
        const myUserId = {{ auth()->id() ?? 'null' }};

        function escapeHtml(str){ return String(str)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;').replace(/'/g,'&#039;'); }

        async function loadQueue(){
            try{
                const res = await fetch(window.location.pathname, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                if (!res.ok) throw new Error('Network');
                const payload = await res.json();
                const queues = payload.data || [];

                // find my queue entry
                const myIndex = queues.findIndex(q => Number(q.user_id) === Number(myUserId));
                const myQueue = myIndex === -1 ? null : queues[myIndex];

                document.getElementById('loading').classList.add('d-none');
                if (!myQueue){
                    document.getElementById('not-in-queue').classList.remove('d-none');
                    document.getElementById('in-queue').classList.add('d-none');
                    return;
                }

                // show in-queue
                document.getElementById('not-in-queue').classList.add('d-none');
                document.getElementById('in-queue').classList.remove('d-none');

                const positionText = myIndex === 0 ? 'Sedang dipanggil' : (myIndex + ' orang sebelum Anda');
                document.getElementById('position').innerText = positionText;
                document.getElementById('status').innerText = myQueue.status ?? '-';
                document.getElementById('current-number').innerText = queues[0] ? ('#' + (queues[0].number ?? queues[0].id)) : '-';
                document.getElementById('total').innerText = queues.length;

                // render list
                const list = document.getElementById('queue-list');
                list.innerHTML = '';
                if (!queues.length){
                    const li = document.createElement('li'); li.className = 'list-group-item small text-muted'; li.innerText = 'Tidak ada data antrian saat ini.'; list.appendChild(li);
                } else {
                    queues.forEach((q, i) => {
                        const li = document.createElement('li');
                        li.className = 'list-group-item d-flex justify-content-between align-items-center';
                        const left = document.createElement('div');
                        left.innerHTML = '<strong>#' + escapeHtml(q.number ?? q.id) + '</strong><div class="small text-muted">' + (i === 0 ? 'Sedang dipanggil' : '') + '</div>';
                        const right = document.createElement('div');
                        if (i === 0) right.innerHTML = '<span class="badge bg-info text-dark">Dipanggil</span>';
                        else if (Number(q.user_id) === Number(myUserId)) right.innerHTML = '<span class="badge bg-warning text-dark">Anda</span>';
                        li.appendChild(left); li.appendChild(right); list.appendChild(li);
                    });
                }

                // progress bar simple estimate
                const pct = Math.max(5, Math.round(((queues.length - 1 - myIndex) / Math.max(1, queues.length - 1)) * 100));
                document.getElementById('progress-bar').style.width = (myIndex <= 0 ? 100 : pct) + '%';

            }catch(e){
                console.error('Failed to load queue', e);
                document.getElementById('loading').classList.add('d-none');
                document.getElementById('not-in-queue').classList.remove('d-none');
                document.getElementById('in-queue').classList.add('d-none');
            }
        }

        loadQueue();
        setInterval(loadQueue, POLL_INTERVAL);
    </script>
@endsection
                                       