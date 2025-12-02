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

        // Authors and Editors see only their own stats
        if (in_array($user?->role, ['editor', 'author'])) {
            return [
                Stat::make('Bài viết của tôi', Article::where('author_id', $user->id)->count())
                    ->description('Tổng số bài viết bạn đã viết')
                    ->descriptionIcon('heroicon-o-newspaper')
                    ->color('success'),

                Stat::make('Chờ duyệt', Article::where('author_id', $user->id)->where('status', 'pending')->count())
                    ->description('Đang chờ phê duyệt')
                    ->descriptionIcon('heroicon-o-clock')
                    ->color('warning'),

                Stat::make('Hiển thị', Article::where('author_id', $user->id)->where('status', 'approved')->where('is_published', true)->count())
                    ->description('Hiện đang xuất bản')
                    ->descriptionIcon('heroicon-o-check-circle')
                    ->color('primary'),

                Stat::make('Tổng lượt xem', Article::where('author_id', $user->id)->sum('view_count'))
                    ->description('Tổng số lượt xem')
                    ->descriptionIcon('heroicon-o-eye')
                    ->color('info'),
            ];
        }

        // Admin sees global stats
        return [
            Stat::make('Tổng bài viết', Article::count())
                ->description('Tất cả bài viết trong hệ thống')
                ->descriptionIcon('heroicon-o-newspaper')
                ->color('success'),

            Stat::make('Chờ duyệt', Article::where('status', 'pending')->count())
                ->description('Bài viết chờ xem xét')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning')
                ->url(route('filament.admin.resources.articles.index', ['tableFilters[status][value]' => 'pending'])),

            Stat::make('Hiển thị', Article::where('is_published', true)->where('status', 'approved')->count())
                ->description('Hiện đang xuất bản')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('primary'),

            Stat::make('Bình luận chờ duyệt', Comment::where('is_approved', false)->count())
                ->description('Chờ kiểm duyệt')
                ->descriptionIcon('heroicon-o-clock')
                ->color('gray')
                ->url(route('filament.admin.resources.comments.index')),
        ];
    }
}
