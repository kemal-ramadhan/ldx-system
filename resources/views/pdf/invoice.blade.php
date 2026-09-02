<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Invoice</title>
<style>
  /* ============================================================
     CATATAN: dompdf TIDAK mendukung flexbox/grid.
     Semua layout di file ini sengaja pakai <table> dan display:block
     supaya render konsisten di dompdf.
     ============================================================ */

  /* Margin halaman didefinisikan DUA kali dengan sengaja:
     - @page untuk versi dompdf yang mendukungnya
     - padding di body sebagai fallback kalau @page diabaikan
     Ini supaya margin tidak "hilang" di versi dompdf tertentu. */
  @page {
    margin: 40px 40px 60px 40px;
  }

  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: Helvetica, Arial, sans-serif;
    color: #1a1a1a;
    font-size: 13px;
    padding: 40px 40px 60px 40px;
  }

  table {
    border-collapse: collapse;
  }

  /* ===== Watermark =====
     - dompdf TIDAK mendukung `transform`, jadi centering pakai
       position:fixed + margin negatif (bukan translate(-50%,-50%)).
     - Sesuaikan WATERMARK_WIDTH/HEIGHT di bawah dengan ukuran asli gambar
       logo Anda (jaga rasio aspek), lalu margin-left/margin-top =
       -(setengah lebar/tinggi) supaya presisi di tengah.
     - Letakkan elemen watermark PALING ATAS di <body> (sebelum konten lain)
       supaya tampil DI BELAKANG konten (dompdf menggambar sesuai urutan HTML). */
  .watermark {
    position: fixed;
    top: 50%;
    left: 50%;
    width: 600px;          /* <-- ganti sesuai lebar logo asli */
    margin-left: -350px;   /* <-- setengah dari width di atas (negatif) */
    margin-top: -350px;    /* <-- setengah dari tinggi gambar (negatif) */
    opacity: 0.09;         /* <-- atur tingkat buram, 0.05–0.15 biasanya pas */
    transform: rotate(-30deg);
  }
  .watermark img {
    width: 100%;
    height: auto;
    display: block;
  }

  /* Logo header sebagai gambar (opsional, kalau mau ganti dari teks) */
  .logo-img {
    width: 140px;   /* <-- sesuaikan lebar logo di header */
    height: auto;
    display: block;
  }
  table.header-table {
    width: 100%;
    margin-bottom: 24px;
  }
  table.header-table td {
    vertical-align: top;
  }
  .invoice-title {
    font-size: 30px;
    font-weight: bold;
    color: #14181f;
  }
  .logo-box {
    background-color: #131a3a;
    color: #ffffff;
    padding: 10px 16px;
    font-weight: bold;
    font-size: 20px;
    text-align: left;
  }
  .logo-box .accent {
    color: #e2183c;
  }
  .logo-box .sub {
    font-size: 8px;
    font-weight: bold;
    margin-left: 4px;
  }

  /* ===== Meta info ===== */
  table.meta-table {
    width: 100%;
    margin-bottom: 24px;
    font-size: 13px;
  }
  table.meta-table td {
    padding: 2px 0;
  }
  .meta-label {
    color: #7c8592;
    width: 110px;
  }
  .meta-value {
    color: #14181f;
    font-weight: bold;
  }

  /* ===== Billed by / to ===== */
  table.billing-table {
    width: 100%;
    margin-bottom: 24px;
  }
  table.billing-table td {
    width: 50%;
    vertical-align: top;
    padding: 0;
  }
  table.billing-table td.spacer {
    width: 16px;
  }
  /* Background & padding ditaruh langsung di <td>, BUKAN di div pembungkus.
     Alasannya: <td> dalam satu <tr> otomatis menyamakan tinggi mengikuti
     yang paling panjang isinya (default table layout, didukung penuh di
     dompdf) — jadi kalau alamat salah satu lebih panjang, box satunya
     ikut memanjang sama tinggi tanpa perlu height:100% yang tidak
     reliable di tabel dompdf. */
  /* Background warna tetap di <td> (supaya tinggi otomatis menyamai
     sel sebelahnya), TAPI padding dipindah ke <div> di dalamnya —
     karena padding langsung di <td> kadang dihitung tidak konsisten
     oleh dompdf. Div dalam ini pakai mekanisme yang sama persis
     dengan .info-box (yang sudah terbukti render benar). */
  td.billing-box {
    background-color: #f4f6f8;
    vertical-align: top;
    padding: 0;
  }
  .billing-box-inner {
    padding: 18px 20px;
  }
  .box-title {
    color: #1f4fd8;
    font-size: 13px;
    font-weight: bold;
    margin-bottom: 8px;
  }
  .company-name {
    font-size: 15px;
    font-weight: bold;
    color: #14181f;
    margin-bottom: 8px;
  }
  .address {
    font-size: 12.5px;
    color: #4b5563;
    line-height: 1.6;
  }

  /* ===== Items table ===== */
  table.items {
    width: 100%;
    margin-bottom: 24px;
    font-size: 13px;
  }
  table.items thead td {
    background-color: #1f3fb6;
    color: #ffffff;
    font-weight: bold;
    padding: 12px 16px;
  }
  table.items thead td.num {
    text-align: right;
  }
  table.items tbody td {
    padding: 14px 16px;
    border-bottom: 1px solid #eef0f3;
    color: #14181f;
  }
  table.items tbody td.desc {
    font-weight: bold;
  }
  table.items tbody td.num {
    text-align: right;
    font-weight: bold;
  }

  /* ===== Bottom section (2 columns) ===== */
  table.bottom-table {
    width: 100%;
    margin-bottom: 30px;
  }
  table.bottom-table td.col-left {
    width: 58%;
    vertical-align: top;
    padding-right: 16px;
  }
  table.bottom-table td.col-right {
    width: 42%;
    vertical-align: top;
  }

  .info-box {
    background-color: #f4f6f8;
    padding: 18px 20px;
    margin-bottom: 16px;
  }
  .info-box p {
    font-size: 12.5px;
    color: #4b5563;
    line-height: 1.6;
  }
  table.bank-table {
    width: 100%;
    font-size: 13px;
  }
  .bank-name {
    color: #7c8592;
  }
  .bank-number {
    font-weight: bold;
    color: #14181f;
    text-align: right;
  }
  .signature-box {
    height: 120px;
  }
  .stamp {
    display: inline-block;
    margin-top: 12px;
    padding: 6px 16px;
    font-size: 13px;
    font-weight: bold;
    letter-spacing: 1px;
    border: 2px solid;
    text-transform: uppercase;
  }
  .stamp-paid {
    color: #1a7d3c;
    border-color: #1a7d3c;
  }
  .stamp-unpaid {
    color: #c0182c;
    border-color: #c0182c;
  }

  /* ===== Totals ===== */
  table.totals-table {
    width: 100%;
    font-size: 13px;
  }
  table.totals-table td {
    padding: 6px 4px;
  }
  .t-label {
    color: #7c8592;
  }
  .t-value {
    font-weight: bold;
    color: #14181f;
    text-align: right;
  }
  .totals-divider td {
    border-top: 1px solid #d9dce1;
    padding: 0;
    height: 1px;
    line-height: 0;
    font-size: 0;
  }
  .grand-label {
    font-size: 17px;
    font-weight: bold;
    color: #14181f;
    padding-top: 10px;
  }
  .grand-value {
    font-size: 17px;
    font-weight: bold;
    color: #14181f;
    text-align: right;
    padding-top: 10px;
  }

  /* ===== Footer =====
     PENTING: position:fixed langsung di <table> tidak stabil di dompdf.
     Fixed position paling stabil diterapkan pada <div> pembungkus.
     left/right/width juga tidak boleh diset bersamaan (redundan/konflik) —
     cukup left:0; right:0; tanpa width. */
  .footer-fixed {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
  }
  table.footer-table {
    width: 100%;
  }
  table.footer-table td {
    height: 42px;
    vertical-align: middle;
  }
  .footer-left {
    background-color: #1f3fb6;
    color: #ffffff;
    font-size: 12px;
    padding-left: 40px;
  }
  .footer-right {
    background-color: #e2183c;
    text-align: right;
    padding-right: 24px;
    width: 220px;
  }
  .mini-logo {
    background-color: #ffffff;
    color: #131a3a;
    font-weight: bold;
    font-size: 12px;
    padding: 3px 8px;
  }
