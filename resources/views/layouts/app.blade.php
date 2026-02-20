<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen text-ink-800">
        @include('partials.header')
        <main class="">
            @yield('content')
        </main>
        @include('partials.footer')
    </div>
</body>
</html>
