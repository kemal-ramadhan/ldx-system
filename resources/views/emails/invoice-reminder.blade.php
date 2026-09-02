<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>
        Invoice Reminder
    </title>
</head>

<body>

    <h2>
        Invoice Payment Reminder
    </h2>

    <p>
        Dear {{ $invoice->client->company_name }},
    </p>

    <p>
        This is a reminder that your invoice is approaching its due date.
    </p>

    <table
        cellpadding="8"
        cellspacing="0"
        border="1"
    >
        <tr>
            <td>
                Invoice Number
            </td>

            <td>
                {{ $invoice->invoice_number }}
            </td>
        </tr>

        <tr>
            <td>
                Service
            </td>

            <td>
                {{ $invoice->service->name }}
            </td>
        </tr>

        <tr>
            <td>
                Due Date
            </td>

            <td>
                {{ $invoice->due_date }}
            </td>
        </tr>

        <tr>
            <td>
                Total
            </td>

            <td>
                Rp {{ number_format($invoice->total, 0, ',', '.') }}
            </td>
        </tr>
    </table>

    <p>
        Please complete the payment before the due date.
    </p>

    <p>
        Thank you.
    </p>

</body>

</html>