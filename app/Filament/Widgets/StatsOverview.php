<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();

        // Authors see only their own stats
        if ($user?->role === 'author') {
            return [
                Stat::make('My Articles', Article::where('author_id', $user->id)->count())
                    ->description('Total articles you wrote')
                    ->descriptionIcon('heroicon-o-newspaper')
                    ->color('success'),

                Stat::make('Pending', Article::where('author_id', $user->id)->where('status', 'pending')->count())
                    ->description('Awaiting approval')
                    ->descriptionIcon('heroicon-o-clock')
                    ->color('warning'),

                Stat::make('Published', Article::where('author_id', $user->id)->where('status', 'approved')->where('is_published', true)->count())
                    ->description('Currently published')
                    ->descriptionIcon('heroicon-o-check-circle')
                    ->color('primary'),

                Stat::make('Total Views', Article::where('author_id', $user->id)->sum('view_count'))
                    ->description('Total view count')
                    ->descriptionIcon('heroicon-o-eye')
                    ->color('info'),
            ];
        }

        // Admin/Editor see global stats
        return [
            Stat::make('Total Articles', Article::count())
                ->description('All articles in the system')
                ->descriptionIcon('heroicon-o-newspaper')
                ->color('success'),

            Stat::make('Pending Approval', Article::where('status', 'pending')->count())
                ->description('Articles awaiting review')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning')
                ->url(route('filament.admin.resources.articles.index', ['tableFilters[status][value]' => 'pending'])),

            Stat::make('Published Articles', Article::where('is_published', true)->where('status', 'approved')->count())
                ->description('Currently published')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('primary'),

            Stat::make('Pending Comments', Comment::where('is_approved', false)->count())
                ->description('Awaiting moderation')
                ->descriptionIcon('heroicon-o-clock')
                ->color('gray')
                ->url(route('filament.admin.resources.comments.index')),
        ];
    }
}
