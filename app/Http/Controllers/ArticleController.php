<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ArticleController extends Controller
{
    public function show(Request $request, $slug)
    {
        // Extract ID from slug-id format
        $parts = explode('-', $slug);
        $id = array_pop($parts);

        // Get sort preference (default: time)
        $sort = $request->input('sort', 'time');

        // Find article (cached for 1 hour)
        $article = Cache::remember("article.{$id}", 3600, function () use ($id) {
            return Article::with(['author', 'category', 'tags'])
                ->published()
                ->findOrFail($id);
        });

        // Increment view count (session-throttled)
        if (!session()->has("viewed_article_{$article->id}")) {
            $article->increment('view_count');
            session()->put("viewed_article_{$article->id}", true);
        }

        // Load comments with sorting and nested replies (not cached - needs to be real-time)
        $commentsQuery = $article->comments()
            ->with(['user', 'replies.user'])
            ->approved()
            ->parent(); // Only top-level comments

        // Apply sorting
        if ($sort === 'likes') {
            $commentsQuery->sortByLikes();
        } else {
            $commentsQuery->latest();
        }

        $comments = $commentsQuery->get();

        // Get related articles (cached for 15 minutes)
        $relatedArticles = Cache::remember("article.{$id}.related", 900, function () use ($article) {
            return Article::with(['category', 'author'])
                ->published()
                ->where('category_id', $article->category_id)
                ->where('id', '!=', $article->id)
                ->latest('published_at')
                ->take(6)
                ->get();
        });

        // Get most read for sidebar (cached for 15 minutes)
        $mostRead = Cache::remember('articles.most_read', 900, function () {
            return Article::with(['category', 'author'])
                ->published()
                ->orderBy('view_count', 'desc')
                ->take(5)
                ->get();
        });

        // Get featured articles for bottom section (cached for 30 minutes)
        $heroArticle = Cache::remember('article.page.hero', 1800, function () use ($article) {
            return Article::with(['category', 'author'])
                ->published()
                ->featured()
                ->where('id', '!=', $article->id) // Exclude current article
                ->latest('published_at')
                ->first();
        });

        $featuredArticles = Cache::remember("article.page.featured.{$article->id}", 1800, function () use ($article, $heroArticle) {
            $query = Article::with(['category', 'author'])
                ->published()
                ->featured()
                ->where('id', '!=', $article->id) // Exclude current article
                ->latest('published_at');

            // Skip hero if it exists
            if ($heroArticle) {
                $query->where('id', '!=', $heroArticle->id);
            }

            return $query->take(10)->get();
        });

        return view('articles.show', compact('article', 'comments', 'relatedArticles', 'mostRead', 'sort', 'heroArticle', 'featuredArticles'));
    }
}
