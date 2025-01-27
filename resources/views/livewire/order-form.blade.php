<div>

    @session('message')
    <div class="p-4 bg-green-100">
        {{ $value }}
    </div>
    @endsession


    <table class="table table-bordered" id="products_table">
        <thead class="thead-dark">
            <tr>
                <th class="align-middle">Product</th>
                <th class="align-middle text-center">Quantity</th>
                <th class="align-middle text-center">Price</th>
                <th class="align-middle text-center">Total</th>
                <th class="align-middle text-center">Action</th>
            </tr>
        </thead>

        <tbody>
{{--            @php--}}
{{--                dd(Cart::instance('order')->content())--}}
{{--            @endphp--}}

            @foreach ($invoiceProducts as $index => $invoiceProduct)
            <tr>
                <td class="align-middle">
                    @if($invoiceProduct['is_saved'])
                        <input type="hidden" name="invoiceProducts[{{$index}}][product_id]" value="{{ $invoiceProduct['product_id'] }}">
                        {{ $invoiceProduct['product_name'] }}
                    @else
                        <!-- Search Field -->
                        <div 
                            class="ms-auto text-secondary"
                            style="position: relative; width: 100%;>
                            <div class="ms-2 d-inline-block">
                                <!-- Livewire search binding -->
                                
                                <input type="text" 
                                    wire:model.live="search_{{$index}}" 
                                    id="search-input-{{$index}}" 
                                    class="form-control mb-3 form-control-sm" 
                                    aria-label="Search product" 
                                    placeholder="Search product"
                                    oninput="filterOptions({{$index}})"
                                    style="width: 100%; box-sizing: border-box;"
                                    onfocus="showDropdown({{$index}})" 
                                />
                            </div>
                        </div>
                
                        <!-- Dropdown -->
                        <div style="position: relative;">
                            <select 
                                wire:model.live="invoiceProducts.{{$index}}.product_id"
                                id="invoiceProducts[{{$index}}][product_id]"
                                class="form-control text-center @error('invoiceProducts.' . $index . '.product_id') is-invalid @enderror"
                                size="5"
                                onchange="updateSearchInput({{$index}})"
                                 style="width: 100%; box-sizing: border-box; position: absolute; top: 100%; z-index: 10; display: none;"
                            >
                                <option value="" class="text-center">-- choose product --</option>
                                @foreach ($allProducts as $product)
                                    <option 
                                        value="{{ $product->id }}" 
                                        class="text-center"
                                        data-name="{{ $product->name }}">
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                
                        @error('invoiceProducts.' . $index)
                            <em class="text-danger">
                                {{ $message }}
                            </em>
                        @enderror
                    @endif
                </td>

                <td class="align-middle text-center">
                    @if($invoiceProduct['is_saved'])
                        {{ $invoiceProduct['quantity'] }}

                        <input type="hidden"
                               name="invoiceProducts[{{$index}}][quantity]"
                               value="{{ $invoiceProduct['quantity'] }}"
                        >
                    @else
                        <input type="number"
                               wire:model="invoiceProducts.{{$index}}.quantity"
                               id="invoiceProducts[{{$index}}][quantity]"
                               class="form-control"
                        />
                    @endif
                </td>

                {{--- Unit Price ---}}
                <td class="align-middle text-center">
                    @if($invoiceProduct['is_saved'])
                        {{ $unit_cost = number_format($invoiceProduct['product_price'], 2) }}

                        <input type="hidden"
                               name="invoiceProducts[{{$index}}][unitcost]"
                               value="{{ $unit_cost }}"
                        >
                    @endif
                </td>

                {{--- Total ---}}
                <td class="align-middle text-center">
                    {{ $product_total = $invoiceProduct['product_price'] * $invoiceProduct['quantity'] }}

                    <input type="hidden"
                           name="invoiceProducts[{{$index}}][total]"
                           value="{{ $product_total }}"
                    >
                </td>

                <td class="align-middle text-center">
                    @if($invoiceProduct['is_saved'])
                        <button type="button" wire:click="editProduct({{$index}})" class="btn btn-icon btn-outline-warning">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-pencil" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" /><path d="M13.5 6.5l4 4" /></svg>
                        </button>

                    @elseif($invoiceProduct['product_id'])

                        <button type="button" wire:click="saveProduct({{$index}})" class="btn btn-icon btn-outline-success mr-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-device-floppy" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg>
                        </button>
                    @endif

                    <button type="button" wire:click="removeProduct({{$index}})" class="btn btn-icon btn-outline-danger">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                    </button>
                </td>
            </tr>
            @endforeach
            <tr>
                <td colspan="4"></td>
                <td class="text-center">
                    <button type="button" wire:click="addProduct" class="btn btn-icon btn-success">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                    </button>
                </td>
            </tr>
            <tr>
                <th colspan="4" class="align-middle text-end">
                    Subtotal
                </th>
                <td class="text-center">
{{--                    ${{ number_format($subtotal, 2) }}--}}
                    {{ Number::currency($subtotal, 'KSH.') }}
                </td>
            </tr>
            <tr>
                <th colspan="4" class="align-middle text-end">
                    Taxes
                </th>
                <td width="150" class="align-middle text-center">
                    <input wire:model.blur="taxes" type="number" id="taxes" class="form-control w-75 d-inline" min="0" max="100">
                    %

                    @error('taxes')
                    <em class="invalid-feedback">
                        {{ $message }}
                    </em>
                    @enderror
                </td>
            </tr>
            <tr>
                <th colspan="4" class="align-middle text-end">
                    Total
                </th>
                <td class="text-center">
                    {{ Number::currency($total, 'KSH.') }}
                    <input type="hidden" name="total_amount" value="{{ $total }}">
                </td>
            </tr>

        </tbody>
    </table>
