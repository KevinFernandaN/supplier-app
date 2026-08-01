@extends('layouts.app')

@section('title', 'Send to Purchase Request – ' . $rabPeriod->name)

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('rab-periods.index') }}">RAB Periods</a></li>
        <li class="breadcrumb-item"><a href="{{ route('rab-periods.show', $rabPeriod) }}">{{ $rabPeriod->name }}</a></li>
        <li class="breadcrumb-item active">Send to Purchase Request</li>
    </ol>
</nav>

<h1 class="h3 mb-3">Send to Purchase Request</h1>

<div class="card" style="max-width:600px">
    <div class="card-body">
        @if($existing && $locked)
            {{-- Already sent, and past the H-1 lock date: no changes allowed --}}
            <div class="alert alert-danger">
                This period is past its H-1 lock date
                (<strong>{{ $rabPeriod->prLockDate()->format('d M Y') }}</strong>) and can no longer be updated,
                to prevent data manipulation after the cook-off deadline.
            </div>
            <p class="text-muted small">
                Purchase Request #{{ $existing->id }} was generated from this period and is now final.
            </p>
            <a href="{{ route('purchase-requests.show', $existing) }}" class="btn btn-primary">View Purchase Request #{{ $existing->id }}</a>

        @elseif($existing)
            {{-- Already sent, but still before the lock date: allow an explicit, confirmed update --}}
            <div class="alert alert-warning">
                This period was already sent as <strong>Purchase Request #{{ $existing->id }}</strong>
                ({{ ucfirst($existing->status) }}). Updating will recalculate quantities from the current
                RAB data and <strong>overwrite</strong> that PR's ingredient list and total portions.
            </div>
            <p class="text-muted small">
                You can update until <strong>{{ $rabPeriod->prLockDate()->format('d M Y') }}</strong> (H-1) —
                after that, this period locks and can no longer be changed.
            </p>

            <form method="POST" action="{{ route('rab-periods.purchase-requests.store', $rabPeriod) }}"
                  onsubmit="return confirm('This will overwrite Purchase Request #{{ $existing->id }} with the latest RAB data. Continue?');">
                @csrf
                <input type="hidden" name="confirm_update" value="1">
                <div class="d-flex gap-2">
                    <button class="btn btn-warning">Yes, Update Purchase Request #{{ $existing->id }}</button>
                    <a href="{{ route('purchase-requests.show', $existing) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>

        @else
            {{-- First time sending this period --}}
            <p class="text-muted small">
                This creates one consolidated Purchase Request for the whole period, combining ingredient
                quantities across every menu and every day. You can update it later (until the H-1 lock date),
                with an explicit confirmation each time.
            </p>

            <form method="POST" action="{{ route('rab-periods.purchase-requests.store', $rabPeriod) }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Kitchen <span class="text-danger">*</span></label>
                    <select name="kitchen_id" class="form-select @error('kitchen_id') is-invalid @enderror" required>
                        <option value="">-- Select Kitchen --</option>
                        @foreach($kitchens as $kitchen)
                            <option value="{{ $kitchen->id }}" @selected(old('kitchen_id') == $kitchen->id)>
                                {{ $kitchen->name }} — {{ ucfirst($kitchen->type) }}
                            </option>
                        @endforeach
                    </select>
                    @if($kitchens->isEmpty())
                        <div class="form-text text-danger">
                            No active kitchens found in this period's region. Add one under Master Data &rarr; Kitchens first.
                        </div>
                    @endif
                    @error('kitchen_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary" @disabled($kitchens->isEmpty())>Generate Purchase Request</button>
                    <a href="{{ route('rab-periods.show', $rabPeriod) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
