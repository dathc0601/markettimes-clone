@if(isset($specialPublications) && $specialPublications->count() > 0)
<div class="bg-white rounded-lg p-6">
    <x-section-heading title="Đặc biệt" />

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        @foreach($specialPublications as $publication)
            @php
                $articleUrl = route('article.show', $publication->slug . '-' . $publication->id);
                $imageUrl = $publication->featured_image ? Storage::url($publication->featured_image) : asset('images/placeholder.jpg');
            @endphp

            <article class="group">
                <a href="{{ $articleUrl }}" class="block mb-3">
                    <img src="{{ $imageUrl }}"
                         alt="{{ $publication->title }}"
                         class="w-full aspect-[3/4] object-cover rounded-lg shadow-md group-hover:shadow-xl transition-shadow">
                </a>

                <a href="{{ $articleUrl }}" class="block">
                    <h4 class="text-lg font-bold text-gray-900 group-hover:text-teal-600 transition-colors line-clamp-2">
                        {{ $publication->title }}
                    </h4>
                </a>

                @if($publication->summary)
                    <p class="text-sm text-gray-600 mt-2 line-clamp-2">
                        {{ $publication->summary }}
                    </p>
                @endif
            </article>
        @endforeach
    </div>
</div>
@endif
