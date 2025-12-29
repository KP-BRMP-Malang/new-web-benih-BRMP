{{-- resources/views/admin/articles/create.blade.php --}}
@extends('layouts.admin')
@section('content')
<!-- Quill CSS -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

<div class="container" style="margin-top: 100px;">
    <h3>Tambah Artikel</h3>
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <form action="{{ route('admin.articles.store') }}" method="POST" enctype="multipart/form-data" id="articleForm">
        @csrf
        <div class="mb-3">
            <label>Headline</label>
            <input type="text" name="headline" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Gambar</label>
            <input type="file" name="image" class="form-control">
        </div>
        <div class="mb-3">
            <label>Isi Artikel</label>
            <div id="quill-editor" style="height: 300px;">{!! old('body') !!}</div>
            <input type="hidden" name="body" id="body">
        </div>
        <button class="btn btn-success" type="submit">Simpan</button>
        <a href="{{ route('admin.articles.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>

<!-- Quill JS -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    var quill = new Quill('#quill-editor', {
        theme: 'snow'
    });

    document.getElementById('articleForm').onsubmit = function() {
        document.getElementById('body').value = quill.root.innerHTML;
    };
</script>
@endsection