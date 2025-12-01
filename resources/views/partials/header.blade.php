<header>
    <!-- Top Bar (Desktop Only) -->
    <div class="hidden md:block bg-black bg-opacity-5 border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 py-2">
            <div class="flex items-center justify-between text-sm text-gray-600">
                <span>Tạp chí đầu tư X-Investor</span>
                <span>{{ now()->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <div id="main-header" class="border-b border-beige-dark">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between lg:justify-center lg:relative">
                <!-- Mobile Menu Button (Left) -->
                <button id="mobile-menu-toggle" class="lg:hidden text-gray-600 hover:text-primary transition-colors">
                    <svg id="hamburger-icon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg id="close-icon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Logo (Centered) -->
                <div class="flex-shrink-0">
                    <a href="{{ route('home') }}" class="block">
                        @if(setting('site_logo'))
                            <img src="{{ Storage::disk('s3')->url(setting('site_logo')) }}" alt="{{ setting('site_name', config('app.name')) }}" class="c-logo">
                        @else
                            <img src="{{ asset('images/logo.svg') }}" alt="{{ setting('site_name', config('app.name')) }}" class="c-logo">
                        @endif
                    </a>
                </div>

                <!-- Search Icon (Right) -->
                <button id="search-toggle" class="lg:absolute lg:right-0 text-gray-600 hover:text-primary transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Search Overlay (Mobile) -->
    <div id="search-overlay" class="fixed inset-0 bg-white z-50 hidden">
        <div class="px-4 py-4">
            <div class="flex items-center justify-between mb-4">
                <button id="search-close" class="text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <a href="{{ route('home') }}">
                    @if(setting('site_logo'))
                        <img src="{{ Storage::disk('s3')->url(setting('site_logo')) }}" alt="{{ setting('site_name', config('app.name')) }}" class="c-logo">
                    @else
                        <img src="{{ asset('images/logo.svg') }}" alt="{{ setting('site_name', config('app.name')) }}" class="c-logo">
                    @endif
                </a>
                <button class="text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </div>
            <div class="mb-6">
                <form action="{{ route('search') }}" method="GET">
                    <input type="text" name="q" placeholder="Nhập từ khóa cần tìm kiếm..." class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                </form>
            </div>
        </div>
    </div>
</header>
