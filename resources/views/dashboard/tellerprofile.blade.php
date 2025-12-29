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

                <h6>Rekomendasi Produk</h6>
                <p>{{ $customer->rekomendasi ?? '-' }}</p>

                <div class="mt-3">
                    <a href="{{ route('teller.dashboard') }}" class="small">Kembali</a>
                </div>
            </div>
        </div>
    </div>
@endsection
