<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        // Generate SEO meta tags using SeoHelper
        $siteName = setting('site_name', config('app.name'));
        $seoMeta = isset($seoModel)
            ? \App\Helpers\SeoHelper::generateMeta($seoModel)
            : \App\Helpers\SeoHelper::generateMeta(null, [
                'title' => isset($title) ? $title . ' | ' . $siteName : $siteName . ' - ' . setting('site_tagline', 'Nhịp sống thị trường'),
                'description' => $metaDescription ?? setting('site_description', 'Tin tức tài chính, kinh doanh, chứng khoán, bất động sản và phân tích thị trường.'),
            ]);
    @endphp

    <title>{{ $seoMeta['title'] }}</title>

    <!-- Favicon -->
    @if(setting('site_favicon'))
    <link rel="icon" type="image/x-icon" href="{{ Storage::disk('s3')->url(setting('site_favicon')) }}">
    <link rel="apple-touch-icon" href="{{ Storage::disk('s3')->url(setting('site_favicon')) }}">
    @endif

    <!-- SEO Meta Tags -->
    <meta name="description" content="{{ $seoMeta['description'] }}">
    @if(isset($metaKeywords))
    <meta name="keywords" content="{{ $metaKeywords }}">
    @endif

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="{{ $seoMeta['type'] }}">
    <meta property="og:url" content="{{ $seoMeta['url'] }}">
    <meta property="og:title" content="{{ $seoMeta['title'] }}">
    <meta property="og:description" content="{{ $seoMeta['description'] }}">
    <meta property="og:image" content="{{ $seoMeta['image'] }}">
    <meta property="og:site_name" content="{{ $siteName }}">
    @if(isset($seoMeta['published_time']))
    <meta property="article:published_time" content="{{ $seoMeta['published_time'] }}">
    @endif
    @if(isset($seoMeta['author']))
    <meta property="article:author" content="{{ $seoMeta['author'] }}">
    @endif
    @if(isset($seoMeta['section']))
    <meta property="article:section" content="{{ $seoMeta['section'] }}">
    @endif

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ $seoMeta['url'] }}">
    <meta name="twitter:title" content="{{ $seoMeta['title'] }}">
    <meta name="twitter:description" content="{{ $seoMeta['description'] }}">
    <meta name="twitter:image" content="{{ $seoMeta['image'] }}">

    <!-- RSS Feed Auto-Discovery -->
    <link rel="alternate" type="application/rss+xml" title="{{ $siteName }} RSS Feed" href="{{ route('rss') }}">
    @if(isset($category))
    <link rel="alternate" type="application/rss+xml" title="{{ $category->name }} RSS Feed" href="{{ route('rss.category', $category) }}">
    @endif

    <!-- Google Analytics -->
    @if(setting('google_analytics_id') || env('GOOGLE_ANALYTICS_ID'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ setting('google_analytics_id', env('GOOGLE_ANALYTICS_ID')) }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ setting('google_analytics_id', env('GOOGLE_ANALYTICS_ID')) }}');
    </script>
    @endif

    <!-- Google Tag Manager -->
    @if(setting('google_tag_manager_id'))
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','{{ setting('google_tag_manager_id') }}');</script>
    @endif

    <!-- Facebook Pixel -->
    @if(setting('facebook_pixel_id'))
    <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '{{ setting('facebook_pixel_id') }}');
    fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id={{ setting('facebook_pixel_id') }}&ev=PageView&noscript=1"
    /></noscript>
    @endif

    <!-- Custom Head Scripts -->
    @if(setting('custom_head_scripts'))
    {!! setting('custom_head_scripts') !!}
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|roboto-condensed:400,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body>
    <div class="min-h-screen">
        <!-- Header -->
        @include('partials.header')

        <!-- Navigation -->
        @include('partials.navigation')

        <!-- Main Content -->
        <main>
            @yield('content')
        </main>

        <!-- Footer -->
        @include('partials.footer')
    </div>

    @stack('scripts')

    <!-- Custom Body Scripts -->
    @if(setting('custom_body_scripts'))
    {!! setting('custom_body_scripts') !!}
    @endif

    <!-- Google Tag Manager (noscript) -->
    @if(setting('google_tag_manager_id'))
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ setting('google_tag_manager_id') }}"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    @endif
</body>
</html>
