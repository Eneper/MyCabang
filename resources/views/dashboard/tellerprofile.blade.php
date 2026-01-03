@extends('layout.app')

@section('content')
    <div class="container py-4">
        <div class="card mx-auto" style="max-width:720px;">
            <div class="card-body d-flex align-items-center gap-3">
                <img src="{{ $customer->photo ? asset('storage/' . $customer->photo) : 'https://via.placeholder.com/160' }}" alt="Photo" class="rounded img-fluid" style="width:128px;height:128px;object-fit:cover;margin-right:1rem;">
                <div>
                    <h2 class="h5">{{ $customer->name }}</h2>
                    <p class="small text-muted">{{ $customer->email }}</p>
                </div>
            </div>

            <div class="card-body border-top">
                <h6>Profil</h6>
                <p class="mb-3">{{ $customer->profile ?? '-' }}</p>

                <h6>Rekomendasi Produk & Alasan</h6>
                <div id="recommendations-list" class="list-group list-group-flush">
                    <!-- Populated by JavaScript -->
                    <div class="list-group-item text-muted">Memuat rekomendasi...</div>
                </div>

                <div class="mt-3">
                    <a href="{{ route('teller.dashboard') }}" class="small">Kembali</a>
                </div>
            </div>

            <script>
                async function loadRecommendations() {
                    const customerId = {{ $customer->id }};
                    try {
                        const res = await fetch('/teller/api/recommendation/' + customerId);
                        if (!res.ok) throw new Error('Failed to fetch');
                        const data = await res.json();
                        const prods = data.products || [];

                        const list = document.getElementById('recommendations-list');
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
                    } catch (e) {
                        console.error('Error loading recommendations:', e);
                        document.getElementById('recommendations-list').innerHTML = '<div class="list-group-item text-danger">Gagal memuat rekomendasi</div>';
                    }
                }

                // Load recommendations on page load
                loadRecommendations();
            </script>
        </div>
    </div>
@endsection
