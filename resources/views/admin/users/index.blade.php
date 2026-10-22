@extends('layouts.admin')
@section('title', 'Pengguna')
@section('heading', 'Pengguna')

@section('content')
<form method="GET" class="mb-4 flex flex-wrap gap-2" role="search">
    <div><label for="q" class="sr-only">Cari pengguna</label><input id="q" name="q" value="{{ $search }}" class="input" placeholder="Nama atau email…"></div>
    <div><label for="role" class="sr-only">Filter role</label><select id="role" name="role" class="input"><option value="">Semua role</option>@foreach (\App\Enums\Role::cases() as $r)<option value="{{ $r->value }}" @selected($role === $r->value)>{{ $r->label() }}</option>@endforeach</select></div>
    <button class="btn-secondary" type="submit">Filter</button>
</form>
<div class="table-wrap"><table class="table">
    <thead><tr><th scope="col">Nama</th><th scope="col">Email</th><th scope="col">Pesanan</th><th scope="col">Terdaftar</th><th scope="col">Role</th></tr></thead>
    <tbody>
    @foreach ($users as $u)
        <tr>
            <td class="font-medium text-white">{{ $u->name }}</td>
            <td>{{ $u->email }}</td>
            <td>{{ $u->orders_count }}</td>
            <td>{{ $u->created_at->format('d/m/Y') }}</td>
            <td>
                @if ($u->id === auth()->id())
                    <x-badge color="purple">{{ $u->role->label() }} (Anda)</x-badge>
                @else
                    <form method="POST" action="{{ route('admin.users.update', $u) }}" class="flex items-center gap-2" data-confirm="Ubah role pengguna ini?">
                        @csrf @method('PATCH')
                        <label for="role-{{ $u->id }}" class="sr-only">Role {{ $u->name }}</label>
                        <select id="role-{{ $u->id }}" name="role" class="input w-auto py-1.5">@foreach (\App\Enums\Role::cases() as $r)<option value="{{ $r->value }}" @selected($u->role === $r)>{{ $r->label() }}</option>@endforeach</select>
                        <button class="btn-secondary btn-sm" type="submit">Simpan</button>
                    </form>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table></div>
{{ $users->links() }}
@endsection
