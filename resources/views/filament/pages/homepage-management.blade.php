<x-filament-panels::page>
    {{ $this->form }}

    {{-- Preview Modal --}}
    @if($showPreview && $previewUrl)
        <div
            x-data="{ open: true }"
            x-show="open"
            x-on:keydown.escape.window="open = false; $wire.closePreview()"
            class="fixed inset-0 z-50"
            style="display: none;"
        >
            {{-- Backdrop --}}
            <div
                class="fixed inset-0 bg-gray-900/50 dark:bg-gray-900/75"
                x-on:click="open = false; $wire.closePreview()"
            ></div>

            {{-- Modal Content --}}
            <div class="fixed inset-4 flex items-center justify-center">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full h-full max-w-7xl flex flex-col overflow-hidden">
                    {{-- Header --}}
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <x-heroicon-o-eye class="w-6 h-6 text-primary-500" />
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Xem trước trang chủ
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Đây là bản xem trước - chưa được xuất bản
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <a
                                href="{{ $previewUrl }}"
                                target="_blank"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                            >
                                <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                                Mở tab mới
                            </a>
                            <button
                                type="button"
                                x-on:click="open = false; $wire.closePreview()"
                                class="inline-flex items-center justify-center w-10 h-10 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            >
                                <x-heroicon-o-x-mark class="w-6 h-6" />
                            </button>
                        </div>
                    </div>

                    {{-- Preview Banner --}}
                    <div class="bg-amber-50 dark:bg-amber-900/30 border-b border-amber-200 dark:border-amber-700 px-6 py-2">
                        <div class="flex items-center gap-2 text-sm text-amber-700 dark:text-amber-300">
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                            <span>
                                <strong>Chế độ xem trước:</strong>
                                Các thay đổi chưa được áp dụng cho người dùng. Nhấn "Xuất bản" để cập nhật trang chủ thực tế.
                            </span>
                        </div>
                    </div>

                    {{-- Iframe --}}
                    <div class="flex-1 bg-gray-100 dark:bg-gray-900">
                        <iframe
                            src="{{ $previewUrl }}"
                            class="w-full h-full border-0"
                            title="Homepage Preview"
                        ></iframe>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
