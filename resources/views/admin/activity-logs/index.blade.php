@extends('layouts.admin')
@section('title', 'Log Aktivitas')
@section('heading', 'Log Aktivitas Admin')

@section('content')
@if ($logs->isEmpty())
    <x-empty-state title="Belum ada aktivitas" text="Tindakan admin yang sensitif akan dicatat di sini." />
@else
<div class="table-wrap"><table class="table">
    <thead><tr><th scope="col">Waktu</th><th scope="col">Admin</th><th scope="col">Aksi</th><th scope="col">Keterangan</th><th scope="col">IP</th></tr></thead>
    <tbody>
    @foreach ($logs as $log)
        <tr>
            <td class="whitespace-nowrap text-xs">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
            <td>{{ $log->user?->name ?? '—' }}</td>
            <td class="font-mono text-xs">{{ $log->action }}</td>
            <td>{{ $log->description }}</td>
            <td class="font-mono text-xs">{{ $log->ip_address }}</td>
        </tr>
    @endforeach
    </tbody>
</table></div>
{{ $logs->links() }}
@endif
@endsection
