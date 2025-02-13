<!DOCTYPE html>
<html lang="en">
<head>
    <title></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">

    <!-- External CSS libraries -->
    <link type="text/css" rel="stylesheet" href="{{ asset('assets/invoice/css/bootstrap.min.css') }}">

    <!-- Google fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Custom Stylesheet -->
    <link type="text/css" rel="stylesheet" href="{{ asset('assets/invoice/css/style.css') }}">
</head>
<body>
    <div class="invoice-16 invoice-content">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <!-- BEGIN: Invoice Details -->
                    <div class="invoice-inner-9" id="invoice_wrapper">
                        <div class="invoice-top">
                            <div class="row">
                                <div class="col-lg-6 col-sm-6">
                                    <div class="logo">
                                        <h1>Nopal Hardware</h1>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-sm-6">
                                    <div class="invoice">
                                        <h1>Invoice # <span>123456</span></h1>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="invoice-info">
                            <div class="row">
                                <div class="col-sm-6 mb-50">
                                    <div class="invoice-number">
                                        <h4 class="inv-title-1">Invoice date:</h4>
                                        <p class="invo-addr-1">
                                            {{ Carbon\Carbon::now()->format('M d, Y') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6 mb-50">
                                    <h4 class="inv-title-1">Customer</h4>
                                    <p class="inv-from-1">{{ $customer->name }}</p>
                                    <p class="inv-from-1">{{ $customer->phone }}</p>
                                    <p class="inv-from-1">{{ $customer->email }}</p>
                                    <p class="inv-from-2">{{ $customer->address }}</p>
                                </div>
                                <div class="col-sm-6 text-end mb-50">
                                    <h4 class="inv-title-1">Store</h4>
                                    <p class="inv-from-1">{{ $customer->name }}</p>
                                    <p class="inv-from-1">0737039809</p>
                                    <p class="inv-from-2">Sagana</p>
                                </div>
                            </div>
                        </div>
                        <div class="order-summary">
                            <div class="table-outer">
                                <table class="default-table invoice-table">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Item</th>
                                            <th class="text-center">Price</th>
                                            <th class="text-center">Quantity</th>
                                            <th class="text-center">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($carts as $item)
                                        <tr>
                                            <td class="text-center">{{ $item->name }}</td>
                                            <td class="text-center">{{ $item->price }}</td>
                                            <td class="text-center">{{ $item->qty }}</td>
                                            <td class="text-center">{{ $item->subtotal }}</td>
                                        </tr>
                                        @endforeach
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Subtotal</strong></td>
                                            <td class="text-center">
                                                <strong>{{ Cart::subtotal() }}</strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Tax</strong></td>
                                            <td class="text-center">
                                                <strong>{{ Cart::tax() }}</strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Total</strong></td>
                                            <td class="text-center">
                                                <strong>{{ Cart::total() }}</strong>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        {{-- <div class="invoice-informeshon-footer">
                            <ul>
                                <li><a href="#">www.website.com</a></li>
                                <li><a href="mailto:sales@hotelempire.com">info@example.com</a></li>
                                <li><a href="tel:+088-01737-133959">+62 123 123 123</a></li>
                            </ul>
                        </div> --}}
                    </div>
                    <!-- END: Invoice Details -->

                    <!-- BEGIN: Invoice Button -->
                    <div class="invoice-btn-section clearfix d-print-none">
                        <a class="btn btn-lg btn-primary" href="{{ route('orders.index') }}">
                            {{ __('Back') }}
                        </a>

                        <button class="btn btn-lg btn-download" type="button" data-bs-toggle="modal" data-bs-target="#modal">
                            {{ __('Pay Now') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('orders.store') }}" method="POST" id="orderForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="customer_id" value="{{ $customer->id }}">

                    <!-- Total Amount Display -->
                    <div class="mb-3">
                        <h5><strong>Total Amount:</strong> <span id="totalAmountDisplay">Ksh.0.00</span></h5>
                    </div>

                    <!-- Payment Type -->
                    <div class="mb-3">
                        <label class="small mb-1" for="payment_type">Payment <span class="text-danger">*</span></label>
                        <select class="form-control @error('payment_type') is-invalid @enderror" id="payment_type" name="payment_type" required>
                            <option selected disabled value="">Select a payment:</option>
                            <option value="HandCash">Cash</option>
                            <option value="Bank">Bank</option>
                            <option value="Mpesa">Mpesa</option>
                            <option value="Credit">Credit</option>
                        </select>
                        @error('payment_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Order Details Table -->
                    <div class="order-summary">
                        <h5>Order Details</h5>
                        <div class="table-outer">
                            <table class="default-table invoice-table">
                                <thead>
                                    <tr>
                                        <th class="text-center">Product</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-center">Original Price</th>
                                        <th class="text-center">Sold At</th>
                                        <th class="text-center">Discount (%)</th>
                                        <th class="text-center">Total Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($carts as $item)
                                    <tr>
                                        <td class="text-center">{{ $item->name }}</td>
                                        <td class="text-center">{{ $item->qty }}</td>
                                        <td class="text-center">{{ $item->price }}</td>
                                        <td class="text-center">
                                            <input type="number" name="sold_at_{{ $item->id }}" class="form-control sold-at" 
                                                data-price="{{ $item->price }}" data-qty="{{ $item->qty }}" step="0.01" 
                                                placeholder="Enter Sold At" required>
                                        </td>
                                        <td class="text-center">
                                            <span class="discount-percent">0%</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="total-price" data-total="{{ $item->price * $item->qty }}">Ksh.0.00</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pay Now -->
                    <div class="mb-3">
                        <label class="small mb-1" for="pay">Pay Now <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-solid @error('pay') is-invalid @enderror" id="pay" name="pay" 
                            value="0.00" readonly />
                        @error('pay')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-lg btn-danger" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-lg btn-download" type="submit">Pay</button>
                </div>
            </form>


        </div>
    </div>
</div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const soldAtInputs = document.querySelectorAll('.sold-at');
            const totalAmountDisplay = document.getElementById('totalAmountDisplay');
            const payField = document.getElementById('pay');

            // Function to calculate total amount
            function calculateTotal() {
                let totalAmount = 0;

                soldAtInputs.forEach((input) => {
                    const price = parseFloat(input.dataset.price);
                    const quantity = parseInt(input.dataset.qty);
                    const soldAt = parseFloat(input.value) || 0;

                    // Calculate discount and update DOM
                    const discountPercent = ((price - soldAt) / price) * 100;
                    input.closest('tr').querySelector('.discount-percent').textContent = `${discountPercent.toFixed(2)}%`;

                    // Calculate total price for the product
                    const totalPrice = soldAt * quantity;
                    input.closest('tr').querySelector('.total-price').textContent = `$${totalPrice.toFixed(2)}`;

                    // Add to total amount
                    totalAmount += totalPrice;
                });

                // Update total amount display and pay field
                totalAmountDisplay.textContent = `$${totalAmount.toFixed(2)}`;
                payField.value = totalAmount.toFixed(2);
            }

            // Attach event listener to "Sold At" inputs
            soldAtInputs.forEach((input) => {
                input.addEventListener('input', calculateTotal);
            });
        });


        document.getElementById('orderForm').addEventListener('submit', function (event) {
            const paymentType = document.getElementById('payment_type').value;

            if (!paymentType) {
                event.preventDefault();
                alert('Please select a payment option before submitting the form.');
                document.getElementById('payment_type').focus();
            }
        });
    </script>

</body>
</html>
