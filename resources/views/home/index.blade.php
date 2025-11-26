@extends('layouts.main')

@section('content')
    {{-- Preview Mode Banner --}}
    @if($isPreview ?? false)
        <div class="bg-amber-500 text-white text-center py-3 px-4 fixed top-0 left-0 right-0 z-50 shadow-lg">
            <div class="max-w-7xl mx-auto flex items-center justify-center gap-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                <span class="font-semibold">CHE DO XEM TRUOC</span>
                <span class="hidden sm:inline">- Cac thay doi chua duoc xuat ban</span>
            </div>
        </div>
        <div class="h-12"></div> {{-- Spacer for fixed banner --}}
    @endif

    {{-- Hero & Featured Articles Section --}}
    @if(($sectionConfig['hero']['enabled'] ?? true) || ($sectionConfig['featured_grid']['enabled'] ?? true))
        @if(isset($heroArticle) && isset($featuredArticles) && $featuredArticles->count() >= 2)
            <section class="mb-8">
                <div class="max-w-7xl mx-auto p-4">
                    <!-- Top Row: Left Column + Right Column -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

                        <!-- Left Column -->
                        @if($sectionConfig['hero']['enabled'] ?? true)
                            <div class="c-head__left space-y-4">
                                <!-- Large Featured Article -->
                                <article class="overflow-hidden">
                                    @php
                                        $heroUrl = route('article.show', $heroArticle->slug . '-' . $heroArticle->id);
                                        $heroImageUrl = $heroArticle->getImageUrl('large') ?? asset('images/placeholder.jpg');
                                    @endphp
                                    <a href="{{ $heroUrl }}" class="block">
                                        <img src="{{ $heroImageUrl }}"
                                             alt="{{ $heroArticle->title }}"
                                             class="w-full h-64 md:h-80 lg:h-auto lg:aspect-square object-cover"
                                             loading="lazy">
                                    </a>
                                    <div class="mt-4">
                                        <a href="{{ $heroUrl }}" class="block group">
                                            <h2 class="text-xl md:text-2xl font-bold text-gray-900 group-hover:text-primary transition-colors mb-2 line-clamp-2">
                                                {{ $heroArticle->title }}
                                            </h2>
                                        </a>
                                        @if($heroArticle->summary)
                                            <p class="text-sm md:text-base text-gray-600 line-clamp-2">
                                                {{ $heroArticle->summary }}
                                            </p>
                                        @endif
                                    </div>
                                </article>

                                <!-- List of 2 Smaller Article Links -->
                                @if($sectionConfig['featured_grid']['enabled'] ?? true)
                                    <div class="space-y-3">
                                        @foreach($featuredArticles->take(2) as $article)
                                            @php
                                                $articleUrl = route('article.show', $article->slug . '-' . $article->id);
                                            @endphp
                                            <article class="border-l-4 border-primary pl-3 py-1">
                                                <a href="{{ $articleUrl }}" class="group">
                                                    <h3 class="text-sm md:text-base font-semibold text-gray-900 group-hover:text-primary transition-colors line-clamp-2">
                                                        {{ $article->title }}
                                                    </h3>
                                                </a>
                                            </article>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Right Column: 2x2 Grid -->
                        @if($sectionConfig['featured_grid']['enabled'] ?? true)
                            <div class="c-head__right">
                                <div class="grid grid-cols-2 gap-2 md:gap-4">
                                    @foreach($featuredArticles->skip(2)->take(4) as $article)
                                        <article class="overflow-hidden">
                                            @php
                                                $articleUrl = route('article.show', $article->slug . '-' . $article->id);
                                                $imageUrl = $article->getImageUrl('medium') ?? asset('images/placeholder.jpg');
                                            @endphp
                                            <a href="{{ $articleUrl }}" class="block">
                                                <img src="{{ $imageUrl }}"
                                                     alt="{{ $article->title }}"
                                                     class="w-full h-40 lg:h-auto lg:aspect-square object-cover"
                                                     loading="lazy">
                                            </a>
                                            <div class="mt-3">
                                                <a href="{{ $articleUrl }}" class="block group">
                                                    <h3 class="text-base md:text-lg font-bold text-gray-900 group-hover:text-primary transition-colors line-clamp-2">
                                                        {{ $article->title }}
                                                    </h3>
                                                </a>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Bottom Row: 2 Horizontal Articles -->
                    @if(($sectionConfig['featured_grid']['enabled'] ?? true) && isset($featuredArticles) && $featuredArticles->count() >= 8)
                        <div class="c-head__bottom grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($featuredArticles->skip(6)->take(2) as $article)
                                <article class="flex gap-4 py-4 border-b border-gray-200">
                                    @php
                                        $articleUrl = route('article.show', $article->slug . '-' . $article->id);
                                        $imageUrl = $article->getImageUrl('medium') ?? asset('images/placeholder.jpg');
                                    @endphp
                                    <a href="{{ $articleUrl }}" class="flex-shrink-0">
                                        <img src="{{ $imageUrl }}"
                                             alt="{{ $article->title }}"
                                             class="w-32 md:w-40 h-24 md:h-28 object-cover"
                                             loading="lazy">
                                    </a>
                                    <div class="flex-1 min-w-0">
                                        <a href="{{ $articleUrl }}" class="block group">
                                            <h3 class="text-base md:text-lg font-bold text-gray-900 group-hover:text-primary transition-colors mb-2 line-clamp-2">
                                                {{ $article->title }}
                                            </h3>
                                        </a>
                                        @if($article->summary)
                                            <p class="text-sm text-gray-600 line-clamp-2">
                                                {{ $article->summary }}
                                            </p>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        @endif
    @endif

    {{-- Teal Highlighted Section (Most Read) --}}
    @if(($sectionConfig['most_read_teal']['enabled'] ?? true) && isset($mostReadTeal) && $mostReadTeal->count() > 0)
        <section class="bg-teal-600 rounded-lg mb-8">
            <div class="max-w-7xl mx-auto px-6 py-12">
                <!-- Two Column Layout -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <!-- Left Column: Large Featured Article -->
                    @if($mostReadTeal->count() >= 1)
                        @php
                            $featuredMostRead = $mostReadTeal->first();
                            $featuredUrl = route('article.show', $featuredMostRead->slug . '-' . $featuredMostRead->id);
                            $featuredImageUrl = $featuredMostRead->getImageUrl('large') ?? asset('images/placeholder.jpg');
                        @endphp
                        <div class="c-media-news__left">
                            <article>
                                <a href="{{ $featuredUrl }}" class="block mb-4">
                                    <img src="{{ $featuredImageUrl }}"
                                         alt="{{ $featuredMostRead->title }}"
                                         class="w-full h-64 md:h-80 object-cover rounded"
                                         loading="lazy">
                                </a>
                                <div>
                                    <a href="{{ $featuredUrl }}" class="block group">
                                        <h3 class="text-xl md:text-2xl font-bold text-white group-hover:text-teal-100 transition-colors mb-3 line-clamp-2">
                                            {{ $featuredMostRead->title }}
                                        </h3>
                                    </a>
                                    <div class="flex items-center gap-3 text-sm text-teal-100 mb-3">
                                        <a href="{{ route('category.show', $featuredMostRead->category) }}" class="hover:text-white transition-colors">
                                            {{ $featuredMostRead->category->name }}
                                        </a>
                                        <span>•</span>
                                        <span>{{ ($featuredMostRead->published_at ?? $featuredMostRead->created_at)->diffForHumans() }}</span>
                                    </div>
                                    @if($featuredMostRead->summary)
                                        <p class="text-sm md:text-base text-white/90 line-clamp-3">
                                            {{ $featuredMostRead->summary }}
                                        </p>
                                    @endif
                                </div>
                            </article>
                        </div>
                    @endif

                    <!-- Right Column: List of 3 Articles -->
                    @if($mostReadTeal->count() > 1)
                        <div class="c-media-news__right">
                            <div class="space-y-6">
                                @foreach($mostReadTeal->skip(1)->take(3) as $article)
                                    @php
                                        $articleUrl = route('article.show', $article->slug . '-' . $article->id);
                                        $imageUrl = $article->getImageUrl('medium') ?? asset('images/placeholder.jpg');
                                    @endphp
                                    <article class="flex flex-col md:flex-row gap-4">
                                        <!-- Image -->
                                        <a href="{{ $articleUrl }}" class="flex-shrink-0">
                                            <img src="{{ $imageUrl }}"
                                                 alt="{{ $article->title }}"
                                                 class="w-full md:w-40 lg:w-48 h-40 md:h-32 object-cover rounded"
                                                 loading="lazy">
                                        </a>

                                        <!-- Title first on mobile, image first on tablet+ -->
                                        <div class="flex-1 min-w-0">
                                            <a href="{{ $articleUrl }}" class="block group">
                                                <h3 class="text-base md:text-lg font-bold text-white group-hover:text-teal-100 transition-colors mb-2 line-clamp-2">
                                                    {{ $article->title }}
                                                </h3>
                                            </a>
                                            <div class="flex items-center gap-3 text-sm text-teal-100 mb-2">
                                                <a href="{{ route('category.show', $article->category) }}" class="hover:text-white transition-colors">
                                                    {{ $article->category->name }}
                                                </a>
                                                <span>•</span>
                                                <span>{{ ($article->published_at ?? $article->created_at)->diffForHumans() }}</span>
                                            </div>
                                            @if($article->summary)
                                                <p class="text-sm text-white/90 line-clamp-2">
                                                    {{ $article->summary }}
                                                </p>
                                            @endif
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </section>
    @endif

    {{-- Main Content + Sidebar --}}
    <section class="mb-8">
        <div class="max-w-7xl mx-auto p-4">
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Main Content -->
                @if($sectionConfig['latest_articles']['enabled'] ?? true)
                    <div class="w-full lg:w-2/3">
                        <!-- Latest Articles -->
                        @if(isset($latestArticles) && $latestArticles->count() > 0)
                            <section class="mb-8">
                                <div id="latest-articles-container">
                                    @include('home.partials.latest-articles', ['articles' => $latestArticles])
                                </div>

                                <!-- Load More Button -->
                                @if($latestArticles->hasMorePages())
                                    <div class="text-center mt-8">
                                        <button id="load-more-btn-home"
                                                data-page="{{ $latestArticles->currentPage() + 1 }}"
                                                class="block text-center w-full px-6 py-3 bg-primary hover:bg-primary-dark text-white font-semibold rounded-lg transition-colors duration-200">
                                            Xem them
                                        </button>
                                    </div>
                                @else
                                    <div class="text-center mt-8">
                                        <p class="text-gray-400 text-sm">Khong con bai viet nao de hien thi</p>
                                    </div>
                                @endif

                                <!-- Loading Spinner -->
                                <div id="loading-spinner-home" class="hidden text-center mt-8">
                                    <svg class="inline-block animate-spin h-8 w-8 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <p class="text-gray-600 mt-2">Dang tai...</p>
                                </div>
                            </section>
                        @endif
                    </div>
                @endif

                <!-- Sidebar -->
                <aside class="w-full lg:w-1/3">
                    @include('partials.sidebar', [
                        'mostRead' => $mostRead,
                        'valuationArticles' => $valuationArticles,
                        'businessArticles' => $businessArticles,
                        'specialPublications' => $specialPublications,
                        'sectionConfig' => $sectionConfig ?? []
                    ])
                </aside>
            </div>
        </div>
    </section>

    {{-- Category Blocks --}}
    @if(($sectionConfig['category_blocks']['enabled'] ?? false) && isset($categories) && $categories->count() > 0)
        <div class="max-w-7xl mx-auto p-4">
            @foreach($categories as $category)
                @include('partials.category-block', ['category' => $category])
            @endforeach
        </div>
    @endif
@endsection
