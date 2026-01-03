@extends('layout.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h1 class="h4 mb-0">Teller Dashboard</h1>
            <div class="text-muted small">Status koneksi: <span id="conn-status" class="text-success">✓</span></div>
        </div>

        <div class="d-flex justify-content-center">
            <div class="card shadow-sm w-100" style="max-width:1200px;">
                <div class="card-body d-flex gap-4" style="min-height: 600px;">
                    <!-- Nasabah yang sedang dilayani (Kiri) -->
                    <div style="flex: 1; overflow-y: auto;">
                        <h2 class="h5 mb-0 text-bca">Nasabah yang sedang dilayani</h2>

                        <div id="detail-empty" class="text-center text-muted py-5 border rounded mt-3">
                            <p class="h6 mb-1">Belum ada nasabah yang sedang dilayani</p>
                            <p class="small mb-0">Tunggu sampai pelanggan dipanggil.</p>
                        </div>

                        <div id="detail-box" class="d-none">
                            <div class="card-body d-flex align-items-center gap-3">
                                <img id="detail-photo" src="https://via.placeholder.com/160" alt="photo" class="rounded img-fluid" style="width:128px;height:128px;object-fit:cover;margin-right:1rem;">
                                <div>
                                    <h3 id="detail-name" class="h5 mb-0">Nama Nasabah</h3>
                                    <p id="detail-email" class="small text-muted mb-0">email@example.com</p>
                                </div>
                            </div>

                            <div class="card-body border-top">
                                <h6>Profil</h6>
                                <p id="detail-profile" class="mb-3" style="text-align: justify;">-</p>

                                <h6>Rekomendasi Produk & Alasan</h6>
                                <div id="suggestions" class="list-group list-group-flush">
                                    <!-- Populated by JS -->
                                </div>

                                <div class="mt-3 d-flex gap-2">
                                    <button id="btn-next" class="btn btn-bca">Lanjutkan ke next</button>
                                    <button id="btn-recall" class="btn btn-outline-secondary">Panggil ulang</button>
                                    <button id="btn-finish" class="btn btn-success">Selesai</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Antrian Menunggu (Kanan) -->
                    <div style="flex: 1; overflow-y: auto; border-left: 1px solid #ddd; padding-left: 1rem;">
                        <h6 class="mb-3">Antrian Menunggu</h6>
                        <div id="queue-list" class="list-group">
                            <!-- populated by JS -->
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

        async function fetchRecommendationFor(id) {
            try {
                const res = await fetch('/teller/api/recommendation/' + id);
                if (!res.ok) return [];
                const data = await res.json();
                return data.products || [];
            } catch (e) {
                return [];
            }
        }

        async function fetchCustomer(id) {
            try {
                const res = await fetch('/teller/api/customer/' + id);
                if (!res.ok) return null;
                return await res.json();
            } catch (e) { return null; }
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
            document.getElementById('detail-photo').src = c.photo ? '/' + c.photo : 'https://via.placeholder.com/160';
            box.dataset.current = c.id;
        }

        async function updateSuggestions(prods) {
            const list = document.getElementById('suggestions');
            list.innerHTML = '';

            if (prods.length) {
                prods.forEach((p, idx) => {
                    const item = document.createElement('div');
                    item.className = 'list-group-item';
                    const hasExplanation = p.explanation && p.explanation.trim();

                    if (hasExplanation) {
                        item.innerHTML = `
                            <div class="d-flex align-items-start justify-content-between gap-2">
                                <div class="flex-fill">
                                    <div class="fw-bold">${p.title}</div>
                                    <div class="small text-muted mb-2">${p.reason}</div>
                                    <div class="small text-secondary" id="exp-${idx}" style="display:none;">
                                        <em>${p.explanation}</em>
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-outline-info toggle-exp" data-idx="${idx}">Detail</button>
                            </div>
                        `;
                    } else {
                        item.innerHTML = `
                            <div>
                                <div class="fw-bold">${p.title}</div>
                                <div class="small text-muted">${p.reason}</div>
                            </div>
                        `;
                    }

                    list.appendChild(item);
                });

                // Attach toggle handlers
                document.querySelectorAll('.toggle-exp').forEach(btn => {
                    btn.onclick = function(e) {
                        e.preventDefault();
                        const idx = this.dataset.idx;
                        const expEl = document.getElementById('exp-' + idx);
                        const isHidden = expEl.style.display === 'none';
                        expEl.style.display = isHidden ? 'block' : 'none';
                        this.innerText = isHidden ? 'Tutup' : 'Detail';
                    };
                });
            } else {
                list.innerHTML = '<div class="list-group-item text-muted">Tidak ada rekomendasi</div>';
            }
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
                // update recommendations
                const prods = await fetchRecommendationFor(current.id);
                await updateSuggestions(prods);
            } else {
                showEmptyDetail();
            }
        }

        // light poll: update queue list only, tidak fetch rekomendasi
        async function updateQueueOnly() {
            try {
                const data = await fetchQueue();
                populateQueueList(data.customers || [], data.current);
            } catch (e) {
                console.error('updateQueueOnly error', e);
            }
        }

        function populateQueueList(customers, currentId) {
            const list = document.getElementById('queue-list');
            list.innerHTML = '';
            customers.forEach(c => {
                const item = document.createElement('div');
                item.className = 'list-group-item d-flex align-items-center';
                item.innerHTML = `
                    <div class="flex-fill">
                        <div class="fw-bold">${c.name}</div>
                        <div class="small text-muted">ID: ${c.id} — ${c.email || ''}</div>
                    </div>
                `;

                if (c.id == currentId) {
                    item.classList.add('active');
                }

                list.appendChild(item);
            });
        }

        document.getElementById('btn-next').addEventListener('click', function () {
            nextCustomer();
        });

        document.getElementById('btn-recall').addEventListener('click', function () {
            recallCustomer();
        });

        document.getElementById('btn-finish').addEventListener('click', async function () {
            const id = document.getElementById('detail-box').dataset.current;
            if (!id) return;
            const btn = this;
            btn.disabled = true;
            await fetch('/teller/api/finish', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ customer_id: id })
            });
            await refresh();
            btn.disabled = false;
        });

        // initial load and poll queue only every 3s (tidak fetch rekomendasi)
        refresh();
        setInterval(updateQueueOnly, 3000);
    </script>
@endsection
