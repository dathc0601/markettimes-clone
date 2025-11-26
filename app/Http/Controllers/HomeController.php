<?php

namespace App\Http\Controllers;

use App\Filament\Pages\HomepageManagement;
use App\Services\HomepageConfigService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected HomepageConfigService $configService;

    public function __construct(HomepageConfigService $configService)
    {
        $this->configService = $configService;
    }

    public function index(Request $request)
    {
        // Check for preview mode (admin only)
        $isPreview = $this->isValidPreviewRequest($request);

        // Get homepage data from config service
        $data = $this->configService->getHomepageData($isPreview);

        // Handle AJAX request for load more
        if ($request->ajax()) {
            $html = view('home.partials.latest-articles', [
                'articles' => $data['latestArticles']
            ])->render();

            return response()->json([
                'html' => $html,
                'next_page' => $data['latestArticles']->hasMorePages()
                    ? $data['latestArticles']->currentPage() + 1
                    : null,
            ]);
        }

        return view('home.index', [
            'heroArticle' => $data['heroArticle'],
            'featuredArticles' => $data['featuredArticles'],
            'latestArticles' => $data['latestArticles'],
            'mostRead' => $data['sidebarMostRead'],
            'mostReadTeal' => $data['mostReadTeal'],
            'valuationArticles' => $data['valuationArticles'],
            'businessArticles' => $data['businessArticles'],
            'specialPublications' => $data['specialPublications'],
            'categories' => $data['categories'],
            'sectionConfig' => $data['sectionConfig'],
            'sidebarBlocks' => $data['sidebarBlocks'],
            'isPreview' => $isPreview,
        ]);
    }

    /**
     * Validate preview request
     */
    protected function isValidPreviewRequest(Request $request): bool
    {
        if ($request->get('preview') !== 'draft') {
            return false;
        }

        $token = $request->get('token');
        if (!$token) {
            return false;
        }

        // Must be logged in
        if (!auth()->check()) {
            return false;
        }

        $user = auth()->user();

        // Must be admin or editor
        if (!in_array($user->role, ['admin', 'editor'])) {
            return false;
        }

        // Validate the token
        return HomepageManagement::validatePreviewToken($token, $user->id);
    }
}
