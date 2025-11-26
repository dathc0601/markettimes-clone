@props(['article', 'layout' => 'horizontal'])

@php
    $articleUrl = route('article.show', $article->slug . '-' . $article->id);
    $publishedDate = $article->published_at ?? $article->created_at;

    // Determine badge class - use accent for featured categories, primary otherwise
    $isFeaturedCategory = in_array(strtolower($article->category->slug), ['tieu-diem', 'highlights', 'featured']);
    $badgeClass = $isFeaturedCategory ? 'category-badge--accent' : 'category-badge--primary';

    // Get responsive image data
    $imageData = $article->responsive_image_data;
    $fallbackUrl = $imageData['fallback']['src'];
@endphp

@if($layout === 'featured')
    <!-- Featured Layout (for category page hero) -->
    <article class="rounded-lg overflow-hidden mb-8">
        <a href="{{ $articleUrl }}" class="block">
            <picture>
                @foreach($imageData['sources'] as $source)
                    @if($source['srcset'])
                    <source srcset="{{ $source['srcset'] }}"
                            @if(isset($source['media'])) media="{{ $source['media'] }}" @endif
                            @if(isset($source['type'])) type="{{ $source['type'] }}" @endif>
                    @endif
                @endforeach
                <img src="{{ $fallbackUrl }}"
                     alt="{{ $article->title }}"
                     class="article-thumbnail h-96"
                     loading="lazy">
            </picture>
        </a>

        <div class="p-6">
            <span class="category-badge {{ $badgeClass }} mb-3">
                {{ $article->category->name }}
            </span>

            <a href="{{ $articleUrl }}" class="block group">
                <h2 class="text-3xl font-bold text-gray-900 group-hover:text-primary transition-colors mb-4 leading-tight">
                    {{ $article->title }}
                </h2>
            </a>

            @if($article->summary)
                <p class="text-lg text-gray-600 mb-4 line-clamp-3">
                    {{ $article->summary }}
                </p>
            @endif

            <div class="article-meta">
                @if($article->author)
                    <span class="font-semibold">{{ $article->author->name }}</span>
                @endif
                <span>•</span>
                <span class="b-grid__time">{{ $publishedDate->diffForHumans() }}</span>
                <span>•</span>
                <span>{{ $article->reading_time }} phút đọc</span>
                @if($article->view_count > 0)
                    <span>•</span>
                    <span>{{ number_format($article->view_count) }} lượt xem</span>
                @endif
            </div>
        </div>
    </article>

@elseif($layout === 'grid')
    <!-- Grid Layout (for featured articles) -->
    <article class="rounded-lg overflow-hidden">
        <a href="{{ $articleUrl }}" class="block">
            <picture>
                @foreach($imageData['sources'] as $source)
                    @if($source['srcset'])
                    <source srcset="{{ $source['srcset'] }}"
                            @if(isset($source['media'])) media="{{ $source['media'] }}" @endif
                            @if(isset($source['type'])) type="{{ $source['type'] }}" @endif>
                    @endif
                @endforeach
                <img src="{{ $fallbackUrl }}"
                     alt="{{ $article->title }}"
                     class="article-thumbnail"
                     loading="lazy">
            </picture>
        </a>

        <div class="p-4">
            <span class="category-badge {{ $badgeClass }} mb-2">
                {{ $article->category->name }}
            </span>

            <a href="{{ $articleUrl }}" class="block group">
                <h3 class="text-xl font-bold text-gray-900 group-hover:text-primary transition-colors mb-2 line-clamp-2">
                    {{ $article->title }}
                </h3>
            </a>

            @if($article->summary)
                <p class="text-sm text-gray-600 line-clamp-3 mb-3">
                    {{ $article->summary }}
                </p>
            @endif

            <div class="b-grid__time text-xs">
                @if($article->author)
                    <span>{{ $article->author->name }}</span>
                    <span>•</span>
                @endif
                <span>{{ $publishedDate->diffForHumans() }}</span>
                <span>•</span>
                <span>{{ $article->reading_time }} phút đọc</span>
            </div>
        </div>
    </article>

