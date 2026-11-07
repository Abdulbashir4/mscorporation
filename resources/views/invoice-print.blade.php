<template id="invoice-print-template">
    <section id="invoice-print">
        <div class="invoice-print-content">
        <header class="invoice-print-header">
            <div>
                <h1>{{ $companyProfile['name'] }}</h1>
                @if($companyProfile['address'])<p><b>Address:</b> {{ $companyProfile['address'] }}</p>@endif
                @if($companyProfile['phone'])<p><b>Phone:</b> {{ $companyProfile['phone'] }}</p>@endif
            </div>
            @if($companyProfile['logo'])
                <img src="{{ $companyProfile['logo'] }}" alt="{{ $companyProfile['name'] }}">
            @else
                <span aria-hidden="true"></span>
            @endif
            <div>
                <p><b>Invoice No:</b> <span data-print="number"></span></p>
                <p><b>Date:</b> <span data-print="date"></span></p>
            </div>
        </header>

        <div class="invoice-print-customer">
            <strong>Bill To:</strong>
            <p><b>Name:</b> <span data-print="customer"></span></p>
            <p><b>Address:</b> <span data-print="address"></span></p>
            <p><b>Phone:</b> <span data-print="phone"></span></p>
        </div>

        <table class="invoice-print-items">
            <colgroup><col style="width:3.5%"><col style="width:56.5%"><col style="width:5.5%"><col style="width:6%"><col style="width:11%"><col style="width:7%"><col style="width:10.5%"></colgroup>
            <thead><tr><th>#</th><th>Description</th><th>Qty</th><th>Unit</th><th>Unit<br>Price</th><th>Disc:</th><th>Total</th></tr></thead>
            <tbody data-print="lines"></tbody>
        </table>

        <div class="invoice-print-summary">
            <div class="invoice-print-dues">
                <table>
                    <tbody>
                        <tr><th>Previous Due:</th><td>—</td></tr>
                        <tr><th>Current Due:</th><td data-print="due"></td></tr>
                        <tr><th>Total Due:</th><td data-print="due"></td></tr>
                    </tbody>
                </table>
                <p><b>In Words: <span data-print="words"></span></b></p>
            </div>
            <table>
                <tbody>
                    <tr><td>Sub Total</td><td data-print="subtotal"></td></tr>
                    <tr><td>Total Discount</td><td data-print="discount"></td></tr>
                    <tr><th>Grand Total</th><td data-print="total"></td></tr>
                    <tr><td>Paid</td><td data-print="paid"></td></tr>
                    <tr><th>Due</th><td data-print="due"></td></tr>
                </tbody>
            </table>
        </div>

        </div>
        <footer class="invoice-print-signatures">
            <span>For Customer</span><span>Prepared By</span><span>For Authority</span>
        </footer>
    </section>
</template>