</div>
<script>
    // Filter dropdown options based on search input
    function filterOptions(index) {
        // Get the search input and dropdown
        const searchInput = document.getElementById(`search-input-${index}`);
        const dropdown = document.getElementById(`invoiceProducts[${index}][product_id]`);
        
        const filter = searchInput.value.toLowerCase().trim(); // Get search value, make lowercase and trim whitespace
        const options = dropdown.options;
    
        // Loop through all options and filter based on search input
        for (let i = 0; i < options.length; i++) {
            const option = options[i];
            const optionText = option.dataset.name ? option.dataset.name.toLowerCase() : option.text.toLowerCase();
    
            // Show or hide the option based on whether the option text includes the filter
            if (optionText.includes(filter)) {
                option.style.display = ''; // Show option
            } else {
                option.style.display = 'none'; // Hide option
            }
        }
            // Show the dropdown if there are matching options, otherwise hide it
        const anyVisibleOption = Array.from(options).some(option => option.style.display !== 'none');
        dropdown.style.display = anyVisibleOption ? '' : 'none';
    }

   // Function to update the search input with the selected option
    function updateSearchInput(index) {
        const dropdown = document.getElementById(`invoiceProducts[${index}}][product_id]`);
        const selectedOption = dropdown.options[dropdown.selectedIndex];
        
        if (selectedOption.value) {
            const searchInput = document.getElementById(`search-input-${index}`);
            const selectedProductName = selectedOption.dataset.name; // Get the name of the selected product
    
            // Set the search input value to the selected product name
            searchInput.value = selectedProductName;  
    
            // Update the Livewire search property for this row (optional: use Livewire if necessary)
            Livewire.emit('updateSearch', index, selectedProductName); 
    
            // Hide the dropdown after selecting a product
            dropdown.style.display = 'none';
        }
    }

    // Function to show the dropdown when focusing on the search input
    function showDropdown(index) {
        const dropdown = document.getElementById(`invoiceProducts[${index}}][product_id]`);
        
        // Show the dropdown if there are options available
        dropdown.style.display = dropdown.options.length > 1 ? '' : 'none';
    }
</script>

