@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="h4">Notifikasi</h1>

        <ul class="list-group mt-3">
            @forelse($notifications as $note)
                <li class="list-group-item">
                    <div class="small text-muted">{{ $note->created_at }}</div>
                    <div>{{ $note->data['message'] ?? $note->type }}</div>
                </li>
            @empty
                <li class="list-group-item">Tidak ada notifikasi.</li>
            @endforelse
        </ul>

        <a href="{{ route('user.queue.index') }}" class="btn btn-secondary mt-3">Kembali</a>
    </div>
@endsection