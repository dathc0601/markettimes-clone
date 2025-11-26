{{-- Article Schema.org JSON-LD Markup --}}
<script type="application/ld+json">
{!! \App\Helpers\SeoHelper::generateArticleSchema($article) !!}
</script>

{{-- Breadcrumb Schema.org JSON-LD Markup --}}
<script type="application/ld+json">
{!! \App\Helpers\SeoHelper::generateBreadcrumbSchema([
    ['name' => 'Trang chủ', 'url' => route('home')],
    ['name' => $article->category->name, 'url' => route('category.show', $article->category->slug)],
    ['name' => $article->title, 'url' => route('article.show', $article->slug . '-' . $article->id)]
]) !!}
</script>
