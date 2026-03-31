@extends('layouts.app')

@section('content')
<h1 class="h3 mb-3">Create Purchase Order</h1>

<form method="POST" action="{{ route('purchase-orders.store') }}">
    @csrf

    <div class="mb-3">
        <label class="form-label fw-semibold">Product</label>
        <select id="product_select" class="form-select">
            <option value="">-- Select Product --</option>
            @foreach($products as $product)
                <option value="{{ $product->id }}">{{ $product->name }}</option>
            @endforeach
        </select>
        <div class="form-text">Select a product to see which suppliers carry it.</div>
    </div>

    <div class="mb-3" id="supplier_wrapper" style="display:none">
        <label class="form-label fw-semibold">Supplier</label>
        <div id="supplier_loading" class="text-muted small mb-1" style="display:none">Loading suppliers...</div>
        <div id="no_suppliers" class="text-danger small mb-1" style="display:none">No active suppliers found for this product.</div>
        <select name="supplier_id" id="supplier_select" class="form-select" required>
            <option value="">-- Select Supplier --</option>
        </select>
        <div class="form-text">Sorted by best KPI, then lowest price.</div>
    </div>

    <div class="mb-3">
        <label class="form-label">Order Date</label>
        <input type="date" name="order_date" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Expected Delivery</label>
        <input type="date" name="expected_delivery_date" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="draft">Draft</option>
            <option value="confirmed">Confirmed</option>
            <option value="received">Received</option>
        </select>
    </div>

    <button class="btn btn-primary">Create</button>
    <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary ms-2">Cancel</a>
</form>

<script>
document.getElementById('product_select').addEventListener('change', function () {
    const productId = this.value;
    const wrapper   = document.getElementById('supplier_wrapper');
    const select    = document.getElementById('supplier_select');
    const loading   = document.getElementById('supplier_loading');
    const noResult  = document.getElementById('no_suppliers');

    // Reset state
    select.innerHTML = '<option value="">-- Select Supplier --</option>';
    noResult.style.display = 'none';

    if (!productId) {
        wrapper.style.display = 'none';
        return;
    }

    wrapper.style.display = 'block';
    loading.style.display = 'block';
    select.disabled = true;

    fetch(`/purchase-orders/suppliers-by-product?product_id=${productId}`)
        .then(r => r.json())
        .then(suppliers => {
            loading.style.display = 'none';
            select.disabled = false;

            if (suppliers.length === 0) {
                noResult.style.display = 'block';
                return;
            }

            suppliers.forEach(s => {
                const kpi  = s.kpi_score  !== null ? parseFloat(s.kpi_score).toFixed(2)  : '-';
                const lead = s.lead_time_days !== null ? s.lead_time_days + ' days' : '-';
                const price = s.price !== null
                    ? 'Rp ' + parseFloat(s.price).toLocaleString('id-ID')
                    : '-';
                const label = `${s.name} — KPI ${kpi} — Lead ${lead} — ${price}`;
                select.appendChild(new Option(label, s.id));
            });
        })
        .catch(() => {
            loading.style.display = 'none';
            select.disabled = false;
        });
});
</script>
@endsection
