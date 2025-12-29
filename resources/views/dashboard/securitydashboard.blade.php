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
                        <div id="detection-list" class="small text-muted">No detections yet.</div>
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
    </script>
@endsection
