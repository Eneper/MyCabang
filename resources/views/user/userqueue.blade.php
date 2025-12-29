@extends('layout.app')

@section('content')
    <div class="container py-4">
        <div class="mx-auto" style="max-width:720px;">
            <h1 class="h4 mb-3 text-bca">Antrian Anda</h1>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="small text-muted">Sedang dipanggil:</div>
                        <div id="current-number" class="display-4 fw-bold">-</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <div class="small text-muted">Nomor Anda</div>
                            <div id="my-number" class="h4 fw-semibold">#-</div>
                            <div id="position" class="small text-muted">Posisi: -</div>
                        </div>
                        <div class="col-6">
                            <div class="small text-muted">Perkiraan menunggu</div>
                            <div id="eta" class="h4 fw-semibold">-</div>
                            <div id="reminder" class="small text-warning">-</div>
                        </div>
                    </div>

                    <hr>

                    <div class="small text-muted">Riwayat antrian</div>
                    <ul id="queue-list" class="mt-2 small text-muted list-unstyled">
                        <li>Tidak ada data antrian saat ini.</li>
                    </ul>

                    <div class="mt-4 text-center">
                        <button id="btn-simulate" class="btn btn-bca btn-sm">Simulasi Antrian</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Simple simulation to demonstrate reminders/notifications UI
        const state = {
            current: 10,
            myNumber: 14,
            queue: [10,11,12,13,14,15,16]
        };

        function render() {
            document.getElementById('current-number').innerText = state.current;
            document.getElementById('my-number').innerText = '#' + state.myNumber;
            const pos = state.queue.indexOf(state.myNumber);
            document.getElementById('position').innerText = pos === -1 ? 'Tidak terdaftar' : (pos === 0 ? 'Sedang dipanggil' : pos + ' orang sebelum Anda');
            document.getElementById('eta').innerText = pos <= 0 ? 'Segera' : (pos * 3) + ' menit (perkiraan)';

            // Reminder when 1 before
            const reminderEl = document.getElementById('reminder');
            if (pos === 1) {
                reminderEl.innerText = 'Reminder: 1 orang sebelum Anda — persiapkan diri.';
            } else if (pos === 0) {
                reminderEl.innerText = 'Sekarang giliran Anda! Check-in di loket.';
            } else {
                reminderEl.innerText = '-';
            }

            // Render list
            const list = document.getElementById('queue-list');
            list.innerHTML = '';
            state.queue.forEach(n => {
                const li = document.createElement('li');
                li.innerText = n === state.current ? n + ' (dipanggil)' : (n === state.myNumber ? n + ' (Anda)' : n);
                list.appendChild(li);
            });
        }

        document.getElementById('btn-simulate').addEventListener('click', function () {
            // advance queue
            state.queue.shift();
            state.current = state.queue[0] || '-';
            render();
        });

        render();
    </script>
@endsection
