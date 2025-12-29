<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>MyCabang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <style>
        :root{--bca-blue:#0055A4;--bca-blue-dark:#00477f;--bca-contrast:#ffffff}
        .navbar-bca{background-color:var(--bca-blue)!important}
        .navbar-bca .navbar-brand,.navbar-bca .nav-link,.navbar-bca .navbar-text{color:var(--bca-contrast)!important}
        .btn-bca{background-color:var(--bca-blue);border-color:var(--bca-blue);color:var(--bca-contrast)}
        .btn-bca:hover{background-color:var(--bca-blue-dark);border-color:var(--bca-blue-dark);color:var(--bca-contrast)}
        .btn-outline-bca{color:var(--bca-blue);border-color:var(--bca-blue);background-color:transparent}
        .btn-outline-bca:hover{background-color:var(--bca-blue);color:var(--bca-contrast)}
        .text-bca{color:var(--bca-blue)!important}
        .bg-bca{background-color:var(--bca-blue)!important;color:var(--bca-contrast)!important}
    </style>
</head>
<body>
    @include('layout.navbar')

    @yield('content')

    {{-- @include('layout.footer') --}}
</body>
</html>
