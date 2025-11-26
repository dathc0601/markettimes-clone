<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/">
    <channel>
        <title>{{ $title }}</title>
        <link>{{ $link }}</link>
        <description>{{ $description }}</description>
        <language>vi</language>
        <lastBuildDate>{{ now()->toRssString() }}</lastBuildDate>
        <atom:link href="{{ url()->current() }}" rel="self" type="application/rss+xml" />

        @foreach($articles as $article)
        <item>
            <title><![CDATA[{{ $article->title }}]]></title>
            <link>{{ route('article.show', $article->slug . '-' . $article->id) }}</link>
            <description><![CDATA[{{ $article->summary }}]]></description>
            <dc:creator>{{ $article->author->name }}</dc:creator>
            <category>{{ $article->category->name }}</category>
            <pubDate>{{ $article->published_at->toRssString() }}</pubDate>
            <guid isPermaLink="true">{{ route('article.show', $article->slug . '-' . $article->id) }}</guid>
            @if($article->featured_image)
            <enclosure url="{{ $article->getImageUrl('large') }}" type="image/jpeg" />
            @endif
        </item>
        @endforeach
    </channel>
</rss>
