<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>{{ $invoice->kode_invoice }}</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>@media print {.d-print-none{display:none!important} body{font-size:12px}.container{max-width:100%}}</style></head>
<body><div class="container py-4"><button onclick="window.print()" class="btn btn-primary mb-4 d-print-none">Cetak Invoice</button>@include('invoice._content')</div></body></html>
