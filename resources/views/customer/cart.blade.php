@extends('layouts.app')
@section('content')
    <style>
        .cart-main {
            width: 100%;
            display: flex !important;
            gap: 0px;
            align-items: flex-start;
            flex-direction: column;
        }

        .cart-left {
            flex: 1;
            max-width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        .cart-title {
            display: block;
            margin-bottom: 24px;
            color: #222;
            margin-top: 32px;
            margin-bottom: 24px;
            font-weight: bold;
        }

        .cart-title span {
            color: #388e3c;
            margin-left: 10px;
            font-size: 16px;
        }

        .cart-item-box {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border: 1px solid #e0e0e0;
        }

        .cart-item-image {
            width: 80px;
            height: 80px;
            min-width: 80px;
            object-fit: cover;
            min-height: 80px;
            border-radius: 8px;
            background: #f5f5f5;
            border: 1px solid #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #666;
        }

        .cart-item-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-width: 0;
            height: 100%;
        }

        .cart-item-name {
            font-size: 16px;
            font-weight: bold;
            color: #222;
            line-height: 1.3;
        }

        .cart-item-price-qty {
            font-size: 16px;
            font-weight: bold;
            color: #388e3c;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cart-item-qty-input {
            width: 75px;
            padding: 4px 8px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            text-align: center;
            font-size: 16px;
        }

        .cart-item-subtotal {
            display: none;
        }

        .cart-item-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            white-space: nowrap
        }

        .cart-empty-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 20px;
            text-align: center;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border: 1px solid #e0e0e0;
        }

        .cart-empty-img {
            font-size: 64px;
            margin-bottom: 24px;
        }


        .cart-summary {
            flex: 1;
            background: #fff;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            min-width: 300px;
            max-width: 350px;
            border: 1px solid #e0e0e0;
            height: fit-content;
            position: sticky;
            top: 80px;
            margin-top: 0px;
        }

        .cart-summary-title {
            margin-bottom: 24px;
            color: #222;
        }

        .cart-summary-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
            font-weight: bold;
        }

        .cart-row {
            width: 100%;
            margin-bottom: 0px;
            display: flex;
            flex-direction: row;
            gap: 20px;
        }

        .cart-item-actions i {
            color: #e53935;
            font-size: 1.4em;
        }


        .checkall-container {
            margin-bottom: 0px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .checkall-label {
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            user-select: none;
            font-size: 1rem;
            color: #222;
        }

        .checkall-box {
            accent-color: #388e3c;
            width: 20px;
            height: 20px;
            border-radius: 5px;
            border: 2px solid #388e3c;
        }

        .cart-item-checkbox {
            accent-color: #388e3c;
            width: 20px;
            height: 20px;
            border-radius: 4px;
            margin-right: 10px;
        }

        .appbar-cart {
            display: none;
        }

        @media (max-width: 1023px) {

            .mobile-search-container,
            .mobile-cart-btn {
                display: none !important;
            }

            .mobile-back-btn,
            .mobile-cart-title {
                display: flex !important;
            }

            .appbar-utama {
                display: none;
                /* hide utama */
            }

            .appbar-cart {
                display: block;
                /* show fokus */
            }

            .cart-main {
                padding: 0 0;
            }

            .cart-title {
                display: none;
            }

            .cart-row {
                flex-direction: column;
                gap: unset;
            }

            .cart-left {
                max-width: 100%;
                padding: 10px 6px;
            }

            .checkall-container {
                display: none;
            }

            .cart-item-name {
                flex: 1;
                overflow: hidden;
                white-space: nowrap;
                text-overflow: ellipsis;
                min-width: 0;
            }

            .cart-item-name a {
                display: block;
                overflow: hidden;
                white-space: nowrap;
                text-overflow: ellipsis;
            }

            .cart-item-content {
                gap: 4px;
            }

            .cart-item-checkbox {
                min-width: 16px;
                min-height: 16px;
                margin-right: 0;
            }

            .cart-item-box {
                padding: 12px;
                gap: 9px;
                margin-bottom: 6px;
            }

            .cart-item-price-qty {
                justify-content: space-between;
                flex-wrap: wrap;
            }

            .cart-summary {
                position: fixed;
                max-width: 100%;
                min-width: 100%;
                top: unset;
                bottom: 0;
                left: 0;
                right: 0;
                display: flex;
                height: 80px;
                border-radius: unset;
                padding: 16px;
                gap: 16px;
                align-items: center;
            }

            .cart-summary-title {
                display: none;
            }

            .cart-summary-total {
                flex-direction: column;
                margin: 0 0;
                align-items: flex-end;
                width: 100%;
            }

            .cart-summary .btn {
                margin: 0 0;
                padding: 0 0;
                height: 100%;
                width: 100%;
                max-width: 100px;
                min-width: 100px;
            }

            .checkall-container-mobile {
                display: flex !important;
                align-items: center;
                gap: 12px;
                width: 100%;
            }

        }

        #notification-container {
            position: fixed;
            top: 100px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            width: 90%;
            max-width: 600px;
            pointer-events: none;
        }

        .notification {
            background: #fff;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            animation: slideDown 0.3s ease-out;
            pointer-events: auto;
        }

        .notification.error {
            border-left: 4px solid #d32f2f;
            background: #ffebee;
            color: #d32f2f;
        }

        .notification.success {
            border-left: 4px solid #4CAF50;
            background: #e8f5e9;
            color: #2e7d32;
        }

        @keyframes slideDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
    
    <div id="notification-container"></div>

    <div class="container"style="margin-bottom: 40px;">

        @if (session('error'))
            <div
                style="background:#ffebee;border:1px solid #f44336;border-radius:8px;padding:12px;margin:16px;color:#d32f2f;font-size:0.9rem;">
                <i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i>
                {{ session('error') }}
            </div>
        @endif
        @if (session('success'))
            <div
                style="background:#e8f5e9;border:1px solid #4caf50;border-radius:8px;padding:12px;margin:16px;color:#2e7d32;font-size:0.9rem;">
                <i class="fas fa-check-circle" style="margin-right:6px;"></i>
                {{ session('success') }}
            </div>
        @endif

        <!-- Desktop Cart Summary -->
        <div class="cart-main">
            <div class="cart-row" style="flex-direction: column;">
                <!-- Tambahkan konten baris di sini jika diperlukan -->
                <!-- Desktop Title -->
                <div class="cart-title">
                    <h2>Keranjang
                        <span>
                            ({{ $items->count() }} Benih)
                        </span>
                    </h2>
                </div>
                <!-- Desktop Select All -->
                <div class="checkall-container" style="margin-bottom:20px;">
                    <input type="checkbox" id="checkAll" class="checkall-box" onclick="toggleAll(this)">
                    <label for="checkAll" class="checkall-label">Pilih Semua</label>
                </div>
            </div>
            <div class="cart-row">
                <!-- Tambahkan konten baris di sini jika diperlukan -->
                <div class="cart-left">
                    <!-- Desktop Empty State -->
                    <div class="cart-empty-box" id="desktop-empty-state" style="display: none;">
                        <div class="cart-empty-img">
                            <i class="fas fa-box-open" style="color:#ffd600;"></i>
                        </div>
                        <div class="cart-empty-content">
                            <h5>Wah, keranjang belanjamu kosong</h5>
                            <p>Yuk, isi dengan barang-barang impianmu!</p>
                            <button onclick="window.location.href='/'" class="btn-green w-50">Mulai Belanja</button>
                        </div>
                    </div>
                    <!-- Desktop Cart Items -->
                    @foreach ($items as $item)
                        @php
                            $availableStock = $item->product->stock - $item->product->minimum_stock;
                            $isOutOfStock = $availableStock <= 0;
                            $isQuantityExceedStock = $item->quantity > $availableStock;
                            $isQuantityBelowMin = $item->quantity < $item->product->minimum_purchase;
                        @endphp
                        <div class="cart-item-box"
                            style="{{ $isOutOfStock || $isQuantityBelowMin ? 'opacity: 0.7;' : '' }}">
                            <input type="checkbox" class="cart-item-checkbox" name="checked_items[]"
                                value="{{ $item->cart_item_id }}" onchange="updateSummary()" checked
                                {{ $isOutOfStock || $isQuantityBelowMin ? 'disabled' : '' }}>

                            @if ($item->product->image1)
                                @php
                                    $imagePath = Str::startsWith($item->product->image1, 'products/')
                                        ? $item->product->image1
                                        : 'products/' . $item->product->image1;
                                @endphp
                                <img src="{{ asset('storage/' . $imagePath) }}" alt="{{ $item->product->product_name }}"
                                    class="cart-item-image">
                            @else
                                <div class="cart-item-image">
                                    <i class="fas fa-seedling"></i>
                                </div>
                            @endif

                            <div class="cart-item-content">
                                <div class="cart-item-name">
                                    <a href="/produk/{{ $item->product->product_id }}"
                                        style="color:#222; text-decoration:none;">
                                        {{ $item->product->product_name }}
                                    </a>
                                </div>

                                @if ($isOutOfStock)
                                    <div style="color:#d32f2f;font-weight:500;font-size:0.9rem;margin-bottom:4px;">
                                        <i class="fas fa-exclamation-triangle"></i> Stok habis
                                    </div>
                                @elseif($isQuantityExceedStock)
                                    <div style="color:#d32f2f;font-size:12px;">
                                        <i class="fas fa-exclamation-triangle"></i> Stok tidak mencukupi (maks:
                                        {{ $availableStock }} {{ $item->product->unit }})
                                    </div>
                                @elseif($isQuantityBelowMin)
                                    <div style="color:#d32f2f;font-size:12px;">
                                        <i class="fas fa-exclamation-triangle"></i> Minimal pembelian:
                                        {{ number_format($item->product->minimum_purchase, 0, ',', '') }}
                                        {{ $item->product->unit }}
                                    </div>
                                @endif

                                <div class="cart-item-price-qty">
                                    <div style="width: 100%;">
                                        Rp{{ number_format($item->price_per_unit, 0, ',', '.') }} /
                                        {{ $item->product->unit }}
                                    </div>
                                    <div style="display: flex; justify-content:flex-end;width:100%;">
                                        <div class="cart-item-actions">
                                            <div onclick="deleteItem({{ $item->cart_item_id }})">
                                                <i
                                                    class="fas
                                                fa-trash-alt "></i>
                                            </div>
                                            <div class="cart-item-qty">
                                                <input type="number" step="0.01" id="qtyInput{{ $item->cart_item_id }}"
                                                    name="quantity" value="{{ $item->quantity }}"
                                                    min="{{ $item->product->minimum_purchase }}"
                                                    max="{{ $availableStock }}" class="cart-item-qty-input"
                                                    onchange="updateQtyDirect({{ $item->cart_item_id }}, {{ $item->product->minimum_purchase }})"
                                                    {{ $isOutOfStock || $isQuantityBelowMin ? 'disabled' : '' }}>
                                                <span style="color: #222">{{ $item->product->unit }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="cart-item-subtotal" id="subtotal{{ $item->cart_item_id }}">
                                    {{ number_format($item->price_per_unit * $item->quantity, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="cart-summary">
                    <div class="cart-summary-title">
                        <h4>
                            Ringkasan belanja
                        </h4>
                    </div>
                    <div class="checkall-container-mobile" style="display: none">
                        <input type="checkbox" id="checkAllMobile" class="checkall-box" onclick="toggleAll(this)">
                        <label for="checkAllMobile" class="checkall-label">Pilih Semua</label>
                    </div>
                    <div class="cart-summary-total">
                        <span style="color:#666;">Total</span>
                        <span id="summaryTotal" style="color:#388e3c;">Rp{{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                    <form id="checkoutForm" method="POST" action="{{ route('checkout.process') }}" style="display:none;">
                        @csrf
                        <input type="hidden" name="checked_items" id="checkedItemsInput">
                    </form>
                    <button class="btn-green w-100" id="checkoutBtn" type="button" disabled
                        onclick="submitCheckout()">Beli</button>
                </div>
            </div>
        </div>
    </div>

    @include('customer.partials.modal_tambah_alamat')

    <script>
        // Ambil id item baru dari session (jika ada)
        const newCartItemId = @json(session('new_cart_item_id'));
        // Helper untuk localStorage
        function getCheckedCartItems() {
            try {
                return JSON.parse(localStorage.getItem('checkedCartItems') || '[]');
            } catch (e) {
                return [];
            }
        }

        function showNotification(message, type = 'error') {
            const container = document.getElementById('notification-container');
            const notif = document.createElement('div');
            notif.className = `notification ${type}`;
            notif.innerHTML = `
                <div style="display:flex;align-items:center;">
                    <i class="fas ${type === 'error' ? 'fa-exclamation-triangle' : 'fa-check-circle'}" style="margin-right:10px;"></i>
                    <span>${message}</span>
                </div>
            `;
            container.appendChild(notif);
            
            setTimeout(() => {
                notif.style.opacity = '0';
                notif.style.transform = 'translateY(-20px)';
                notif.style.transition = 'all 0.3s ease-out';
                setTimeout(() => notif.remove(), 300);
            }, 3000);
        }

        function setCheckedCartItems(ids) {
            localStorage.setItem('checkedCartItems', JSON.stringify(ids));
        }

        function updateSummary() {
            let checkboxes = document.querySelectorAll('.cart-item-checkbox');
            let total = 0;
            let checkedCount = 0;
            let allChecked = true;
            let checkedIds = [];
            let hasInvalidItems = false;

            checkboxes.forEach(cb => {
                if (cb.checked) {
                    let id = cb.value;
                    let subtotalText = document.getElementById('subtotal' + id)?.innerText;
                    if (subtotalText) {
                        let subtotal = parseInt(subtotalText.replace(/[^\d]/g, ''));
                        total += isNaN(subtotal) ? 0 : subtotal;
                    }
                    checkedCount++;
                    checkedIds.push(id);

                    if (cb.disabled) {
                        hasInvalidItems = true;
                    }
                } else {
                    allChecked = false;
                }
            });

            setCheckedCartItems(checkedIds);

            // Update totals
            const summaryTotal = document.getElementById('summaryTotal');

            if (summaryTotal) {
                summaryTotal.innerText = 'Rp' + total.toLocaleString('id-ID');
            }

            const checkoutBtn = document.getElementById('checkoutBtn');

            if (checkoutBtn) {
                checkoutBtn.disabled = checkedCount === 0 || hasInvalidItems;
            }

            // Sinkronisasi checkbox 'Pilih Semua'
            const checkAll = document.getElementById('checkAll');
            const checkAllMobile = document.getElementById('checkAllMobile')
            if (checkAll) {
                checkAll.checked = allChecked && checkboxes.length > 0;
                checkAll.indeterminate = !allChecked && checkedCount > 0;
            }
            if (checkAllMobile) {
                checkAllMobile.checked = allChecked && checkboxes.length > 0;
                checkAllMobile.indeterminate = !allChecked && checkedCount > 0;
            }
        }

        function toggleAll(source) {
            let checkboxes = document.querySelectorAll('.cart-item-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = source.checked;
            });
            updateSummary();
        }

        function updateQtyDirect(cartItemId, minimalPembelian) {
            let input = document.getElementById('qtyInput' + cartItemId);
            if (!input) return;

            let val = input.value.replace(',', '.');
            let unit = input.nextElementSibling ? input.nextElementSibling.innerText.trim() : '';
            let qty;

            if (["Mata", "Tanaman", "Rizome"].includes(unit)) {
                qty = parseInt(val);
                // Cek max stock
                if (input.max && qty > parseFloat(input.max)) {
                    qty = parseFloat(input.max);
                    input.value = qty;
                    showNotification('Maaf, stok tidak mencukupi. Maksimal: ' + qty + ' ' + unit, 'error');
                }
                
                if (isNaN(qty) || qty < minimalPembelian) {
                    qty = minimalPembelian;
                    input.value = qty;
                    showNotification('Minimal pembelian: ' + minimalPembelian.toLocaleString('id-ID') + ' ' + unit, 'error');
                }
            } else {
                qty = parseFloat(val);
                // Batasi 2 angka desimal
                if (!isNaN(qty)) {
                    qty = parseFloat(qty.toFixed(2));
                }
                
                // Cek max stock
                if (input.max && qty > parseFloat(input.max)) {
                    qty = parseFloat(input.max);
                    input.value = qty;
                    showNotification('Maaf, stok tidak mencukupi. Maksimal: ' + qty + ' ' + unit, 'error');
                }
                
                if (isNaN(qty) || qty < minimalPembelian) {
                    qty = minimalPembelian;
                    input.value = qty;
                    showNotification('Minimal pembelian: ' + minimalPembelian.toLocaleString('id-ID') + ' ' + unit, 'error');
                }
            }

            let formData = new FormData();
            formData.append('quantity', qty);
            formData.append('_token', '{{ csrf_token() }}');

            fetch(`{{ route('cart.update_qty', ['cart_item' => 'placeholder']) }}`.replace('placeholder', cartItemId), {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        input.value = data.quantity;
                        document.getElementById('subtotal' + cartItemId).innerText = data.subtotal;
                        updateSummary();
                    } else {
                        showNotification(data.message || 'Terjadi kesalahan saat mengupdate quantity', 'error');
                        input.value = minimalPembelian;
                        updateSummary();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Terjadi kesalahan saat mengupdate quantity', 'error');
                    input.value = minimalPembelian;
                    updateSummary();
                });
        }

        function submitCheckout() {
            if (document.getElementById('checkoutBtn')?.disabled) return;

            let checkedItems = Array.from(document.querySelectorAll('.cart-item-checkbox:checked')).map(cb => cb.value);
            if (checkedItems.length === 0) {
                showNotification('Pilih minimal satu barang untuk checkout', 'error');
                return;
            }

            let hasAddress = @json($hasAddress);
            if (!hasAddress) {
                var modal = new bootstrap.Modal(document.getElementById('modalTambahAlamat'));
                modal.show();
                return;
            }

            document.getElementById('checkedItemsInput').value = JSON.stringify(checkedItems);
            document.getElementById('checkoutForm').submit();
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Set status checkbox sesuai localStorage
            let checkedIds = getCheckedCartItems();
            let checkboxes = document.querySelectorAll('.cart-item-checkbox');

            // Jika ada item baru, pastikan hanya item baru yang otomatis tercentang
            let changed = false;
            if (newCartItemId) {
                checkboxes.forEach(cb => {
                    if (cb.value == newCartItemId) {
                        cb.checked = true;
                        if (!checkedIds.includes(cb.value)) {
                            checkedIds.push(cb.value);
                            changed = true;
                        }
                    } else {
                        // Status centang item lama tetap mengikuti localStorage
                        cb.checked = checkedIds.includes(cb.value);
                    }
                });
                if (changed) {
                    setCheckedCartItems(checkedIds);
                }
            } else {
                // Jika tidak ada item baru, status centang item lama tetap mengikuti localStorage
                checkboxes.forEach(cb => {
                    cb.checked = checkedIds.includes(cb.value);
                });
            }

            updateSummary();

            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateSummary);
            });

            document.getElementById('checkAll')?.addEventListener('change', function() {
                toggleAll(this);
            });

            document.getElementById('checkAllMobile')?.addEventListener('change', function() {
                toggleAll(this);
            });

            const initialItemCount = document.querySelectorAll('.cart-item-checkbox').length;

            updatePageVisibility(initialItemCount);

            // Real-time input validation listener
            document.querySelectorAll('.cart-item-qty-input').forEach(input => {
                input.addEventListener('input', function() {
                    let val = this.value;
                    let max = parseFloat(this.max);
                    
                    // Format decimals
                    if (val.includes('.')) {
                        let parts = val.split('.');
                        if (parts[1].length > 2) {
                            this.value = parseFloat(val).toFixed(2);
                            val = this.value;
                        }
                    }
                    
                    // Max stock check
                    if (max && parseFloat(val) > max) {
                        this.value = max;
                        showNotification('Stok tidak mencukupi. Maksimal: ' + max + ' ' + (this.nextElementSibling?.innerText || ''), 'error');
                    }
                });
            });
        });

        function updatePageVisibility(itemCount) {
            const emptyState = document.getElementById('desktop-empty-state');
            const checkAllContainer = document.querySelector('.checkall-container');
            const cartItemCountSpan = document.querySelector('.cart-title span');

            if (itemCount === 0) {
                if (emptyState) emptyState.style.display = 'flex';
                if (checkAllContainer) checkAllContainer.style.display = 'none';
            } else {
                if (emptyState) emptyState.style.display = 'none';
            }

            cartItemCountSpan.innerText = `(${itemCount} Benih)`;
        }

        function deleteItem(cartItemId) {
            fetch(`{{ route('cart.delete', ['cart_item' => 'placeholder']) }}`.replace('placeholder', cartItemId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const itemToRemove = document.querySelector(`.cart-item-checkbox[value="${cartItemId}"]`)
                            ?.closest('.cart-item-box');

                        if (itemToRemove) {
                            itemToRemove.remove();
                        }
                        updateSummary();
                        const remainingItems = document.querySelectorAll('.cart-item-checkbox');
                        updatePageVisibility(remainingItems.length);
                    } else {
                        showNotification(data.message || 'Gagal menghapus item', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Terjadi kesalahan saat menghapus item', 'error');
                });
        }
    </script>

@section('after_content')
    @include('customer.partials.mitra_footer')
@endsection
@endsection
