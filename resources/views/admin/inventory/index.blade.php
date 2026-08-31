@extends('admin.layouts.app')

@section('title', 'Inventory')
@section('header', 'Inventory')
@section('eyebrow', 'Operations / stock control')

@section('content')

@if(session('success'))
    <div class="admin-alert admin-alert-success" style="margin-bottom:14px">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="admin-alert admin-alert-danger" style="margin-bottom:14px">
        {{ $errors->first() }}
    </div>
@endif

<div
    style="
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap:16px;
        flex-wrap:wrap;
        margin-bottom:18px;
    "
>
    <div>
        <h2
            style="
                margin:0;
                font-size:27px;
                letter-spacing:-.035em;
            "
        >
            Inventory command center
        </h2>

        <p
            class="admin-muted"
            style="
                margin:7px 0 0;
                font-size:12px;
            "
        >
            Availability, tracked quantity, movement history and controlled
            stock adjustments.
        </p>
    </div>

    <a
        class="admin-btn"
        href="{{ route('admin.inventory.export', request()->only('reason')) }}"
    >
        Export movement CSV
    </a>
</div>


{{-- =========================================================
     SUMMARY
========================================================= --}}

<div
    style="
        display:grid;
        grid-template-columns:repeat(4,minmax(0,1fr));
        gap:10px;
        margin-bottom:14px;
    "
>
    @foreach([
        ['Tracked', $summary['tracked']],
        ['Simple availability', $summary['untracked']],
        ['Low stock', $summary['low']],
        ['Out of stock', $summary['out']],
    ] as [$label, $value])

        <div
            class="admin-card"
            style="padding:15px"
        >
            <div class="admin-eyebrow">
                {{ $label }}
            </div>

            <div
                style="
                    font-size:21px;
                    font-weight:720;
                    margin-top:7px;
                "
            >
                {{ number_format($value) }}
            </div>
        </div>

    @endforeach
</div>


{{-- =========================================================
     MAIN GRID
========================================================= --}}

