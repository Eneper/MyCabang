@extends('layout.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h1 class="h4 mb-0">Teller Dashboard</h1>
            <div class="text-muted small">Status koneksi: <span id="conn-status" class="text-success">✓</span></div>
        </div>

        <div class="d-flex justify-content-center">
            <div class="card shadow-sm w-100" style="max-width:760px;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h2 class="h5 mb-0 text-bca">Nasabah yang sedang dilayani</h2>
                        <div class="text-muted small">Auto-refresh setiap 3 detik</div>
                    </div>

                    <div id="detail-empty" class="text-center text-muted py-5 border rounded">
                        <p class="h6 mb-1">Belum ada nasabah yang sedang dilayani</p>
                        <p class="small mb-0">Tunggu sampai pelanggan dipanggil.</p>
                    </div>

                    <div id="detail-box" class="d-none">
                        <div class="d-flex align-items-start gap-4">
                            <div class="me-3">
                                <img id="detail-photo" src="https://via.placeholder.com/160" alt="photo" class="img-fluid rounded" style="width:112px;height:112px;object-fit:cover;">
                            </div>

                            <div class="flex-fill">
                                <div class="d-flex align-items-center">
                                    <h3 id="detail-name" class="h5 mb-0">Nama Nasabah</h3>
                                    <span id="priority-badge" class="badge bg-warning text-dark ms-2">Prioritas</span>
                                </div>

                                <p id="detail-email" class="text-muted small mb-2">email@example.com</p>

                                <div class="row g-3 text-sm text-muted">
                                    <div class="col-6">
                                        <div class="small text-uppercase text-muted">Profil</div>
                                        <div id="detail-profile" class="mt-1">-</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="small text-uppercase text-muted">Rekomendasi</div>
                                        <div id="detail-rekomendasi" class="mt-1">-</div>
                                    </div>
                                </div>

                                <div class="mt-3 d-flex align-items-center gap-2">
                                    <button id="btn-add-note" class="btn btn-outline-secondary btn-sm">Tambah Catatan</button>
                                    <a id="detail-view-link" href="#" class="ms-auto small text-decoration-none">Lihat Halaman</a>
                                </div>

                                <div class="mt-3 d-flex gap-2">
                                    <button id="btn-next" class="btn btn-bca">Lanjutkan ke next</button>
                                    <button id="btn-recall" class="btn btn-outline-secondary">Panggil ulang</button>
                                </div>

                                <hr class="my-3">

                                <div>
                                    <h6 class="mb-2">Saran Rekomendasi</h6>
                                    <div id="suggestions" class="list-group list-group-flush">
                                        <div class="list-group-item">Tabungan X — cocok berdasarkan histori</div>
                                        <div class="list-group-item">Deposito Y — promosi 3 bulan</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        async function fetchQueue() {
            try {
                const res = await fetch('/teller/api/queue');
                document.getElementById('conn-status').innerText = res.ok ? '✓' : '✕';
                document.getElementById('conn-status').className = res.ok ? 'text-success' : 'text-danger';
                if (!res.ok) throw new Error('network error');
                return await res.json();
            } catch (e) {
                console.error(e);
                document.getElementById('conn-status').innerText = '✕';
                document.getElementById('conn-status').className = 'text-danger';
                return { customers: [], current: null };
            }
        }

        function showEmptyDetail() {
            document.getElementById('detail-empty').classList.remove('d-none');
            document.getElementById('detail-box').classList.add('d-none');
        }

        function showDetail(c) {
            document.getElementById('detail-empty').classList.add('d-none');
            const box = document.getElementById('detail-box');
            box.classList.remove('d-none');

            document.getElementById('detail-name').innerText = c.name;
            document.getElementById('detail-email').innerText = c.email || '-';
            document.getElementById('detail-profile').innerText = c.profile || '-';
            document.getElementById('detail-rekomendasi').innerText = c.rekomendasi || '-';
            document.getElementById('detail-photo').src = c.photo ? '/storage/' + c.photo : 'https://via.placeholder.com/160';
            document.getElementById('detail-view-link').href = '/teller/customers/' + c.id;
            box.dataset.current = c.id;

            // priority badge (all customers are priority)
            document.getElementById('priority-badge').classList.remove('d-none');
        }

        async function nextCustomer() {
            const btn = document.getElementById('btn-next');
            btn.disabled = true;
            await fetch('/teller/api/serve/next', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf } });
            await refresh();
            btn.disabled = false;
        }

        async function recallCustomer() {
            const id = document.getElementById('detail-box').dataset.current;
            if (!id) return;
            await fetch('/teller/api/serve', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ customer_id: id })
            });
            // small feedback
            const btn = document.getElementById('btn-recall');
            btn.innerText = 'Dipanggil';
            setTimeout(() => btn.innerText = 'Panggil ulang', 1500);
        }

        async function refresh() {
            const data = await fetchQueue();
            if (!data.customers || !data.customers.length || !data.current) {
                showEmptyDetail();
                return;
            }

            // show only the current customer
            const current = data.customers.find(x => x.id == data.current);
            if (current) {
                showDetail(current);
            } else {
                showEmptyDetail();
            }
        }

        document.getElementById('btn-next').addEventListener('click', function () {
            nextCustomer();
        });

        document.getElementById('btn-recall').addEventListener('click', function () {
            recallCustomer();
        });

        // initial load and poll every 3s
        refresh();
        setInterval(refresh, 3000);
    </script>
@endsection
