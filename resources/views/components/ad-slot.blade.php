@if($ad)
<div class="ad-slot ad-slot--{{ $position }} {{ $class ?? '' }}" data-ad-id="{{ $ad->id }}" data-position="{{ $position }}">
    @if($ad->type === 'image')
        <a href="{{ $ad->click_url }}"
           @if($ad->open_in_new_tab) target="_blank" rel="noopener noreferrer" @endif
           class="ad-slot__link block">
            <img src="{{ $ad->image_url }}"
                 alt="{{ $ad->alt_text ?? 'Quang cao' }}"
                 @if($ad->width) width="{{ $ad->width }}" @endif
                 @if($ad->height) height="{{ $ad->height }}" @endif
                 class="ad-slot__image max-w-full h-auto mx-auto"
                 @if($lazy) loading="lazy" @endif>
        </a>
    @else
        <div class="ad-slot__html">
            {!! $ad->html_content !!}
        </div>
    @endif
</div>
@endif
