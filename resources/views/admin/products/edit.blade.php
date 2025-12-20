@extends('layouts.admin')

@section('content')
    <div style="padding-top: 80px;">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Edit Produk</h2>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Form Edit Produk</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.products.update', $product->product_id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="mb-3">Informasi Dasar</h6>

                                        <div class="mb-3">
                                            <label for="product_name" class="form-label">Nama Produk *</label>
                                            <input type="text"
                                                class="form-control @error('product_name') is-invalid @enderror"
                                                id="product_name" name="product_name"
                                                value="{{ old('product_name', $product->product_name) }}" required>
                                            @error('product_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="plant_type_id" class="form-label">Kategori *</label>
                                            <select class="form-select @error('plant_type_id') is-invalid @enderror"
                                                id="plant_type_id" name="plant_type_id" required>
                                                <option value="">Pilih Kategori</option>
                                                @foreach ($plantTypes as $plantType)
                                                    <option value="{{ $plantType->plant_type_id }}"
                                                        {{ old('plant_type_id', $product->plant_type_id) == $plantType->plant_type_id ? 'selected' : '' }}>
                                                        {{ $plantType->plant_type_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('plant_type_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="description" class="form-label">Deskripsi *</label>
                                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                                rows="4">{{ str_replace('\\n', "\n", old('description', $product->description)) }}</textarea>
                                            @error('description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <h6 class="mb-3">Informasi Stok & Harga</h6>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="stock" class="form-label">Stok *</label>
                                                    <input type="number" step="0.01"
                                                        class="form-control @error('stock') is-invalid @enderror"
                                                        id="stock" name="stock"
                                                        value="{{ old('stock', $product->stock) }}" required>
                                                    @error('stock')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="minimum_stock" class="form-label">Stok Minimum *</label>
                                                    <input type="number" step="0.01"
                                                        class="form-control @error('minimum_stock') is-invalid @enderror"
                                                        id="minimum_stock" name="minimum_stock"
                                                        value="{{ old('minimum_stock', $product->minimum_stock) }}"
                                                        required>
                                                    @error('minimum_stock')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="unit" class="form-label">Satuan *</label>
                                                    <input type="text"
                                                        class="form-control @error('unit') is-invalid @enderror"
                                                        id="unit" name="unit"
                                                        value="{{ old('unit', $product->unit) }}" required>
                                                    @error('unit')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="minimum_purchase" class="form-label">Min. Pembelian
                                                        *</label>
                                                    <input type="number" step="0.01"
                                                        class="form-control @error('minimum_purchase') is-invalid @enderror"
                                                        id="minimum_purchase" name="minimum_purchase"
                                                        value="{{ old('minimum_purchase', $product->minimum_purchase) }}"
                                                        required>
                                                    @error('minimum_purchase')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="price_per_unit" class="form-label">Harga per Unit (Rp) *</label>
                                            <input type="number"
                                                class="form-control @error('price_per_unit') is-invalid @enderror"
                                                id="price_per_unit" name="price_per_unit"
                                                value="{{ old('price_per_unit', $product->price_per_unit) }}" required>
                                            @error('price_per_unit')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="mb-3">Gambar Produk</h6>
                                        @php
                                            $image1Path = Str::startsWith($product->image1, 'products/')
                                                ? $product->image1
                                                : 'products/' . $product->image1;

                                            $image2Path = Str::startsWith($product->image2, 'products/')
                                                ? $product->image2
                                                : 'products/' . $product->image2;

                                            $imageCertificatePath = Str::startsWith(
                                                $product->image_certificate,
                                                'certificates/',
                                            )
                                                ? $product->image_certificate
                                                : 'certificates/' . $product->image_certificate;
                                        @endphp
                                        @if ($product->image1)
                                            <div class="mb-3">
                                                <label class="form-label">Gambar Utama Saat Ini</label>
                                                <div>
                                                    <img src="{{ asset('storage/' . $image1Path) }}" alt="Gambar Utama"
                                                        style="max-width: 200px; height: auto;" class="img-thumbnail">
                                                </div>
                                            </div>
                                        @endif

                                        <div class="mb-3">
                                            <label for="image1" class="form-label">Ganti Gambar Utama</label>
                                            <input type="file"
                                                class="form-control @error('image1') is-invalid @enderror" id="image1"
                                                name="image1" accept="image/*">
                                            <div class="form-text">Format: JPG, PNG, GIF. Maksimal 2MB</div>
                                            @error('image1')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        @if ($product->image2)
                                            <div class="mb-3">
                                                <label class="form-label">Gambar Tambahan Saat Ini</label>
                                                <div>
                                                    <img src="{{ asset('storage/' . $image2Path) }}"
                                                        alt="Gambar Tambahan" style="max-width: 200px; height: auto;"
                                                        class="img-thumbnail">
                                                </div>
                                            </div>
                                        @endif

                                        <div class="mb-3">
                                            <label for="image2" class="form-label">Ganti Gambar Tambahan</label>
                                            <input type="file"
                                                class="form-control @error('image2') is-invalid @enderror" id="image2"
                                                name="image2" accept="image/*">
                                            <div class="form-text">Format: JPG, PNG, GIF. Maksimal 2MB</div>
                                            @error('image2')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <h6 class="mb-3">Informasi Sertifikat</h6>

                                        @if ($product->image_certificate)
                                            <div class="mb-3">
                                                <label class="form-label">Gambar Sertifikat Saat Ini</label>
                                                <div>
                                                    <img src="{{ asset('storage/' . $imageCertificatePath) }}"
                                                        alt="Gambar Sertifikat" style="max-width: 200px; height: auto;"
                                                        class="img-thumbnail">
                                                </div>
                                            </div>
                                        @endif

                                        <div class="mb-3">
                                            <label for="image_certificate" class="form-label">Ganti Gambar
                                                Sertifikat</label>
                                            <input type="file"
                                                class="form-control @error('image_certificate') is-invalid @enderror"
                                                id="image_certificate" name="image_certificate" accept="image/*">
                                            <div class="form-text">Format: JPG, PNG, GIF. Maksimal 2MB</div>
                                            @error('image_certificate')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="certificate_number" class="form-label">Nomor Sertifikat</label>
                                            <input type="text"
                                                class="form-control @error('certificate_number') is-invalid @enderror"
                                                id="certificate_number" name="certificate_number"
                                                value="{{ old('certificate_number', $product->certificate_number) }}">
                                            @error('certificate_number')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="certificate_class" class="form-label">Kelas Sertifikat</label>
                                            <input type="text"
                                                class="form-control @error('certificate_class') is-invalid @enderror"
                                                id="certificate_class" name="certificate_class"
                                                value="{{ old('certificate_class', $product->certificate_class) }}">
                                            @error('certificate_class')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="valid_from" class="form-label">Berlaku Dari</label>
                                                    <input type="date"
                                                        class="form-control @error('valid_from') is-invalid @enderror"
                                                        id="valid_from" name="valid_from"
                                                        value="{{ old('valid_from', $product->valid_from) }}">
                                                    @error('valid_from')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="valid_until" class="form-label">Berlaku Sampai</label>
                                                    <input type="date"
                                                        class="form-control @error('valid_until') is-invalid @enderror"
                                                        id="valid_until" name="valid_until"
                                                        value="{{ old('valid_until', $product->valid_until) }}">
                                                    @error('valid_until')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-end mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Produk
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplemde@1.11.2/dist/simplemde.min.css">
<style>
    .CodeMirror {
        min-height: 200px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/simplemde@1.11.2/dist/simplemde.min.js"></script>
<script>
    var simplemde = new SimpleMDE({ 
        element: document.getElementById("description"),
        spellChecker: false,
        status: false,
    });
</script>
@endpush