@elseif($layout === 'small')
    <!-- Small Layout (for sidebar) -->
    <article class="flex gap-3">
        <a href="{{ $articleUrl }}" class="flex-shrink-0">
            <picture>
                @foreach($imageData['sources'] as $source)
                    @if($source['srcset'])
                    <source srcset="{{ $source['srcset'] }}"
                            @if(isset($source['media'])) media="{{ $source['media'] }}" @endif
                            @if(isset($source['type'])) type="{{ $source['type'] }}" @endif>
                    @endif
                @endforeach
                <img src="{{ $fallbackUrl }}"
                     alt="{{ $article->title }}"
                     class="w-20 h-14 object-cover rounded"
                     loading="lazy">
            </picture>
        </a>

        <div class="flex-1 min-w-0">
            <a href="{{ $articleUrl }}" class="block group">
                <h4 class="text-sm font-semibold text-gray-900 group-hover:text-primary transition-colors line-clamp-2 mb-1">
                    {{ $article->title }}
                </h4>
            </a>

            <div class="b-grid__time text-xs">
                {{ $publishedDate->diffForHumans() }}
            </div>
        </div>
    </article>

@elseif($layout === 'related')
    <!-- Related Articles Layout (for article detail page) -->
    <article class="flex flex-wrap relative gap-1 sm:gap-6 py-6 border-b border-gray-200 last:border-b-0">
        <a href="{{ $articleUrl }}" class="flex-shrink-0 w-full sm:w-2/5">
            <picture>
                @foreach($imageData['sources'] as $source)
                    @if($source['srcset'])
                    <source srcset="{{ $source['srcset'] }}"
                            @if(isset($source['media'])) media="{{ $source['media'] }}\" @endif
                            @if(isset($source['type'])) type="{{ $source['type'] }}\" @endif>
                    @endif
                @endforeach
                <img src="{{ $fallbackUrl }}"
                     alt="{{ $article->title }}"
                     class="w-full h-48 md:h-56 object-cover rounded"
                     loading="lazy">
            </picture>
        </a>

        <div class="flex-1 min-w-0">
            <a href="{{ $articleUrl }}" class="block group">
                <h3 class="text-xl md:text-2xl font-bold text-gray-900 group-hover:text-primary transition-colors mb-3 line-clamp-2">
                    {{ $article->title }}
                </h3>
            </a>

            <span class="absolute top-8 left-2 sm:static sm:mb-3 category-badge category-badge--neutral">
                {{ $article->category->name }}
            </span>

            @if($article->summary)
                <p class="text-sm md:text-base text-gray-600 line-clamp-3">
                    {{ $article->summary }}
                </p>
            @endif
        </div>
    </article>
@else
    <!-- Horizontal Layout (default for lists) -->
    <article class="relative flex flex-wrap gap-1 sm:gap-4 py-4 border-b border-gray-200 last:border-b-0">
        <a href="{{ $articleUrl }}" class="flex-shrink-0 w-full sm:w-auto">
            <picture>
                @foreach($imageData['sources'] as $source)
                    @if($source['srcset'])
                    <source srcset="{{ $source['srcset'] }}"
                            @if(isset($source['media'])) media="{{ $source['media'] }}" @endif
                            @if(isset($source['type'])) type="{{ $source['type'] }}" @endif>
                    @endif
                @endforeach
                <img src="{{ $fallbackUrl }}"
                     alt="{{ $article->title }}"
                     class="w-full sm:w-40 md:w-48 h-32 sm:h-28 md:h-32 object-cover rounded"
                     loading="lazy">
            </picture>
        </a>

        <div class="flex-1 min-w-0">
            <span class="absolute top-6 left-2 sm:static sm:mb-2 category-badge {{ $badgeClass }}">
                {{ $article->category->name }}
            </span>

            <a href="{{ $articleUrl }}" class="block group">
                <h3 class="text-lg font-bold text-gray-900 group-hover:text-primary transition-colors mb-2 line-clamp-2">
                    {{ $article->title }}
                </h3>
            </a>

            @if($article->summary)
                <p class="text-sm text-gray-600 line-clamp-2 mb-3">
                    {{ $article->summary }}
                </p>
            @endif

            <div class="b-grid__time text-xs">
                @if($article->author)
                    <span>{{ $article->author->name }}</span>
                    <span>•</span>
                @endif
                <span>{{ $publishedDate->diffForHumans() }}</span>
                <span>•</span>
                <span>{{ $article->reading_time }} phút đọc</span>
                @if($article->view_count > 0)
                    <span>•</span>
                    <span>{{ number_format($article->view_count) }} lượt xem</span>
                @endif
            </div>
        </div>
    </article>
@endif
