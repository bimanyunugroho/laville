<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        @page {
            margin: 5mm;
            page-break-inside: avoid;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 7px;
            margin: 0;
            padding: 0;
        }

        .invoice {
            width: 100%;
        }

        .header, .footer {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding: 2px 0;
        }

        .section {
            margin: 4px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        th, td {
            border-bottom: 1px dotted #000;
            padding: 2px;
            font-size: 7px;
        }

        th {
            background: #eee;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .no-border td {
            border: none;
        }

    </style>
</head>
<body>
    <div class="invoice">
        <div class="header">
            <h3><strong>LAVILLE STORE</strong></h3>
            Invoice #: {{ $invoice->invoice_number }} <br>
            Date: {{ date('d/m/Y', strtotime($invoice->transaction_date)) }}
        </div>

        <div class="section">
            Customer: {{ $invoice->customer ? $invoice->customer->name : 'Guest Customer' }} <br>
            Status: <strong>{{ strtoupper($invoice->status ?? 'PAID') }}</strong>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Harga</th>
                    <th class="text-right">Disc</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $subtotal = 0; $totalDiscount = 0; @endphp
                @foreach($invoice->details as $i => $detail)
                    @php
                        $itemTotal = $detail->quantity * $detail->price;
                        $subtotal += $itemTotal;
                        $totalDiscount += $detail->discount ?? 0;
                    @endphp
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $detail->product->name ?? '-' }}</td>
                        <td class="text-right">{{ $detail->quantity }}</td>
                        <td class="text-right">{{ number_format($detail->price,0,',','.') }}</td>
                        <td class="text-right">{{ number_format($detail->discount,0,',','.') }}</td>
                        <td class="text-right">{{ number_format($itemTotal,0,',','.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="no-border">
            <tr>
                <td class="text-right" colspan="5">Sub Total:</td>
                <td class="text-right">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($invoice->discount)
            <tr>
                <td class="text-right" colspan="5">Diskon:</td>
                <td class="text-right">-Rp {{ number_format($invoice->discount, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($invoice->tax)
            <tr>
                <td class="text-right" colspan="5">PPN ({{ $invoice->tax }}%):</td>
                <td class="text-right">Rp {{ number_format($subtotal * ($invoice->tax / 100), 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr>
                <td class="text-right" colspan="5"><strong>Total:</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</strong></td>
            </tr>
        </table>

        @if(isset($invoice->payments))
        <table class="no-border">
            @php $paid = 0; @endphp
            @foreach($invoice->payments as $payment)
                @php $paid += $payment->amount; @endphp
                <tr>
                    <td colspan="5">Bayar via {{ $payment->payment_method }}</td>
                    <td class="text-right">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="5"><strong>Total Bayar:</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($paid, 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td colspan="5"><strong>Kembalian:</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($paid - $invoice->total_amount, 0, ',', '.') }}</strong></td>
            </tr>
        </table>
        @endif

        <div class="footer">
            Terima Kasih Atas Kunjungan Anda :)
        </div>
    </div>
</body>
</html>
