<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <title>
        Invoice PDF
    </title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
            margin: 0;
            padding: 30px;
        }

        .header {
            width: 100%;
            margin-bottom: 25px;
        }

        .header-left {
            width: 60%;
            float: left;
        }

        .header-right {
            width: 40%;
            float: right;
            text-align: right;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .company-info {
            color: #6b7280;
            line-height: 1.6;
        }

        .clearfix::after {
            content: "";
            display: block;
            clear: both;
        }

        .invoice-title {
            font-size: 26px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            background: #fee2e2;
            color: #b91c1c;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }

        .section {
            margin-top: 30px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: #f3f4f6;
            text-align: left;
            padding: 12px;
            border: 1px solid #e5e7eb;
            font-size: 12px;
        }

        table td {
            padding: 12px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
        }

        .text-right {
            text-align: right;
        }

        .summary {
            width: 350px;
            margin-left: auto;
            margin-top: 20px;
        }

        .summary td {
            padding: 10px;
        }

        .summary .grand-total {
            font-size: 15px;
            font-weight: bold;
        }

        .payment-box {
            margin-top: 30px;
            padding: 20px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            background: #f9fafb;
        }

        .payment-box h4 {
            margin-top: 0;
            margin-bottom: 10px;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            color: #9ca3af;
            font-size: 11px;
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <div class="header clearfix">

        <div class="header-left">

            <div class="company-name">
                LDX DATA CENTER
            </div>

            <div class="company-info">
                Jl. Data Center No. 01<br>
                Jakarta Selatan, Indonesia<br>
                billing@ldxdc.com<br>
                +62 812 0000 0000
            </div>

        </div>

        <div class="header-right">

            <div class="invoice-title">
                INVOICE
            </div>

            <div style="margin-bottom:8px;">
                {{ $invoice->invoice_number }}
            </div>

            <span class="badge">
                {{ strtoupper($invoice->status) }}
            </span>

        </div>

    </div>

    <!-- BILL TO -->
    <div class="section">

        <div class="section-title">
            Bill To
        </div>

        <strong>
            {{ $invoice->client->company_name }}
        </strong>

        <br><br>

        {{ $invoice->client->company_address }}

        <br>

        {{ $invoice->client->company_email }}

        <br>

        {{ $invoice->client->company_phone }}

    </div>

    <!-- INVOICE INFO -->
    <div class="section">

        <table>

            <tr>

                <td width="25%">
                    <strong>
                        Invoice Date
                    </strong>
                </td>

                <td width="25%">
                    {{ $invoice->issue_date }}
                </td>

                <td width="25%">
                    <strong>
                        Due Date
                    </strong>
                </td>

                <td width="25%">
                    {{ $invoice->due_date }}
                </td>

            </tr>

            <tr>

                <td>
                    <strong>
                        Service
                    </strong>
                </td>

                <td colspan="3">
                    {{ $invoice->service?->name }}
                </td>

            </tr>

        </table>

    </div>

    <!-- ITEMS -->
    <div class="section">

        <div class="section-title">
            Invoice Items
        </div>

        <table>

            <thead>

                <tr>

                    <th width="40%">
                        Description
                    </th>

                    <th width="15%">
                        Qty
                    </th>

                    <th width="20%">
                        Price
                    </th>

                    <th width="25%">
                        Total
                    </th>

                </tr>

            </thead>

            <tbody>

                @foreach($invoice->items as $item)

                    <tr>

                        <td>
                            <strong>
                                {{ $item->name }}
                            </strong>

                            <br>

                            <span style="color:#6b7280;">
                                {{ $item->description }}
                            </span>
                        </td>

                        <td class="text-right">
                            {{ $item->quantity }}
                        </td>

                        <td class="text-right">
                            Rp {{ number_format($item->price, 0, ',', '.') }}
                        </td>

                        <td class="text-right">
                            Rp {{ number_format($item->total, 0, ',', '.') }}
                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

    </div>

    <!-- SUMMARY -->
    <table class="summary">

        <tr>

            <td>
                Subtotal
            </td>

            <td class="text-right">
                Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}
            </td>

        </tr>

        <tr>

            <td>
                Tax
            </td>

            <td class="text-right">
                Rp {{ number_format($invoice->tax, 0, ',', '.') }}
            </td>

        </tr>

        <tr>

            <td class="grand-total">
                Grand Total
            </td>

            <td class="text-right grand-total">
                Rp {{ number_format($invoice->total, 0, ',', '.') }}
            </td>

        </tr>

    </table>

    <!-- PAYMENT -->
    <div class="payment-box">

        <h4>
            Payment Information
        </h4>

        <p>
            Please transfer payment to:
        </p>

        <strong>
            Bank BCA
        </strong>

        <br>

        1234567890

        <br>

        a/n LDX DATA CENTER

        <br><br>

        Payment is considered valid after funds have been received.

    </div>

    <!-- FOOTER -->
    <div class="footer">

        Thank you for your trust in using LDX Data Center services.

        <br><br>

        Generated at:
        {{ now()->format('d M Y H:i') }}

    </div>

</body>

</html>