<div class="admin-section-grid">

    {{-- =====================================================
         PRODUCT INVENTORY
    ====================================================== --}}

    <section class="admin-card">

        <form
            method="GET"
            action="{{ route('admin.inventory.index') }}"
            style="
                display:grid;
                grid-template-columns:minmax(200px,1fr) 160px 160px auto;
                gap:8px;
                padding:14px;
                border-bottom:1px solid #e4e7ec;
            "
        >
            <input
                class="admin-field"
                name="q"
                value="{{ request('q') }}"
                placeholder="Search product or SKU"
            >

            <select
                class="admin-field"
                name="mode"
            >
                <option value="">
                    All inventory modes
                </option>

                <option
                    value="tracked"
                    @selected(request('mode') === 'tracked')
                >
                    Tracked quantity
                </option>

                <option
                    value="simple"
                    @selected(request('mode') === 'simple')
                >
                    Simple availability
                </option>
            </select>

            <select
                class="admin-field"
                name="state"
            >
                <option value="">
                    All stock states
                </option>

                <option
                    value="in"
                    @selected(request('state') === 'in')
                >
                    In stock
                </option>

                <option
                    value="low"
                    @selected(request('state') === 'low')
                >
                    Low stock
                </option>

                <option
                    value="out"
                    @selected(request('state') === 'out')
                >
                    Out of stock
                </option>
            </select>

            <button
                type="submit"
                class="admin-btn"
            >
                Filter
            </button>
        </form>


        {{-- PRODUCT TABLE --}}

        <div class="admin-table-wrap">

            <table class="admin-table">

                <thead>
                    <tr>
                        <th>
                            Product
                        </th>

                        <th>
                            Mode
                        </th>

                        <th>
                            Available
                        </th>

                        <th style="text-align:right">
                            Quantity
                        </th>

                        <th style="text-align:right">
                            Action
                        </th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($products as $product)

                        @php
                            $tracked = (bool) $product->track_inventory;

                            $stock = max(
                                (int) ($product->stock ?? 0),
                                (int) ($product->stock_quantity ?? 0)
                            );

                            $available = $tracked
                                ? $stock > 0
                                : (bool) $product->is_in_stock;
                        @endphp

                        <tr>

                            {{-- PRODUCT --}}

                            <td>
                                <a
                                    href="{{ route('admin.products.edit', $product) }}"
                                    style="
                                        font-weight:700;
                                        text-decoration:none;
                                    "
                                >
                                    {{ $product->name }}
                                </a>

                                <div
                                    class="admin-muted"
                                    style="
                                        font-size:9px;
                                        margin-top:4px;
                                    "
                                >
                                    {{ $product->sku ?: 'No SKU' }}

                                    ·

                                    {{ $product->size_label ?: '—' }}
                                </div>
                            </td>


                            {{-- MODE --}}

                            <td>
                                @if($tracked)
                                    Tracked
                                @else
                                    Simple
                                @endif
                            </td>


                            {{-- AVAILABILITY --}}

                            <td>
                                @if($available)

                                    <span class="admin-status success">
                                        In stock
                                    </span>

                                @else

                                    <span class="admin-status warning">
                                        Out of stock
                                    </span>

                                @endif
                            </td>


                            {{-- QUANTITY --}}

                            <td
                                style="
                                    text-align:right;
                                    font-weight:700;
                                "
                            >
                                @if($tracked)

                                    {{ $stock }}

                                @else

                                    —

                                @endif
                            </td>


                            {{-- ACTION --}}

                            <td style="text-align:right">

                                @if(!$tracked)

                                    <form
                                        method="POST"
                                        action="{{ route('admin.inventory.availability', $product) }}"
                                        style="
                                            display:inline-flex;
                                            margin:0;
                                        "
                                    >
                                        @csrf

                                        <input
                                            type="hidden"
                                            name="is_in_stock"
                                            value="{{ $available ? 0 : 1 }}"
                                        >

                                        <button
                                            type="submit"
                                            class="admin-btn"
                                        >
                                            @if($available)
                                                Mark out
                                            @else
                                                Mark in
                                            @endif
                                        </button>

                                    </form>

                                @else

                                    <a
                                        class="admin-btn"
                                        href="#stock-adjustment"
                                    >
                                        Adjust
                                    </a>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="5"
                                class="admin-muted"
                                style="
                                    padding:40px;
                                    text-align:center;
                                "
                            >
                                No inventory products found.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- PAGINATION --}}

        <div style="padding:14px">
            {{ $products->links() }}
        </div>

    </section>


    {{-- =====================================================
         RIGHT COLUMN
    ====================================================== --}}

    <aside
        style="
            display:grid;
            gap:14px;
            align-content:start;
        "
    >

        {{-- =================================================
             STOCK ADJUSTMENT
        ================================================== --}}

        <form
            id="stock-adjustment"
            method="POST"
            action="{{ route('admin.inventory.adjust') }}"
            class="admin-card"
            style="padding:20px"
        >
            @csrf

            <div class="admin-eyebrow">
                Manual movement
            </div>

            <h3
                style="
                    font-size:16px;
                    margin:7px 0 17px;
                "
            >
                Stock adjustment
            </h3>


            <div
                style="
                    display:grid;
                    gap:10px;
                "
            >

                {{-- PRODUCT --}}

                <select
                    required
                    name="product_id"
                    class="admin-field"
                >
                    <option value="">
                        Select product
                    </option>

                    @foreach($adjustableProducts as $product)

                        <option
                            value="{{ $product->id }}"
                            @selected(
                                (string) old('product_id')
                                ===
                                (string) $product->id
                            )
                        >
                            {{ $product->name }}

                            @if($product->sku)
                                · {{ $product->sku }}
                            @endif
                        </option>

                    @endforeach
                </select>


                {{-- OPTIONAL VARIANT --}}

                <input
                    class="admin-field"
                    name="product_variant_id"
                    type="number"
                    min="1"
                    value="{{ old('product_variant_id') }}"
                    placeholder="Variant ID (optional)"
                >


                {{-- QUANTITY CHANGE --}}

                <input
                    class="admin-field"
                    required
                    name="quantity_change"
                    type="number"
                    value="{{ old('quantity_change') }}"
                    placeholder="+10 received or -2 damaged"
                >


                {{-- REASON --}}

                <select
                    class="admin-field"
                    name="reason"
                >
                    @foreach([
                        'manual',
                        'received',
                        'damage',
                        'correction',
                        'return',
                        'reserved',
                        'cycle_count',
                    ] as $reason)

                        <option
                            value="{{ $reason }}"
                            @selected(
                                old('reason', 'manual') === $reason
                            )
                        >
                            {{ ucfirst(
                                str_replace('_', ' ', $reason)
                            ) }}
                        </option>

                    @endforeach
                </select>


                {{-- REFERENCE --}}

                <input
                    class="admin-field"
                    name="reference"
                    value="{{ old('reference') }}"
                    placeholder="Reference / PO / ticket"
                >


                {{-- NOTE --}}

                <textarea
                    class="admin-field"
                    style="
                        padding-top:10px;
                        min-height:80px;
                    "
                    name="note"
                    placeholder="Internal note"
                >{{ old('note') }}</textarea>


                <button
                    type="submit"
                    class="admin-btn admin-btn-primary"
                >
                    Apply adjustment
                </button>

            </div>

        </form>


        {{-- =================================================
             MOVEMENT LEDGER
        ================================================== --}}

        <section class="admin-card">

            <div class="admin-card-header">

                <div>

                    <div class="admin-eyebrow">
                        Movement ledger
                    </div>

                    <div
                        style="
                            font-size:14px;
                            font-weight:700;
                            margin-top:4px;
                        "
                    >
                        Recent changes
                    </div>

                </div>

            </div>


            <div
                style="
                    max-height:620px;
                    overflow:auto;
                "
            >

                @forelse($adjustments as $adjustment)

                    <div
                        style="
                            padding:14px 18px;
                            border-bottom:1px solid #edf0f3;
                        "
                    >

                        <div
                            style="
                                display:flex;
                                justify-content:space-between;
                                gap:12px;
                            "
                        >

                            <div
                                style="
                                    font-size:11px;
                                    font-weight:700;
                                "
                            >
                                {{ optional($adjustment->product)->name
                                    ?: 'Deleted product' }}
                            </div>


                            @if($adjustment->quantity_change < 0)

                                <strong
                                    style="
                                        font-size:12px;
                                        color:#b42318;
                                    "
                                >
                                    {{ $adjustment->quantity_change }}
                                </strong>

                            @else

                                <strong
                                    style="
                                        font-size:12px;
                                        color:#16794b;
                                    "
                                >
                                    +{{ $adjustment->quantity_change }}
                                </strong>

                            @endif

                        </div>


                        <div
                            class="admin-muted"
                            style="
                                font-size:9px;
                                margin-top:4px;
                            "
                        >
                            {{ str_replace(
                                '_',
                                ' ',
                                $adjustment->reason
                            ) }}

                            · after
                            {{ $adjustment->quantity_after }}

                            ·
                            {{ optional(
                                $adjustment->created_at
                            )->format('d M H:i') }}
                        </div>


                        @if($adjustment->reference)

                            <div
                                class="admin-muted"
                                style="
                                    font-size:9px;
                                    margin-top:3px;
                                "
                            >
                                Ref:
                                {{ $adjustment->reference }}
                            </div>

                        @endif


                        @if($adjustment->note)

                            <div
                                class="admin-muted"
                                style="
                                    font-size:9px;
                                    margin-top:3px;
                                    line-height:1.5;
                                "
                            >
                                {{ $adjustment->note }}
                            </div>

                        @endif

                    </div>

                @empty

                    <div
                        class="admin-muted"
                        style="
                            padding:35px;
                            text-align:center;
                            font-size:11px;
                        "
                    >
                        No stock movements yet.
                    </div>

                @endforelse

            </div>

        </section>

    </aside>

</div>


{{-- =========================================================
     RESPONSIVE
========================================================= --}}

<style>

    @media (max-width: 1000px) {

        .admin-section-grid {
            grid-template-columns: 1fr !important;
        }

    }


    @media (max-width: 850px) {

        div[style*="grid-template-columns:repeat(4"] {
            grid-template-columns:
                repeat(2, minmax(0, 1fr)) !important;
        }

    }


    @media (max-width: 700px) {

        .admin-card > form[method="GET"] {
            grid-template-columns: 1fr !important;
        }

    }


    @media (max-width: 600px) {

        div[style*="grid-template-columns:repeat(4"] {
            grid-template-columns: 1fr !important;
        }

    }

</style>

@endsection