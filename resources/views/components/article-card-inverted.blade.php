@props(['article'])

@php
    $articleUrl = route('article.show', $article->slug . '-' . $article->id);
    $imageUrl = $article->featured_image ? Storage::url($article->featured_image) : asset('images/placeholder.jpg');
    $publishedDate = $article->published_at ?? $article->created_at;
@endphp

<article class="flex gap-4 p-4 rounded-lg hover:bg-teal-700 transition-colors">
    <a href="{{ $articleUrl }}" class="flex-shrink-0">
        <img src="{{ $imageUrl }}"
             alt="{{ $article->title }}"
             class="w-48 h-32 object-cover rounded">
    </a>

    <div class="flex-1 min-w-0">
        <span class="inline-block px-2 py-1 text-xs font-semibold rounded bg-white text-teal-600 mb-2">
            {{ $article->category->name }}
        </span>

        <a href="{{ $articleUrl }}" class="block group">
            <h4 class="text-lg font-semibold text-white group-hover:text-yellow-300 transition-colors mb-2 line-clamp-2">
                {{ $article->title }}
            </h4>
        </a>

        @if($article->summary)
            <p class="text-sm text-teal-50 line-clamp-2 mb-2">
                {{ $article->summary }}
            </p>
        @endif

        <div class="flex items-center gap-2 text-xs text-teal-100">
            <span>{{ $publishedDate->diffForHumans() }}</span>
            @if($article->view_count > 0)
                <span>•</span>
                <span>{{ number_format($article->view_count) }} lượt xem</span>
            @endif
        </div>
    </div>
</article>
