<div class="lg:sticky top-0">
    @forelse($sidebarBlocks ?? [] as $blockIndex => $block)
        @php
            $blockKey = $block['key'] ?? '';
            $blockTitle = $block['title'] ?? '';
            $blockArticles = $block['articles'] ?? collect();
            $blockConfig = $block['config'] ?? [];
        @endphp

        @if($blockArticles->count() > 0)
            @if($blockKey === 'sidebar_most_read')
                {{-- Most Read Section --}}
                <div class="mb-4 lg:mb-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b-2 border-teal-600">
                        {{ $blockTitle }}
                    </h3>

                    <div class="space-y-4">
                        @foreach($blockArticles as $index => $article)
                            <div class="flex gap-3 items-start">
                                <div class="flex-shrink-0 text-gray-300 rounded-full flex items-center justify-center text-3xl font-black mr-2">
                                    {{ $index + 1 }}
                                </div>

                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('article.show', $article->slug . '-' . $article->id) }}"
                                       class="block">
                                        <h4 class="text-sm font-semibold text-gray-900 hover:text-teal-600 transition-colors line-clamp-2">
                                            {{ $article->title }}
                                        </h4>
                                    </a>

                                    <div class="flex items-center gap-2 mt-1 text-xs text-gray-500">
                                        <span class="inline-block rounded text-xs font-bold text-teal-600">
                                            {{ $article->category->name }}
                                        </span>
                                        <span>{{ $article->published_at ? $article->published_at->diffForHumans() : $article->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif($blockKey === 'sidebar_valuation')
                {{-- Valuation Forum Section --}}
                <div class="mb-4 lg:mb-8">
                    <div class="bg-gray-200 rounded-lg p-8 text-center text-gray-500 aspect-5/2 lg:aspect-square flex justify-center items-center">
                        <div>
                            <p class="text-sm">Quang cao</p>
                        </div>
                    </div>
                </div>

                <div class="mb-4 lg:mb-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b-2 border-teal-600">
                        @php
                            $categorySlug = $blockConfig['source_config']['category_slug'] ?? 'tham-dinh-gia';
                        @endphp
                        <a href="{{ route('category.show', $categorySlug) }}" class="hover:text-teal-600 transition-colors">
                            {{ $blockTitle }}
                        </a>
                    </h3>

                    <div class="space-y-4">
                        @foreach($blockArticles as $article)
                            <x-article-card :article="$article" layout="small" />

                            @if(!$loop->last)
                                <hr class="border-gray-200">
                            @endif
                        @endforeach
                    </div>
                </div>
            @elseif($blockKey === 'sidebar_business')
                {{-- Business Bridge Section --}}
                <div class="mb-4 lg:mb-8">
                    <div class="bg-gray-200 rounded-lg p-8 text-center text-gray-500 aspect-5/2 lg:aspect-square flex justify-center items-center">
                        <div>
                            <p class="text-sm">Quang cao</p>
                        </div>
                    </div>
                </div>

                <div class="mb-4 lg:mb-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b-2 border-teal-600">
                        @php
                            $categorySlug = $blockConfig['source_config']['category_slug'] ?? 'kinh-doanh';
                        @endphp
                        <a href="{{ route('category.show', $categorySlug) }}" class="hover:text-teal-600 transition-colors">
                            {{ $blockTitle }}
                        </a>
                    </h3>

                    <div class="space-y-4">
                        @foreach($blockArticles as $article)
                            <x-article-card :article="$article" layout="small" />

                            @if(!$loop->last)
                                <hr class="border-gray-200">
                            @endif
                        @endforeach
                    </div>
                </div>
            @elseif($blockKey === 'sidebar_special')
                {{-- Special Publications Section --}}
                <div class="mb-4 lg:mb-8">
                    <div class="bg-gray-200 rounded-lg p-8 text-center text-gray-500 aspect-5/2 lg:aspect-square flex justify-center items-center">
                        <div>
                            <p class="text-sm">Quang cao</p>
                        </div>
                    </div>
                </div>

                <div class="mb-4 lg:mb-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b-2 border-teal-600">
                        {{ $blockTitle }}
                    </h3>

                    <div class="space-y-4">
                        @foreach($blockArticles as $article)
                            <x-article-card :article="$article" layout="small" />

                            @if(!$loop->last)
                                <hr class="border-gray-200">
                            @endif
                        @endforeach
                    </div>
                </div>
            @else
                {{-- Generic Block (fallback) --}}
                @if($blockIndex > 0)
                    <div class="mb-4 lg:mb-8">
                        <div class="bg-gray-200 rounded-lg p-8 text-center text-gray-500 aspect-5/2 lg:aspect-square flex justify-center items-center">
                            <div>
                                <p class="text-sm">Quang cao</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="mb-4 lg:mb-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b-2 border-teal-600">
                        {{ $blockTitle }}
                    </h3>

                    <div class="space-y-4">
                        @foreach($blockArticles as $article)
                            <x-article-card :article="$article" layout="small" />

                            @if(!$loop->last)
                                <hr class="border-gray-200">
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    @empty
        {{-- Fallback to legacy variables if sidebarBlocks is empty --}}
        {{-- Most Read Section --}}
        @if(($sectionConfig['sidebar_most_read']['enabled'] ?? true) && isset($mostRead) && $mostRead->count() > 0)
            <div class="mb-4 lg:mb-8">
                <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b-2 border-teal-600">
                    {{ $sectionConfig['sidebar_most_read']['title'] ?? 'Doc nhieu' }}
                </h3>

                <div class="space-y-4">
                    @foreach($mostRead as $index => $article)
                        <div class="flex gap-3 items-start">
                            <div class="flex-shrink-0 text-gray-300 rounded-full flex items-center justify-center text-3xl font-black mr-2">
                                {{ $index + 1 }}
                            </div>

                            <div class="flex-1 min-w-0">
                                <a href="{{ route('article.show', $article->slug . '-' . $article->id) }}"
                                   class="block">
                                    <h4 class="text-sm font-semibold text-gray-900 hover:text-teal-600 transition-colors line-clamp-2">
                                        {{ $article->title }}
                                    </h4>
                                </a>

                                <div class="flex items-center gap-2 mt-1 text-xs text-gray-500">
                                    <span class="inline-block rounded text-xs font-bold text-teal-600">
                                        {{ $article->category->name }}
                                    </span>
                                    <span>{{ $article->published_at ? $article->published_at->diffForHumans() : $article->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Valuation Forum Section --}}
        @if(($sectionConfig['sidebar_valuation']['enabled'] ?? true) && isset($valuationArticles) && $valuationArticles->count() > 0)
            <div class="mb-4 lg:mb-8">
                <div class="bg-gray-200 rounded-lg p-8 text-center text-gray-500 aspect-5/2 lg:aspect-square flex justify-center items-center">
                    <div>
                        <p class="text-sm">Quang cao</p>
                    </div>
                </div>
            </div>

            <div class="mb-4 lg:mb-8">
                <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b-2 border-teal-600">
                    @php
                        $valuationCategorySlug = $sectionConfig['sidebar_valuation']['source_config']['category_slug'] ?? 'tham-dinh-gia';
                    @endphp
                    <a href="{{ route('category.show', $valuationCategorySlug) }}" class="hover:text-teal-600 transition-colors">
                        {{ $sectionConfig['sidebar_valuation']['title'] ?? 'Dien dan Tham dinh gia' }}
                    </a>
                </h3>

                <div class="space-y-4">
                    @foreach($valuationArticles->take($sectionConfig['sidebar_valuation']['count'] ?? 3) as $article)
                        <x-article-card :article="$article" layout="small" />

                        @if(!$loop->last)
                            <hr class="border-gray-200">
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Business Bridge Section --}}
        @if(($sectionConfig['sidebar_business']['enabled'] ?? true) && isset($businessArticles) && $businessArticles->count() > 0)
            <div class="mb-4 lg:mb-8">
                <div class="bg-gray-200 rounded-lg p-8 text-center text-gray-500 aspect-5/2 lg:aspect-square flex justify-center items-center">
                    <div>
                        <p class="text-sm">Quang cao</p>
                    </div>
                </div>
            </div>

            <div class="mb-4 lg:mb-8">
                <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b-2 border-teal-600">
                    @php
                        $businessCategorySlug = $sectionConfig['sidebar_business']['source_config']['category_slug'] ?? 'kinh-doanh';
                    @endphp
                    <a href="{{ route('category.show', $businessCategorySlug) }}" class="hover:text-teal-600 transition-colors">
                        {{ $sectionConfig['sidebar_business']['title'] ?? 'Nhip cau doanh nghiep' }}
                    </a>
                </h3>

                <div class="space-y-4">
                    @foreach($businessArticles->take($sectionConfig['sidebar_business']['count'] ?? 3) as $article)
                        <x-article-card :article="$article" layout="small" />

                        @if(!$loop->last)
                            <hr class="border-gray-200">
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Special Publications Section --}}
        @if(($sectionConfig['sidebar_special']['enabled'] ?? true) && isset($specialPublications) && $specialPublications->count() > 0)
            <div class="mb-4 lg:mb-8">
                <div class="bg-gray-200 rounded-lg p-8 text-center text-gray-500 aspect-5/2 lg:aspect-square flex justify-center items-center">
                    <div>
                        <p class="text-sm">Quang cao</p>
                    </div>
                </div>
            </div>

            <div class="mb-4 lg:mb-8">
                <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b-2 border-teal-600">
                    {{ $sectionConfig['sidebar_special']['title'] ?? 'Dac san' }}
                </h3>

                <div class="space-y-4">
                    @foreach($specialPublications->take($sectionConfig['sidebar_special']['count'] ?? 2) as $article)
                        <x-article-card :article="$article" layout="small" />

                        @if(!$loop->last)
                            <hr class="border-gray-200">
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    @endforelse
</div>
