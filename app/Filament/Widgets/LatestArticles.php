<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestArticles extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $query = Article::query()->latest('created_at');

        // Authors and Editors can only see their own articles
        if (in_array(auth()->user()?->role, ['editor', 'author'])) {
            $query->where('author_id', auth()->id());
        }

        return $table
            ->query($query->limit(5))
            ->columns([
                Tables\Columns\ImageColumn::make('featured_image')
                    ->square()
                    ->label('Image')
                    ->disk('s3')
                    ->getStateUsing(function ($record) {
                        if (!$record->featured_image) {
                            return null;
                        }
                        // Handle both old (string) and new (JSON) formats
                        $paths = is_string($record->featured_image) ? json_decode($record->featured_image, true) : $record->featured_image;
                        if (is_array($paths)) {
                            return $paths['thumbnail'] ?? $paths['original'] ?? null;
                        }
                        return $record->featured_image;
                    }),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(50)
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('category.name')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('author.name')
                    ->badge()
                    ->color('info')
                    ->visible(fn () => !in_array(auth()->user()?->role, ['editor', 'author'])),

                Tables\Columns\IconColumn::make('is_published')
                    ->boolean()
                    ->label('Published'),

                Tables\Columns\TextColumn::make('view_count')
                    ->numeric()
                    ->label('Views'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->since()
                    ->label('Created'),
            ])
            ->heading(in_array(auth()->user()?->role, ['editor', 'author']) ? 'My Latest Articles' : 'Latest Articles')
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Article $record): string => route('filament.admin.resources.articles.edit', $record))
                    ->icon('heroicon-o-eye'),
            ]);
    }
}
