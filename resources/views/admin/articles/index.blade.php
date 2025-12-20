{{-- resources/views/admin/articles/index.blade.php --}}
@extends('layouts.admin')
@section('content')
<div class="container mt-4">
    <h3>Kelola Artikel</h3>
    <a href="{{ route('admin.articles.create') }}" class="btn btn-primary mb-3">Tambah Artikel</a>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Headline</th>
                <th>Gambar</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($articles as $a)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $a->headline }}</td>
                <td>
                    @if($a->image)
                        <img src="{{ asset('storage/articles/'.$a->image) }}" width="60">
                    @endif
                </td>
                <td>{{ $a->created_at->format('d-m-Y') }}</td>
                <td>
                    <a href="{{ route('admin.articles.edit', $a) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('admin.articles.destroy', $a) }}" method="POST" style="display:inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm" onclick="return confirm('Hapus artikel?')">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5">Belum ada artikel</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection