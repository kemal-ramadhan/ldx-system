<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>
        Invoice Notification
    </title>
</head>

<body style="
    margin:0;
    padding:0;
    background:#f4f6f9;
    font-family:Arial, Helvetica, sans-serif;
">

    <table width="100%" cellpadding="0" cellspacing="0">

        <tr>

            <td align="center" style="padding:40px 20px;">

                <table width="700" cellpadding="0" cellspacing="0" style="
                    background:#ffffff;
                    border-radius:12px;
                    overflow:hidden;
                    box-shadow:0 2px 10px rgba(0,0,0,0.05);
                ">

                    <!-- HEADER -->
                    <tr>

                        <td style="
                            background:#111827;
                            padding:30px;
                            color:#ffffff;
                        ">

                            <h1 style="
                                margin:0;
                                font-size:28px;
                                font-weight:bold;
                            ">
                                LDX DATA CENTER
                            </h1>

                            <p style="
                                margin-top:8px;
                                font-size:14px;
                                color:#d1d5db;
                            ">
                                Invoice Notification
                            </p>

                        </td>

                    </tr>

                    <!-- CONTENT -->
                    <tr>

                        <td style="padding:40px 30px;">

                            <p style="
                                margin-top:0;
                                font-size:15px;
                                color:#374151;
                            ">
                                Dear
                                <strong>
                                    {{ $invoice->client->company_name }}
                                </strong>,
                            </p>

                            <p style="
                                font-size:15px;
                                line-height:1.7;
                                color:#4b5563;
                            ">
                                Kami menginformasikan bahwa invoice terbaru Anda telah diterbitkan oleh
                                <strong>
                                    LDX Data Center
                                </strong>.
                            </p>

                            <!-- INFO BOX -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="
                                margin-top:25px;
                                border:1px solid #e5e7eb;
                                border-radius:10px;
                                overflow:hidden;
                            ">

                                <tr>

                                    <td style="
                                        background:#f9fafb;
                                        padding:15px 20px;
                                        border-bottom:1px solid #e5e7eb;
                                    ">

                                        <strong>
                                            Invoice Information
                                        </strong>

                                    </td>

                                </tr>

                                <tr>

                                    <td style="padding:20px;">

                                        <table width="100%">

                                            <tr>

                                                <td style="
                                                    padding:8px 0;
                                                    color:#6b7280;
                                                ">
                                                    Invoice Number
                                                </td>

                                                <td align="right" style="
                                                    padding:8px 0;
                                                    font-weight:bold;
                                                    color:#111827;
                                                ">
                                                    {{ $invoice->invoice_number }}
                                                </td>

                                            </tr>

                                            <tr>

                                                <td style="
                                                    padding:8px 0;
                                                    color:#6b7280;
                                                ">
                                                    Issue Date
                                                </td>

                                                <td align="right" style="
                                                    padding:8px 0;
                                                    color:#111827;
                                                ">
                                                    {{ $invoice->issue_date }}
                                                </td>

                                            </tr>

                                            <tr>

                                                <td style="
                                                    padding:8px 0;
                                                    color:#6b7280;
                                                ">
                                                    Due Date
                                                </td>

                                                <td align="right" style="
                                                    padding:8px 0;
                                                    color:#dc2626;
                                                    font-weight:bold;
                                                ">
                                                    {{ $invoice->due_date }}
                                                </td>

                                            </tr>

                                            <tr>

                                                <td style="
                                                    padding:8px 0;
                                                    color:#6b7280;
                                                ">
                                                    Total Amount
                                                </td>

                                                <td align="right" style="
                                                    padding:8px 0;
                                                    font-size:18px;
                                                    font-weight:bold;
                                                    color:#111827;
                                                ">
                                                    Rp {{ number_format($invoice->total, 0, ',', '.') }}
                                                </td>

                                            </tr>

                                        </table>

                                    </td>

                                </tr>

                            </table>

                            <!-- ATTACHMENT -->
                            <div style="
                                margin-top:30px;
                                padding:20px;
                                background:#eff6ff;
                                border:1px solid #bfdbfe;
                                border-radius:10px;
                            ">

                                <p style="
                                    margin:0;
                                    font-size:14px;
                                    color:#1e40af;
                                    line-height:1.7;
                                ">
                                    Invoice lengkap terlampir pada email ini dalam format PDF.
                                    Silakan melakukan pembayaran sebelum tanggal jatuh tempo.
                                </p>

                            </div>

                            <!-- FOOTER MESSAGE -->
                            <p style="
                                margin-top:30px;
                                font-size:14px;
                                line-height:1.7;
                                color:#6b7280;
                            ">
                                Jika terdapat pertanyaan terkait tagihan ini,
                                silakan hubungi tim billing kami.
                            </p>

                            <p style="
                                margin-top:30px;
                                font-size:14px;
                                color:#111827;
                            ">
                                Regards,<br>
                                <strong>
                                    LDX Data Center Billing Team
                                </strong>
                            </p>

                        </td>

                    </tr>

                    <!-- FOOTER -->
                    <tr>

                        <td style="
                            background:#f9fafb;
                            padding:20px 30px;
                            border-top:1px solid #e5e7eb;
                        ">

                            <p style="
                                margin:0;
                                font-size:12px;
                                color:#9ca3af;
                                text-align:center;
                            ">
                                © {{ date('Y') }} LDX Data Center.
                                All rights reserved.
                            </p>

                        </td>

                    </tr>

                </table>

            </td>

        </tr>

    </table>

</body>

</html>