</style>
</head>
<body>

  <!-- ===== WATERMARK =====
       Ganti "GANTI_DENGAN_BASE64_LOGO_ANDA" dengan string base64 logo Anda,
       format: data:image/png;base64,XXXXXXXXXX...
       Diletakkan di paling atas supaya digambar dompdf lebih dulu (di belakang konten). -->
  <div class="watermark">
    <img src="{{ $logoSrc }}" alt="watermark">
  </div>

  <!-- Header -->
  <table class="header-table">
    <tr>
      <td style="width:70%;">
        <div class="invoice-title">Invoice</div>
      </td>
      <td style="width:30%;" align="right">
        <!-- OPSI A: Logo sebagai gambar (disarankan) -->
        <img class="logo-img" src="{{ $logoLdxSrc }}" alt="LDX Data Centre">
      </td>
    </tr>
  </table>

  <!-- Meta -->
  <table class="meta-table">
    <tr>
      <td class="meta-label">Invoice No.</td>
      <td class="meta-value">{{ $invoice->invoice_number }}</td>
    </tr>
    <tr>
      <td class="meta-label">Invoice Date</td>
      <td class="meta-value">{{ \Carbon\Carbon::parse($invoice->issue_date)->locale('id')->translatedFormat('d F Y') }}</td>
    </tr>
    <tr>
      <td class="meta-label">Due Date</td>
      <td class="meta-value">{{ \Carbon\Carbon::parse($invoice->due_date)->locale('id')->translatedFormat('d F Y') }}</td>
    </tr>
  </table>

  <!-- Billed By / To -->
  <table class="billing-table">
    <tr>
      <td class="billing-box">
        <div class="billing-box-inner">
          <div class="box-title">Billed By</div>
          <div class="company-name">PT LDX Data Center</div>
          <div class="address">
            Gedung Cyber 1, Lt. 10 Jl. Kuningan Barat Raya No. 8, RT.1/RW.3,
            Kuningan Barat, Kecamatan Mampang Prapatan, Kota Jakarta Selatan,
            Daerah Khusus Ibukota Jakarta 12710
          </div>
        </div>
      </td>
      <td class="spacer"></td>
      <td class="billing-box">
        <div class="billing-box-inner">
          <div class="box-title">Billed To</div>
          <div class="company-name">{{ $invoice->client->company_name }}</div>
          <div class="address">
            {{ $invoice->client->company_address }}
          </div>
        </div>
      </td>
    </tr>
  </table>

  <!-- Items table -->
  <table class="items">
    <thead>
      <tr>
        <td>Service and description</td>
        <td class="num">Qty</td>
        <td class="num">Rate</td>
        <td class="num">Total</td>
      </tr>
    </thead>
    <tbody>
      @foreach($invoice->items as $item)
        <tr>
          <td>{{ $item->name }}</td>
          <td>{{ $item->quantity }}</td>
          <td>{{ number_format($item->price, 0, ',', '.') }}</td>
          <td>{{ number_format($item->total, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
  </table>

  <!-- Bottom section -->
  <table class="bottom-table">
    <tr>
      <td class="col-left">
        <div class="info-box">
          <div class="box-title">Terms and Conditions</div>
          <p>
            Silakan lakukan pembayaran ke rekening yang tertera pada
            invoice perusahaan. Pembayaran dianggap sah setelah dana
            diterima.
          </p>
        </div>
        <div class="info-box">
          <div class="box-title">Bank Account Details</div>
          <table class="bank-table">
            <tr>
              <td class="bank-name">BCA RONI M</td>
              <td class="bank-number">7435303471</td>
            </tr>
          </table>
        </div>
        <div class="info-box signature-box">
          <div class="box-title">Authorized Signature</div>
              @if(in_array(strtolower($invoice->status), ['paid']))
                <div class="stamp stamp-paid">Lunas</div>
              @else
                  <div class="stamp stamp-unpaid">Belum Lunas</div>
              @endif
        </div>
      </td>
      <td class="col-right">
        <table class="totals-table">
          <tr>
            <td class="t-label">Sub Total</td>
            <td class="t-value">Rp. {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
          </tr>
          <tr>
              <td class="t-label">
                  PPN ({{ $invoice->ppn_percentage }}%)
              </td>
              <td class="t-value">
                  Rp. {{ number_format($invoice->ppn_amount, 0, ',', '.') }}
              </td>
          </tr>

          <tr>
              <td class="t-label">
                  PPH 23 ({{ $invoice->pph23_percentage }}%)
              </td>
              <td class="t-value">
                  Rp. {{ number_format($invoice->pph23_amount, 0, ',', '.') }}
              </td>
          </tr>
          <tr class="totals-divider">
            <td colspan="2"></td>
          </tr>
          <tr>
            <td class="grand-label">Grand Total</td>
            <td class="grand-value">Rp. {{ number_format($invoice->total, 0, ',', '.') }}</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <!-- Footer -->
  <div class="footer-fixed">
    <table class="footer-table">
      <tr>
        <td class="footer-left">Copyright &copy; LDX Data Centre</td>
        <td class="footer-right" width="100%" style="text-align: right; padding-right: 24px;">
          <span
              style="display: inline-block; background: #ffffff; padding: 6px 10px; border-radius: 5px;">
              <img
                  src="{{ $logoSrc }}"
                  alt="LDX Data Centre"
                  width="50"
                  style="display: block;">
          </span>
      </td>
      </tr>
    </table>
  </div>

</body>
</html>