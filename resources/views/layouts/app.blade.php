<!DOCTYPE html>
<html lang="id">

<!-- Header -->

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.materialdesignicons.com/5.4.55/css/materialdesignicons.min.css" rel="stylesheet">
    <!-- APP CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <title>MTSN 6 Garut</title>
</head>

<body>
    <!-- Sidebar -->
    @include('layouts.sidebar')

    <section id="content">
        <!-- NAVBAR -->
        @include('layouts.navbar')

        <!-- Main Content -->
        @yield('content')

        <!-- Footer -->
        @include('layouts.footer')
    </section>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
</body>

</html>
