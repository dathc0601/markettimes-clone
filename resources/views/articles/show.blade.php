@extends('layouts.main')

@php
    // Pass article model to layout for SEO meta generation
    $seoModel = $article;
@endphp

@section('content')
<div class="mb-4 sm:mt-4 space-y-4">
    <div class="max-w-7xl mx-auto px-2 sm:px-4 sm:py-6 bg-white sm:rounded-lg sm:shadow">
        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Article Content -->
            <article class="w-full lg:w-2/3">
                @include('partials.breadcrumbs', ['items' => [
                    ['label' => 'Trang chủ', 'url' => route('home')],
                    ['label' => $article->category->name, 'url' => route('category.show', $article->category)]
                ]])

                <!-- Title -->
                <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $article->title }}</h1>

                <!-- Meta -->
                <div class="article-meta border-b pb-4 mb-6">
                    <span class="font-semibold">{{ $article->author->name }}</span>
                    <span>•</span>
                    <span>{{ ($article->published_at ?? $article->created_at)->format('H:i d/m/Y') }}</span>
                    <span>•</span>
                    <span>{{ $article->reading_time }} phút đọc</span>
                    @if($article->view_count > 0)
                        <span>•</span>
                        <span>{{ number_format($article->view_count) }} lượt xem</span>
                    @endif
                </div>

                <!-- Social Share -->
                <div class="flex flex-wrap gap-2 mb-6">
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="social-share-btn social-share-btn--facebook">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        Facebook
                    </a>

                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($article->title) }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="social-share-btn social-share-btn--twitter">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                        Twitter
                    </a>

                    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="social-share-btn social-share-btn--linkedin">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        LinkedIn
                    </a>

                    <a href="https://wa.me/?text={{ urlencode($article->title . ' - ' . url()->current()) }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="social-share-btn social-share-btn--whatsapp">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        WhatsApp
                    </a>

                    <a href="https://telegram.me/share/url?url={{ urlencode(url()->current()) }}&text={{ urlencode($article->title) }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="social-share-btn social-share-btn--telegram">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                        Telegram
                    </a>

                    <a href="https://pinterest.com/pin/create/button/?url={{ urlencode(url()->current()) }}&description={{ urlencode($article->title) }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="social-share-btn social-share-btn--pinterest">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.401.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C23.97 5.39 18.592.026 11.985.026L12.017 0z"/></svg>
                        Pinterest
                    </a>

                    <a href="mailto:?subject={{ urlencode($article->title) }}&body={{ urlencode('Đọc bài viết này: ' . url()->current()) }}"
                       class="social-share-btn social-share-btn--email">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                        Email
                    </a>

                    <button type="button"
                            onclick="copyToClipboard('{{ url()->current() }}')"
                            class="social-share-btn social-share-btn--copy">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                        Copy Link
                    </button>
                </div>

                @push('scripts')
                <script>
                    function copyToClipboard(text) {
                        if (navigator.clipboard && window.isSecureContext) {
                            navigator.clipboard.writeText(text).then(function() {
                                alert('Đã sao chép liên kết!');
                            }, function() {
                                alert('Không thể sao chép liên kết. Vui lòng thử lại.');
                            });
                        } else {
                            // Fallback for older browsers
                            const textArea = document.createElement("textarea");
                            textArea.value = text;
                            textArea.style.position = "fixed";
                            textArea.style.left = "-999999px";
                            document.body.appendChild(textArea);
                            textArea.focus();
                            textArea.select();
                            try {
                                document.execCommand('copy');
                                alert('Đã sao chép liên kết!');
                            } catch (err) {
                                alert('Không thể sao chép liên kết. Vui lòng thử lại.');
                            }
                            document.body.removeChild(textArea);
                        }
                    }
                </script>
                @endpush

                <!-- Ad Before Content -->
                <div class="mb-6">
                    <x-ad-slot position="article_before_content" page="article" class="text-center" />
                </div>

                <!-- Featured Image -->
                @if($article->featured_image)
                    @php
                        $imageData = $article->responsive_image_data;
                    @endphp
                    <picture>
                        @foreach($imageData['sources'] as $source)
                            @if($source['srcset'])
                                <source srcset="{{ $source['srcset'] }}"
                                        @if(isset($source['media'])) media="{{ $source['media'] }}" @endif
                                        @if(isset($source['type'])) type="{{ $source['type'] }}" @endif>
                            @endif
                        @endforeach
                        <img src="{{ $imageData['fallback']['src'] }}"
                             alt="{{ $article->title }}"
                             class="w-full rounded mb-6"
                             loading="lazy">
                    </picture>
                @endif

                <!-- Summary -->
                @if($article->summary)
                    <div class="text-lg font-semibold text-gray-700 mb-6 p-4 bg-beige rounded border-l-4 border-primary">
                        {{ $article->summary }}
                    </div>
                @endif

                <!-- Content -->
                <div class="prose prose-lg max-w-none mb-8">
                    {!! $article->content !!}
                </div>

                <!-- Ad After Content -->
                <div class="mb-8">
                    <x-ad-slot position="article_after_content" page="article" class="text-center" />
                </div>

                <!-- Tags -->
                @if($article->tags->count() > 0)
                    <div class="flex flex-wrap gap-2 mb-8 pt-6 border-t">
                        <span class="text-sm font-semibold text-gray-700">Tags:</span>
                        @foreach($article->tags as $tag)
                            <span class="px-3 py-1 bg-beige-dark text-gray-700 rounded-full text-sm hover:bg-primary hover:text-white transition-colors cursor-pointer">
                    {{ $tag->name }}
                </span>
                        @endforeach
                    </div>
                @endif

                <!-- Related Articles -->
                @if($relatedArticles->count() > 0)
                    <div class="border-t pt-8 mt-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">Bài liên quan</h3>
                        <div class="space-y-0">
                            @foreach($relatedArticles as $related)
                                <x-article-card :article="$related" layout="related" />
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Ad Below Related Articles -->
                <div class="my-8">
                    <x-ad-slot position="article_related_below" page="article" class="text-center" />
                </div>

                <!-- Comments -->
                <div class="border-t mt-8 pt-6">
                    <!-- Comment Form -->
                    <div class="mb-8">
                        <h3 class="text-xl font-bold mb-4">Gửi bình luận</h3>
                        @include('articles.partials.comment-form', ['article' => $article])
                    </div>

                    <!-- Comments Display -->
                    @include('articles.partials.comments', ['article' => $article, 'comments' => $comments, 'sort' => $sort])
                </div>
            </article>

            <!-- Sidebar -->
            <aside class="w-full lg:w-1/3">
                @include('partials.sidebar', ['mostRead' => $mostRead, 'currentPage' => 'article'])
            </aside>
        </div>
    </div>

    <!-- Featured Articles Section -->
    @if(isset($heroArticle) && isset($featuredArticles) && $featuredArticles->count() >= 2)
        <div class="max-w-7xl mx-auto max-sm:px-2">
            <!-- Section Heading -->
            <div class="mb-6">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 border-b-4 border-primary inline-block pb-2">
                    Bài viết nổi bật
                </h2>
            </div>

            <!-- Top Row: Left Column + Right Column -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

                <!-- Left Column -->
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
                        @foreach($featuredArticles->take(2) as $featuredArticle)
                            @php
                                $articleUrl = route('article.show', $featuredArticle->slug . '-' . $featuredArticle->id);
                            @endphp
                            <article class="border-l-4 border-primary pl-3 py-1">
                                <a href="{{ $articleUrl }}" class="group">
                                    <h3 class="text-sm md:text-base font-semibold text-gray-900 group-hover:text-primary transition-colors line-clamp-2">
                                        {{ $featuredArticle->title }}
                                    </h3>
                                </a>
                            </article>
                        @endforeach
                    </div>
                </div>

                <!-- Right Column: 2x2 Grid -->
                <div class="c-head__right">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($featuredArticles->skip(2)->take(4) as $featuredArticle)
                            <article class="overflow-hidden">
                                @php
                                    $articleUrl = route('article.show', $featuredArticle->slug . '-' . $featuredArticle->id);
                                    $imageUrl = $featuredArticle->getImageUrl('medium') ?? asset('images/placeholder.jpg');
                                @endphp
                                <a href="{{ $articleUrl }}" class="block">
                                    <img src="{{ $imageUrl }}"
                                         alt="{{ $featuredArticle->title }}"
                                         class="w-full h-40 object-cover"
                                         loading="lazy">
                                </a>
                                <div class="mt-3">
                                    <a href="{{ $articleUrl }}" class="block group">
                                        <h3 class="text-base md:text-lg font-bold text-gray-900 group-hover:text-primary transition-colors line-clamp-2">
                                            {{ $featuredArticle->title }}
                                        </h3>
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Bottom Row: 2 Horizontal Articles -->
            @if(isset($featuredArticles) && $featuredArticles->count() >= 8)
                <div class="c-head__bottom grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($featuredArticles->skip(6)->take(2) as $featuredArticle)
                        <article class="flex gap-4 py-4 border-b border-gray-200">
                            @php
                                $articleUrl = route('article.show', $featuredArticle->slug . '-' . $featuredArticle->id);
                                $imageUrl = $featuredArticle->getImageUrl('medium') ?? asset('images/placeholder.jpg');
                            @endphp
                            <a href="{{ $articleUrl }}" class="flex-shrink-0">
                                <img src="{{ $imageUrl }}"
                                     alt="{{ $featuredArticle->title }}"
                                     class="w-32 md:w-40 h-24 md:h-28 object-cover"
                                     loading="lazy">
                            </a>
                            <div class="flex-1 min-w-0">
                                <a href="{{ $articleUrl }}" class="block group">
                                    <h3 class="text-base md:text-lg font-bold text-gray-900 group-hover:text-primary transition-colors mb-2 line-clamp-2">
                                        {{ $featuredArticle->title }}
                                    </h3>
                                </a>
                                @if($featuredArticle->summary)
                                    <p class="text-sm text-gray-600 line-clamp-2">
                                        {{ $featuredArticle->summary }}
                                    </p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>

<!-- Schema Markup -->
@include('articles.partials.schema', ['article' => $article])
@endsection
