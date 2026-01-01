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
                        <img id="capture-img" src="https://via.placeholder.com/800x400?text=Live+Feed" alt="capture" class="img-fluid w-100" style="object-fit:cover;">
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-sm-4">
                        <div class="card p-2 h-100">
                            <small class="text-muted">Detected</small>
                            <div id="detected-name" class="fw-medium">-</div>
                            <div id="detected-priority" class="text-warning small">-</div>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="card p-2 h-100">
                            <small class="text-muted">IoT Notification</small>
                            <div id="iot-status" class="fw-medium text-success">No notifications</div>
                            <div class="small text-muted">Last sent: <span id="iot-last">-</span></div>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="card p-2 h-100">
                            <small class="text-muted">Actions</small>
                            <div class="mt-2">
                                <button class="btn btn-bca btn-sm" id="btn-alert">Kirim Notifikasi IoT</button>
                                <button class="btn btn-outline-bca btn-sm" id="btn-escort">Antar ke Ruang Prioritas</button>
                                <button class="btn btn-outline-secondary btn-sm mt-2" id="btn-refresh-detections">Refresh Detections</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="h6">Detail Capture</h5>
                        <div id="capture-detail" class="small text-muted">Tidak ada capture</div>
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
        document.getElementById('btn-alert').addEventListener('click', function () {
            document.getElementById('iot-status').innerText = 'Notification sent';
            document.getElementById('iot-last').innerText = new Date().toLocaleTimeString();
        });

        document.getElementById('btn-escort').addEventListener('click', function () {
            alert('Mark as escorted (this action should be implemented in backend)');
        });

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
                data.detections.forEach(d => {
                    const el = document.createElement('button');
                    el.className = 'list-group-item list-group-item-action';
                    el.type = 'button';
                    el.innerText = (d.name || 'Unknown') + ' — ' + new Date(d.created_at).toLocaleString();
                    el.addEventListener('click', () => showDetection(d));
                    list.appendChild(el);
                });
            } catch (e) {
                console.error(e);
            }
        }

        function showDetection(d) {
            document.getElementById('detected-name').innerText = d.name || '-';
            // show detection id or linked customer id
            document.getElementById('detected-priority').innerText = d.customer_id ? ('Customer ID: ' + d.customer_id) : ('Detection ID: ' + d.id);
            const img = document.getElementById('capture-img');
            if (d.photo) {
                img.src = '/storage/' + d.photo;
            }
            document.getElementById('capture-detail').innerText = JSON.stringify(d.metadata || {}, null, 2);

            // add confirm button
            let confirmBtn = document.getElementById('btn-confirm-detection');
            if (!confirmBtn) {
                confirmBtn = document.createElement('button');
                confirmBtn.id = 'btn-confirm-detection';
                confirmBtn.className = 'btn btn-bca btn-sm mt-2';
                confirmBtn.innerText = 'Konfirmasi & Buat Antrian';
                confirmBtn.addEventListener('click', () => confirmDetection(d.id));
                document.querySelector('.card.mt-3 .card-body').appendChild(confirmBtn);
            } else {
                // update click handler
                confirmBtn.onclick = () => confirmDetection(d.id);
            }
        }

        async function confirmDetection(id) {
            const res = await fetch('/security/api/faces/' + id + '/confirm', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') } });
            if (!res.ok) {
                alert('Gagal mengkonfirmasi');
                return;
            }
            const data = await res.json();
            alert('Berhasil dikonfirmasi. Customer ditambahkan ke antrian.');
            loadDetections();
        }

        document.getElementById('btn-refresh-detections').addEventListener('click', loadDetections);

        // initial load and poll
        loadDetections();
        setInterval(loadDetections, 3000);
    </script>
@endsection
