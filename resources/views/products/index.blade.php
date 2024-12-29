@extends('layouts.tabler')

@section('content')
<div class="page-body">
<div class="row g-2 align-items-center">
    <div class="col-auto ms-auto d-print-none">
        <div class="btn-list m-6">
            <a href="{{ route('orders.create') }}" class="btn btn-primary d-none d-sm-inline-block">
                <x-icon.plus/>
                Create new order
            </a>
            <a href="{{ route('orders.create') }}" class="btn btn-primary d-sm-none btn-icon" aria-label="Create new order">
                <x-icon.plus/>
            </a>
        </div>
</div>
</div>           
    @if($products->isEmpty())
        <x-empty
            title="No products found"
            message="Try adjusting your search or filter to find what you're looking for."
            button_label="{{ __('Add your first Product') }}"
            button_route="{{ route('products.create') }}"
        />
    @else
        <div class="container container-xl">
            <x-alert/>

            @livewire('tables.product-table')
        </div>
    @endif
</div>
@endsection
