@extends('layouts.main')

@php
    // Pass category model to layout for SEO meta generation
    $seoModel = $category;
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    {{-- Header Banner Ad --}}
    <div class="mb-6">
        <x-ad-slot position="category_header_banner" page="category" class="text-center" />
    </div>

    @php
        // Check if this is a featured category to apply accent color
        $isFeaturedCategory = in_array(strtolower($category->slug), ['tieu-diem', 'highlights', 'featured']);
        $headerClass = $isFeaturedCategory ? 'section-header section-header--accent' : 'section-header';
    @endphp
    <h1 class="{{ $headerClass }}">{{ $category->name }}</h1>

    @if($category->description)
        <p class="text-gray-600 mb-6">{!! nl2br($category->description) !!}</p>
    @endif

    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-gray-600">
            <span class="font-semibold" id="total-articles">{{ $articles->total() + ($heroArticle ? 1 : 0) + $featuredArticles->count() }}</span> bài viết
        </p>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Main Content -->
        <div class="w-full lg:w-2/3">
            <!-- Featured Articles Section -->
            @if(isset($heroArticle) && isset($featuredArticles) && $featuredArticles->count() >= 2)
                <section class="mb-8">
                    <!-- Top Row: Left Column + Right Column -->
                    <div class="flex flex-col lg:flex-row gap-4 mb-6">
                        <!-- Left Column -->
                        <div class="c-head__left space-y-4 w-full lg:w-2/3">
                            <!-- Large Featured Article -->
                            <article class="overflow-hidden">
                                @php
                                    $heroUrl = route('article.show', $heroArticle->slug . '-' . $heroArticle->id);
                                    $heroImageUrl = $heroArticle->getImageUrl('large') ?? asset('images/placeholder.jpg');
                                @endphp
                                <a href="{{ $heroUrl }}" class="block">
                                    <img src="{{ $heroImageUrl }}"
                                         alt="{{ $heroArticle->title }}"
                                         class="w-full h-64 md:h-80 object-cover"
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
                        </div>

                        <!-- Right Column: 2x2 Grid -->
                        <div class="c-head__right w-full lg:w-1/3">
                            <div class="space-y-4">
                                @foreach($featuredArticles->skip(2)->take(2) as $article)
                                    <article class="overflow-hidden">
                                        @php
                                            $articleUrl = route('article.show', $article->slug . '-' . $article->id);
                                            $imageUrl = $article->getImageUrl('medium') ?? asset('images/placeholder.jpg');
                                        @endphp
                                        <a href="{{ $articleUrl }}" class="block">
                                            <img src="{{ $imageUrl }}"
                                                 alt="{{ $article->title }}"
                                                 class="w-full h-40 object-cover"
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
                    </div>
                </section>
            @endif

            {{-- Featured Below Ad --}}
            <div class="mb-6">
                <x-ad-slot position="category_featured_below" page="category" class="text-center" />
            </div>

            @if($articles->count() > 0 || $heroArticle)
                <div class="space-y-4 mb-8" id="articles-container">
                    @foreach($articles as $article)
                        <x-article-card :article="$article" layout="horizontal" />
                    @endforeach
                </div>

                <!-- Load More Button -->
                @if($articles->hasMorePages())
                <div class="mt-8 text-center">
                    <button
                        id="load-more-btn"
                        data-page="{{ $articles->currentPage() + 1 }}"
                        data-category="{{ $category->slug }}"
                        class="block w-full text-center px-8 py-3 bg-primary text-white font-semibold rounded-lg hover:bg-primary-dark transition-colors">
                        Xem thêm
                    </button>
                    <div id="loading-spinner" class="hidden mt-4">
                        <svg class="animate-spin h-8 w-8 mx-auto text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>
                @endif
            @else
                <div class="bg-white rounded-lg p-8 text-center">
                    <p class="text-gray-600">Chưa có bài viết nào trong danh mục này</p>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <aside class="w-full lg:w-1/3">
            @include('partials.sidebar', ['mostRead' => $mostRead, 'currentPage' => 'category'])
        </aside>
    </div>
</div>
@endsection
