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
                                <div id="total-queue" class="h5 fw-semibold mt-2">-</div>
                            </div>
                        </div>

                        <div class="alert alert-info alert-sm" role="alert">
                            <small id="queue-message">Tunggu giliran Anda untuk dipanggil</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function checkQueueStatus() {
            try {
                const res = await fetch('/customer/api/queue-status');
                if (!res.ok) throw new Error('Failed to fetch');

                const data = await res.json();
                updateQueueUI(data);
            } catch (e) {
                console.error('Error checking queue status:', e);
            }
        }

        function updateQueueUI(data) {
            const notInQueueEl = document.getElementById('not-in-queue');
            const inQueueEl = document.getElementById('in-queue');

            if (data.in_queue) {
                notInQueueEl.classList.add('d-none');
                inQueueEl.classList.remove('d-none');

                document.getElementById('position').innerText = '#' + data.position;
                document.getElementById('total-queue').innerText = data.total_in_queue;

                if (data.currently_served) {
                    document.getElementById('status').innerText = '🔴 Sedang Dilayani';
                    document.getElementById('status').className = 'h5 fw-semibold mt-2 text-success';
                    document.getElementById('queue-message').innerText = 'Anda sedang dilayani sekarang!';
                } else {
                    document.getElementById('status').innerText = '⏳ Menunggu';
                    document.getElementById('status').className = 'h5 fw-semibold mt-2 text-warning';
                    document.getElementById('queue-message').innerText = 'Posisi #' + data.position + ' dari ' + data.total_in_queue + ' menunggu';
                }
            } else if (data.is_finished) {
                notInQueueEl.classList.add('d-none');
                inQueueEl.classList.remove('d-none');
                document.getElementById('status').innerText = '✓ Selesai';
                document.getElementById('status').className = 'h5 fw-semibold mt-2 text-success';
                document.getElementById('queue-message').innerText = 'Layanan Anda telah selesai. Terima kasih!';
            } else {
                notInQueueEl.classList.remove('d-none');
                inQueueEl.classList.add('d-none');
            }
        }
        // Initial load and poll every 2 seconds
        checkQueueStatus();
        setInterval(checkQueueStatus, 2000);
    </script>
@endsection
