@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="h4">Detail Antrian</h1>

        <div class="card mt-3">
            <div class="card-body">
                <p><strong>Nomor:</strong> {{ $queue->number ?? '-' }}</p>
                <p><strong>Status:</strong> {{ $queue->status ?? '-' }}</p>
                <p><strong>Dibuat:</strong> {{ $queue->created_at ?? '-' }}</p>
                <p><strong>Keterangan:</strong> {{ $queue->note ?? '-' }}</p>
            </div>
        </div>

        <a href="{{ route('user.queue.index') }}" class="btn btn-secondary mt-3">Kembali</a>
    </div>
@endsection