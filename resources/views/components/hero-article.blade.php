@props(['article'])

@php
    $articleUrl = route('article.show', $article->slug . '-' . $article->id);
    $imageUrl = $article->getImageUrl('large') ?? asset('images/placeholder.jpg');
    $publishedDate = $article->published_at ?? $article->created_at;

    // Category badge colors
    $badgeColors = [
        'bg-red-600',
        'bg-blue-600',
        'bg-green-600',
        'bg-yellow-600',
        'bg-purple-600',
        'bg-pink-600',
        'bg-indigo-600',
        'bg-teal-600',
    ];
    $badgeColor = $badgeColors[$article->category_id % count($badgeColors)];
@endphp

<article class="bg-white rounded-lg overflow-hidden hover:shadow-xl transition-shadow mb-8">
    <a href="{{ $articleUrl }}" class="block">
        <img src="{{ $imageUrl }}"
             alt="{{ $article->title }}"
             class="w-full h-56 md:h-72 lg:h-96 object-cover">
    </a>

    <div class="p-6">
        <span class="inline-block px-3 py-1 text-sm font-semibold rounded {{ $badgeColor }} text-white mb-3">
            {{ $article->category->name }}
        </span>

        <a href="{{ $articleUrl }}" class="block group">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 group-hover:text-teal-600 transition-colors mb-4 leading-tight">
                {{ $article->title }}
            </h2>
        </a>

        @if($article->summary)
            <p class="text-lg text-gray-700 mb-4 line-clamp-3">
                {{ $article->summary }}
            </p>
        @endif

        <div class="flex items-center gap-4 text-sm text-gray-600">
            <span class="font-semibold">{{ $article->author->name }}</span>
            <span>•</span>
            <span>{{ $publishedDate->diffForHumans() }}</span>
            @if($article->view_count > 0)
                <span>•</span>
                <span>{{ number_format($article->view_count) }} lượt xem</span>
            @endif
        </div>
    </div>
</article>
