<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />


    <title>{{ $title ?? config('app.name', 'Nigeria Risk Index') }}</title>

    {{-- Meta tags are usually defined in the component or passed as slots/props --}}
    <meta name="description" content="{{ $description ?? 'Nigeria Risk Index' }}">
    <meta name="keywords" content="{{ $keywords ?? 'risk, index, Nigeria' }}">
    <meta name="author" content="Nigeria Risk Index">

    {{-- Favicon link and Open Graph image --}}
    {{-- Note: Renamed nri-logo.ico for best practice --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('images/nri-logo.ico') }}">
    <meta property="og:image" content="{{ asset('images/nri-logo.ico') }}">
    <meta property="og:image:alt" content="Nigeria Risk Index Logo">

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Valid JSON-LD Schema
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "Nigeria Risk Index",
      "url": "https://nigeriariskindex.com",
      "logo": "https://nigeriariskindex.com/images/nri-logo.png"
    }
    </script> --}}

    <script async src="https://www.googletagmanager.com/gtag/js?id=G-K3NGJH469J"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());
        gtag('config', 'G-K3NGJH469J');
    </script>


</head>

<body class="font-sans bg-[#0A1628] text-white">

    <x-header />


    <main>
        {{ $slot }}
    </main>

    <x-footer />

    <script>
        document.getElementById('menu-toggle').addEventListener('click', function() {
            document.getElementById('menu').classList.toggle('hidden');
        });
    </script>
    <script src="{{ asset('js/map.js') }}"></script>
    <!-- Chart.js + Geo plugin -->
    {{-- <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script> --}}

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>


    <script src="https://cdn.jsdelivr.net/npm/chartjs-chart-geo@4"></script>




</body>

</html>
