<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Filament\Resources\ArticleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Authors automatically submit for approval
        if (auth()->user()?->role === 'author') {
            $data['status'] = 'pending';
            $data['is_published'] = false;
        }

        return $data;
    }

    public function getSubheading(): string|Htmlable|null
    {
        return new \Illuminate\Support\HtmlString('
            <style>
                /* Filament Admin Panel - Trix Editor Image Attachments */
                .fi-fo-rich-editor .attachment {
                    margin: 1.5rem 0;
                    width: 100%;
                }

                .fi-fo-rich-editor .attachment figure,
                .fi-fo-rich-editor figure.attachment {
                    margin: 0;
                    padding: 0;
                    width: 100%;
                }

                .fi-fo-rich-editor .attachment img,
                .fi-fo-rich-editor figure.attachment img {
                    max-width: 100%;
                    height: auto;
                    display: block;
                    border-radius: 4px;
                    border: 1px solid #e5e7eb;
                }

                .fi-fo-rich-editor .attachment__caption {
                    font-size: 0.875rem;
                    color: #6b7280;
                    text-align: center;
                    padding: 0.5rem 0;
                    font-style: italic;
                }

                .fi-fo-rich-editor .attachment__caption-editor {
                    width: 100%;
                    padding: 0.5rem;
                    border: 1px dashed #d1d5db;
                    border-radius: 4px;
                    font-size: 0.875rem;
                    color: #6b7280;
                    resize: vertical;
                    min-height: 40px;
                }

                .fi-fo-rich-editor .attachment__toolbar {
                    margin-top: 0.5rem;
                    padding: 0.5rem;
                    background-color: #f9fafb;
                    border-radius: 4px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }

                .fi-fo-rich-editor .attachment__metadata-container {
                    display: flex;
                    gap: 0.5rem;
                    font-size: 0.75rem;
                    color: #9ca3af;
                }

                .fi-fo-rich-editor .attachment__name {
                    font-weight: 500;
                }

                .fi-fo-rich-editor .attachment__size {
                    opacity: 0.8;
                }

                /* Trix Toolbar Buttons */
                .fi-fo-rich-editor .trix-button-row {
                    display: flex;
                    gap: 0.5rem;
                    align-items: center;
                }

                .fi-fo-rich-editor .trix-button-group {
                    display: flex;
                    gap: 0.25rem;
                }

                .fi-fo-rich-editor .trix-button-group--actions {
                    display: flex;
                }

                .fi-fo-rich-editor .trix-button {
                    padding: 0.375rem 0.75rem;
                    font-size: 0.875rem;
                    font-weight: 500;
                    border-radius: 0.375rem;
                    border: 1px solid #d1d5db;
                    background-color: #ffffff;
                    color: #374151;
                    cursor: pointer;
                    transition: all 0.15s ease-in-out;
                    outline: none;
                }

                .fi-fo-rich-editor .trix-button:hover {
                    background-color: #f9fafb;
                    border-color: #9ca3af;
                }

                .fi-fo-rich-editor .trix-button:active {
                    background-color: #f3f4f6;
                    transform: scale(0.98);
                }

                .fi-fo-rich-editor .trix-button--remove {
                    color: #dc2626;
                    border-color: #fecaca;
                    background-color: #fef2f2;
                }

                .fi-fo-rich-editor .trix-button--remove:hover {
                    background-color: #fee2e2;
                    border-color: #fca5a5;
                    color: #b91c1c;
                }

                .fi-fo-rich-editor .trix-button--remove:active {
                    background-color: #fecaca;
                }
            </style>
        ');
    }

    public function getFooter(): ?\Illuminate\Contracts\View\View
    {
        return view('filament.components.image-properties-modal');
    }
}
