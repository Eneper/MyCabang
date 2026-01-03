@extends('layout.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4 mb-0 text-bca">Security Dashboard</h1>
        </div>

        <div class="row g-3">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body p-0">
                        <img id="capture-img" src="https://via.placeholder.com/800x400?text=Live+Feed" alt="capture"
                            class="img-fluid w-100" style="object-fit:cover;">
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="mb-3">Informasi Terdeteksi</h6>
                                <div id="customer-info-empty" class="text-center text-muted py-4">
                                    <p class="small">Tidak ada customer terdeteksi</p>
                                </div>
                                <div id="customer-info-box" class="d-none">
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <img id="customer-photo" src="https://via.placeholder.com/150" alt="photo"
                                                class="img-fluid rounded" style="height: 120px; object-fit: cover;">
                                        </div>
                                        <div class="col-md-9">
                                            <div class="mb-2">
                                                <label class="form-label small text-muted mb-1">Nama</label>
                                                <div id="customer-name" class="fw-bold">-</div>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small text-muted mb-1">Kode Nasabah</label>
                                                <div id="customer-code">-</div>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small text-muted mb-1">Status Pencocokan</label>
                                                <div id="customer-status" class="badge bg-success">-</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <button id="btn-confirm-customer" class="btn btn-bca btn-sm">Konfirmasi & Buat
                                            Antrian</button>
                                        <button id="btn-reject-customer"
                                            class="btn btn-outline-secondary btn-sm">Batalkan</button>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small text-muted mb-1">Rekomendasi</label>
                                        <div id="customer-recommendations">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="h6">Detail Metadata</h5>
                        <div id="capture-detail" class="small text-muted" style="max-height: 200px; overflow-y: auto;">Tidak
                            ada capture</div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="h6">Recent Detections</h5>
                        <div id="detection-list" class="list-group list-group-flush small">
                            <div class="list-group-item text-muted">No detections yet.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentDetection = null;

        // Detections polling and UI
        async function loadDetections() {
            try {
                const res = await fetch('/security/api/faces');
                if (!res.ok) throw new Error('network');
                const data = await res.json();
                const list = document.getElementById('detection-list');
                list.innerHTML = '';
                if (!data.detections || !data.detections.length) {
                    list.innerHTML = '<div class="list-group-item text-muted">No detections yet.</div>';
                    return;
                }
                data.detections.forEach((d, idx) => {
                    const el = document.createElement('button');
                    el.className = 'list-group-item list-group-item-action';
                    if (idx === 0) el.classList.add('active'); // Highlight first item
                    el.type = 'button';
                    el.innerText = (d.name || 'Unknown') + ' — ' + new Date(d.created_at).toLocaleString();
                    el.addEventListener('click', () => showDetection(d));
                    list.appendChild(el);

                    // Auto-show first detection
                    if (idx === 0) {
                        showDetection(d);
                    }
                });
            } catch (e) {
                console.error(e);
            }
        }

        async function showDetection(d) {
            currentDetection = d;
            console.log('Showing detection:', d);

            const img = document.getElementById('capture-img');
            if (d.photo) {
                img.src = '/storage/' + d.photo;
            }

            let metadata = d.metadata || {};

            // Hide empty state and show customer info
            document.getElementById('customer-info-empty').classList.add('d-none');
            document.getElementById('customer-info-box').classList.remove('d-none');

            // If customer_id exists, fetch full customer data from API
            if (d.customer_id) {
                console.log('Fetching customer data for ID:', d.customer_id);
                try {
                    const res = await fetch('/security/api/customer/' + d.customer_id);
                    if (res.ok) {
                        const customerData = await res.json();
                        if (customerData.customer) {
                            const cust = customerData.customer;
                            console.log('Customer data retrieved:', cust);

                            // Update customer info display
                            document.getElementById('customer-name').innerText = cust.name;
                            document.getElementById('customer-code').innerText = cust.cust_code;
                            document.getElementById('customer-status').innerText = 'Cocok - Ditemukan di Database';
                            document.getElementById('customer-status').className = 'badge bg-success';

                            if (cust.photo) {
                                document.getElementById('customer-photo').src = '/storage/' + cust.photo;
                            } else {
                                document.getElementById('customer-photo').src = 'https://via.placeholder.com/150';
                            }

                            metadata = {
                                'Name': cust.name,
                                'Customer Code': cust.cust_code,
                                'Photo': cust.photo ? 'Yes' : 'No',
                                ...metadata
                            };

                            // show customer.rekomendasi if present (accepts array of objects or plain string)
                            const custRecEl = document.getElementById('customer-recommendations');
                            custRecEl.innerHTML = '';

                            const escapeHtml = (str) => String(str)
                                .replace(/&/g, '&amp;')
                                .replace(/</g, '&lt;')
                                .replace(/>/g, '&gt;')
                                .replace(/"/g, '&quot;')
                                .replace(/'/g, '&#039;');

                            const renderRecObject = (rec) => {
                                const title = rec.product_name ?? rec.title ?? rec.name ?? null;
                                const rank = rec.rank ?? '';
                                const confidence = (typeof rec.confidence === 'number') ? `Confidence: ${(rec.confidence * 100).toFixed(1)}%` : '';
                                return `
                                        <div class="rec-item">
                                            <div class="rec-title">${escapeHtml(rank ? (rank + '. ' + title) : (title ?? JSON.stringify(rec)))}</div>
                                            ${confidence ? `<div class="rec-confidence">${escapeHtml(confidence)}</div>` : ''}
                                        </div>
                                    `;
                            };

                            if (Array.isArray(cust.rekomendasi) && cust.rekomendasi.length) {
                                cust.rekomendasi
                                    .sort((a, b) => (a.rank ?? 0) - (b.rank ?? 0))
                                    .forEach(rec => {
                                        const wrapper = document.createElement('div');
                                        wrapper.innerHTML = renderRecObject(rec);
                                        custRecEl.appendChild(wrapper);
                                    });
                            } else if (typeof cust.rekomendasi === 'string' && cust.rekomendasi.trim()) {
                                custRecEl.innerText = cust.rekomendasi;
                            } else {
                                custRecEl.innerHTML = '<em>-</em>';
                            }
                        }
                    } else {
                        console.error('Failed to fetch, status:', res.status);
                    }
                } catch (e) {
                    console.error('Failed to fetch customer data:', e);
                    document.getElementById('customer-status').innerText = 'Error Loading';
                    document.getElementById('customer-status').className = 'badge bg-warning';
                }
            } else {
                // No customer match - show detection name
                console.log('No customer_id, showing detection name:', d.name);
                document.getElementById('customer-name').innerText = d.name || 'Unknown';
                document.getElementById('customer-code').innerText = '-';
                document.getElementById('customer-status').innerText = 'Tidak Cocok - Tidak Ada di Database';
                document.getElementById('customer-status').className = 'badge bg-danger';
                document.getElementById('customer-photo').src = 'https://via.placeholder.com/150';
                // show recommendations from detection metadata if present
                const recEl = document.getElementById('customer-recommendations');
                if (metadata.recommendations) {
                    const recs = metadata.recommendations;
                    const escapeHtml = (str) => String(str)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');

                    if (Array.isArray(recs) && recs.length) {
                        recEl.innerHTML = '<ul>' + recs.map(r => {
                            if (r && typeof r === 'object') {
                                const name = r.product_name ?? r.title ?? r.name ?? JSON.stringify(r);
                                return '<li>' + escapeHtml(name) + '</li>';
                            }
                            return '<li>' + escapeHtml(String(r)) + '</li>';
                        }).join('') + '</ul>';
                    } else {
                        recEl.innerText = String(recs);
                    }
                } else if (metadata.recommendation) {
                    recEl.innerText = String(metadata.recommendation);
                } else {
                    recEl.innerText = '-';
                }
            }

            document.getElementById('capture-detail').innerText = JSON.stringify(metadata, null, 2);
        }

        async function confirmDetection(id) {
            if (!currentDetection) {
                alert('Tidak ada detection yang dipilih');
                return;
            }

            const btn = document.getElementById('btn-confirm-customer');
            btn.disabled = true;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            console.log('Confirming detection ID:', id);
            console.log('Customer ID:', currentDetection.customer_id || 'None (will create new)');
            console.log('CSRF Token:', csrfToken ? 'Present' : 'Missing');

            try {
                // Prepare body with customer_id if exists
                const body = {};
                if (currentDetection.customer_id) {
                    body.customer_id = currentDetection.customer_id;
                }

                const res = await fetch('/security/api/faces/' + id + '/confirm', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(body)
                });

                console.log('Response status:', res.status);
                const responseText = await res.text();
                console.log('Response text:', responseText);

                if (!res.ok) {
                    alert('Gagal mengkonfirmasi (Status: ' + res.status + '). Cek console untuk detail.');
                    btn.disabled = false;
                    return;
                }

                const data = JSON.parse(responseText);
                console.log('Success response:', data);
                alert('✓ Berhasil! Customer ' + currentDetection.name + ' ditambahkan ke antrian.');
                loadDetections();
                document.getElementById('customer-info-empty').classList.remove('d-none');
                document.getElementById('customer-info-box').classList.add('d-none');
                currentDetection = null;
            } catch (e) {
                console.error('Error during confirm:', e);
                alert('Terjadi error: ' + e.message + '. Cek console untuk detail lengkap.');
                btn.disabled = false;
            }
        }

        function rejectDetection() {
            document.getElementById('customer-info-empty').classList.remove('d-none');
            document.getElementById('customer-info-box').classList.add('d-none');
            currentDetection = null;
        }

        // Event listeners
        document.getElementById('btn-confirm-customer').addEventListener('click', function () {
            if (currentDetection) {
                confirmDetection(currentDetection.id);
            }
        });

        document.getElementById('btn-reject-customer').addEventListener('click', rejectDetection);

        // initial load and poll
        loadDetections();
        setInterval(loadDetections, 3000);
    </script>
@endsection