<x-filament-panels::page>
    <style>
        /* Grid Layouts */
        .ads-stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }
        @media (max-width: 1024px) {
            .ads-stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 640px) {
            .ads-stats-grid { grid-template-columns: 1fr; }
        }

        .ads-positions-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }
        @media (max-width: 1200px) {
            .ads-positions-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .ads-positions-grid { grid-template-columns: 1fr; }
        }

        /* Stat Cards */
        .ads-stat-card {
            background: white;
            border: 1px solid rgb(229 231 235);
            border-radius: 0.75rem;
            padding: 1.25rem;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .dark .ads-stat-card {
            background: rgb(31 41 55);
            border-color: rgb(55 65 81);
        }
        .ads-stat-card:hover {
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
        }
        .ads-stat-card .stat-accent-bar {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .ads-stat-card:hover .stat-accent-bar {
            opacity: 1;
        }
        .stat-accent-amber { background: linear-gradient(to right, #f59e0b, #f97316); }
        .stat-accent-emerald { background: linear-gradient(to right, #10b981, #14b8a6); }
        .stat-accent-blue { background: linear-gradient(to right, #3b82f6, #6366f1); }
        .stat-accent-gray { background: linear-gradient(to right, #9ca3af, #6b7280); }

        .stat-icon-box {
            width: 3rem;
            height: 3rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .stat-icon-amber { background: rgb(254 243 199); }
        .dark .stat-icon-amber { background: rgba(245, 158, 11, 0.2); }
        .stat-icon-emerald { background: rgb(209 250 229); }
        .dark .stat-icon-emerald { background: rgba(16, 185, 129, 0.2); }
        .stat-icon-blue { background: rgb(219 234 254); }
        .dark .stat-icon-blue { background: rgba(59, 130, 246, 0.2); }
        .stat-icon-gray { background: rgb(243 244 246); }
        .dark .stat-icon-gray { background: rgb(55 65 81); }

        /* Quick Actions */
        .ads-quick-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        .ads-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }
        .ads-btn-primary {
            background: #f59e0b;
            color: white;
        }
        .ads-btn-primary:hover {
            background: #d97706;
        }
        .ads-btn-secondary {
            background: white;
            border: 1px solid rgb(209 213 219);
            color: rgb(55 65 81);
        }
        .dark .ads-btn-secondary {
            background: rgb(31 41 55);
            border-color: rgb(75 85 99);
            color: rgb(209 213 219);
        }
        .ads-btn-secondary:hover {
            background: rgb(249 250 251);
        }
        .dark .ads-btn-secondary:hover {
            background: rgb(55 65 81);
        }

        /* Tab Buttons */
        .ads-tabs {
            display: inline-flex;
            flex-wrap: wrap;
            gap: 0.25rem;
            padding: 0.25rem;
            background: rgb(243 244 246);
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
        }
        .dark .ads-tabs {
            background: rgb(31 41 55);
        }
        .ads-tab {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.5rem;
            border: none;
            background: transparent;
            color: rgb(107 114 128);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .dark .ads-tab {
            color: rgb(156 163 175);
        }
        .ads-tab:hover {
            color: rgb(17 24 39);
        }
        .dark .ads-tab:hover {
            color: white;
        }
        .ads-tab.active {
            background: white;
            color: rgb(17 24 39);
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        }
        .dark .ads-tab.active {
            background: rgb(55 65 81);
            color: white;
        }

        /* Page Section Cards */
        .ads-page-section {
            background: white;
            border: 1px solid rgb(229 231 235);
            border-radius: 1rem;
            overflow: hidden;
        }
        .dark .ads-page-section {
            background: rgb(31 41 55);
            border-color: rgb(55 65 81);
        }
        .ads-page-header {
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .ads-page-header.amber { background: linear-gradient(to right, #f59e0b, #f97316); }
        .ads-page-header.blue { background: linear-gradient(to right, #3b82f6, #6366f1); }
        .ads-page-header.purple { background: linear-gradient(to right, #8b5cf6, #ec4899); }
        .ads-page-header-icon {
            width: 2.5rem;
            height: 2.5rem;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .ads-page-header-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: white;
        }
        .ads-page-header-badge {
            padding: 0.25rem 0.75rem;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 9999px;
        }
        .ads-page-body {
            padding: 1rem;
        }

        /* Position Cards */
        .ads-position-card {
            padding: 1rem;
            border-radius: 0.75rem;
            border: 1px solid rgb(229 231 235);
            margin-bottom: 0.75rem;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .dark .ads-position-card {
            border-color: rgb(75 85 99);
        }
        .ads-position-card:last-child {
            margin-bottom: 0;
        }
        .ads-position-card.has-ads {
            background: rgb(236 253 245);
            border-color: rgb(167 243 208);
        }
        .dark .ads-position-card.has-ads {
            background: rgba(16, 185, 129, 0.1);
            border-color: rgba(16, 185, 129, 0.3);
        }
        .ads-position-card.empty {
            background: rgb(249 250 251);
        }
        .dark .ads-position-card.empty {
            background: rgba(107, 114, 128, 0.1);
        }
        .ads-position-card:hover {
            border-color: rgb(156 163 175);
        }
        .ads-position-card.has-ads:hover {
            border-color: rgb(52 211 153);
        }
        .dark .ads-position-card.has-ads:hover {
            border-color: rgb(16 185 129);
        }
        .ads-position-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        .ads-position-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: rgb(17 24 39);
        }
        .dark .ads-position-name {
            color: white;
        }
        .ads-position-size {
            padding: 0.125rem 0.5rem;
            background: rgb(229 231 235);
            color: rgb(75 85 99);
            font-size: 0.6875rem;
            font-family: ui-monospace, monospace;
            border-radius: 0.25rem;
        }
        .dark .ads-position-size {
            background: rgb(75 85 99);
            color: rgb(209 213 219);
        }
        .ads-position-desc {
            font-size: 0.75rem;
            color: rgb(107 114 128);
            margin-bottom: 0.75rem;
            line-height: 1.4;
        }
        .dark .ads-position-desc {
            color: rgb(156 163 175);
        }
        .ads-position-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        /* Badges */
        .ads-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            font-size: 0.6875rem;
            font-weight: 500;
            border-radius: 0.375rem;
        }
        .ads-badge-amber {
            background: rgb(254 243 199);
            color: rgb(180 83 9);
        }
        .dark .ads-badge-amber {
            background: rgba(245, 158, 11, 0.2);
            color: rgb(251 191 36);
        }
        .ads-badge-emerald {
            background: rgb(209 250 229);
            color: rgb(4 120 87);
        }
        .dark .ads-badge-emerald {
            background: rgba(16, 185, 129, 0.2);
            color: rgb(52 211 153);
        }
        .ads-badge-gray {
            background: rgb(243 244 246);
            color: rgb(107 114 128);
        }
        .dark .ads-badge-gray {
            background: rgb(75 85 99);
            color: rgb(156 163 175);
        }
        .ads-badge-pulse {
            width: 6px;
            height: 6px;
            background: rgb(16 185 129);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Recent Ads List */
        .ads-list-panel {
            background: white;
            border: 1px solid rgb(229 231 235);
            border-radius: 1rem;
            overflow: hidden;
        }
        .dark .ads-list-panel {
            background: rgb(31 41 55);
            border-color: rgb(55 65 81);
        }
        .ads-list-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgb(229 231 235);
        }
        .dark .ads-list-header {
            border-color: rgb(55 65 81);
        }
        .ads-list-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: rgb(17 24 39);
        }
        .dark .ads-list-title {
            color: white;
        }
        .ads-list-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-bottom: 1px solid rgb(243 244 246);
            transition: background 0.2s ease;
        }
        .dark .ads-list-item {
            border-color: rgb(55 65 81);
        }
        .ads-list-item:last-child {
            border-bottom: none;
        }
        .ads-list-item:hover {
            background: rgb(249 250 251);
        }
        .dark .ads-list-item:hover {
            background: rgba(55, 65, 81, 0.5);
        }
        .ads-list-preview {
            width: 5rem;
            height: 3.5rem;
            background: rgb(243 244 246);
            border: 1px solid rgb(229 231 235);
            border-radius: 0.5rem;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .dark .ads-list-preview {
            background: rgb(55 65 81);
            border-color: rgb(75 85 99);
        }
        .ads-list-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .ads-list-info {
            flex: 1;
            min-width: 0;
        }
        .ads-list-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: rgb(17 24 39);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .dark .ads-list-name {
            color: white;
        }
        .ads-list-meta {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-top: 0.25rem;
            font-size: 0.75rem;
            color: rgb(107 114 128);
        }
        .dark .ads-list-meta {
            color: rgb(156 163 175);
        }
        .ads-list-meta svg {
            width: 0.875rem;
            height: 0.875rem;
        }
        .ads-list-status {
            flex-shrink: 0;
        }
        .ads-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem 0.625rem;
            font-size: 0.75rem;
            font-weight: 500;
            border-radius: 9999px;
        }
        .ads-status-active {
            background: rgb(209 250 229);
            color: rgb(4 120 87);
        }
        .dark .ads-status-active {
            background: rgba(16, 185, 129, 0.2);
            color: rgb(52 211 153);
        }
        .ads-status-inactive {
            background: rgb(243 244 246);
            color: rgb(107 114 128);
        }
        .dark .ads-status-inactive {
            background: rgb(55 65 81);
            color: rgb(156 163 175);
        }
        .ads-list-actions {
            display: flex;
            gap: 0.25rem;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        .ads-list-item:hover .ads-list-actions {
            opacity: 1;
        }
        .ads-action-btn {
            width: 2.25rem;
            height: 2.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            border: none;
            background: transparent;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .ads-action-btn svg {
            width: 1.125rem;
            height: 1.125rem;
        }
        .ads-action-toggle { color: rgb(245 158 11); }
        .ads-action-toggle:hover { background: rgb(254 243 199); }
        .dark .ads-action-toggle:hover { background: rgba(245, 158, 11, 0.2); }
        .ads-action-play { color: rgb(16 185 129); }
        .ads-action-play:hover { background: rgb(209 250 229); }
        .dark .ads-action-play:hover { background: rgba(16, 185, 129, 0.2); }
        .ads-action-edit { color: rgb(59 130 246); }
        .ads-action-edit:hover { background: rgb(219 234 254); }
        .dark .ads-action-edit:hover { background: rgba(59, 130, 246, 0.2); }
        .ads-action-delete { color: rgb(239 68 68); }
        .ads-action-delete:hover { background: rgb(254 226 226); }
        .dark .ads-action-delete:hover { background: rgba(239, 68, 68, 0.2); }

        /* Empty State */
        .ads-empty-state {
            padding: 3rem;
            text-align: center;
        }
        .ads-empty-icon {
            width: 4rem;
            height: 4rem;
            background: rgb(243 244 246);
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
        .dark .ads-empty-icon {
            background: rgb(55 65 81);
        }
        .ads-empty-icon svg {
            width: 2rem;
            height: 2rem;
            color: rgb(156 163 175);
        }
        .dark .ads-empty-icon svg {
            color: rgb(107 114 128);
        }
        .ads-empty-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: rgb(17 24 39);
            margin-bottom: 0.5rem;
        }
        .dark .ads-empty-title {
            color: white;
        }
        .ads-empty-text {
            font-size: 0.875rem;
            color: rgb(107 114 128);
            margin-bottom: 1.5rem;
        }
        .dark .ads-empty-text {
            color: rgb(156 163 175);
        }

        /* Utility */
        .flex-center { display: flex; align-items: center; }
        .flex-between { display: flex; align-items: center; justify-content: space-between; }
        .gap-3 { gap: 0.75rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-8 { margin-bottom: 2rem; }
        .ml-5 { margin-left: 1.25rem; }
        .text-3xl { font-size: 1.875rem; font-weight: 700; }
        .text-sm { font-size: 0.875rem; }
        .text-xs { font-size: 0.75rem; }
        .text-gray-900 { color: rgb(17 24 39); }
        .dark .text-gray-900 { color: white; }
        .text-gray-500 { color: rgb(107 114 128); }
        .dark .text-gray-500 { color: rgb(156 163 175); }
        .text-emerald-600 { color: rgb(5 150 105); }
        .dark .text-emerald-600 { color: rgb(52 211 153); }
        .text-amber-600 { color: rgb(217 119 6); }
        .dark .text-amber-600 { color: rgb(251 191 36); }
        .uppercase { text-transform: uppercase; }
        .tracking-wider { letter-spacing: 0.05em; }
        .font-mono { font-family: ui-monospace, monospace; }
    </style>

    <div class="ads-dashboard" x-data="{
        selectedPage: 'all',
        positions: @js($this->positions),
        adData: @js($this->adData),
        getAdsForPosition(position) {
            return this.adData[position] || [];
        },
        getActiveCount(position) {
            const ads = this.adData[position] || [];
            return ads.filter(ad => ad.is_active).length;
        },
        hasAds(position) {
            return (this.adData[position] || []).length > 0;
        }
    }">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex-center gap-3 mb-2">
                <div style="width: 6px; height: 2rem; background: #f59e0b; border-radius: 9999px;"></div>
                <h1 class="text-3xl text-gray-900">Các vị trí quảng cáo</h1>
            </div>
            <p class="text-sm text-gray-500 ml-5">
                Tổng quan và quản lý vị trí quảng cáo trên toàn trang web
            </p>
        </div>

        <!-- Stats Grid -->
        <div class="ads-stats-grid mb-8">
            <div class="ads-stat-card">
                <div class="stat-accent-bar stat-accent-amber"></div>
                <div class="flex-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Tổng quảng cáo</p>
                        <p class="text-3xl text-gray-900">{{ $this->adsCount }}</p>
                    </div>
                    <div class="stat-icon-box stat-icon-amber">
                        <svg style="width: 1.5rem; height: 1.5rem;" class="text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="ads-stat-card">
                <div class="stat-accent-bar stat-accent-emerald"></div>
                <div class="flex-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Đang hoạt động</p>
                        <p class="text-3xl text-emerald-600">{{ $this->activeAdsCount }}</p>
                    </div>
                    <div class="stat-icon-box stat-icon-emerald">
                        <svg style="width: 1.5rem; height: 1.5rem;" class="text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="ads-stat-card">
                <div class="stat-accent-bar stat-accent-blue"></div>
                <div class="flex-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Vị trí khả dụng</p>
                        <p class="text-3xl text-gray-900">17</p>
                    </div>
                    <div class="stat-icon-box stat-icon-blue">
                        <svg style="width: 1.5rem; height: 1.5rem; color: #3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="ads-stat-card">
                <div class="stat-accent-bar stat-accent-gray"></div>
                <div class="flex-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Vị trí trống</p>
                        <p class="text-3xl text-gray-900">{{ 17 - count($this->adData) }}</p>
                    </div>
                    <div class="stat-icon-box stat-icon-gray">
                        <svg style="width: 1.5rem; height: 1.5rem; color: #6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="ads-quick-actions">
            <a href="{{ route('filament.admin.resources.ads.create') }}" class="ads-btn ads-btn-primary">
                <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Thêm quảng cáo mới
            </a>
            <a href="{{ route('filament.admin.resources.ads.index') }}" class="ads-btn ads-btn-secondary">
                <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                Xem danh sách
            </a>
            <button wire:click="clearAllCache" class="ads-btn ads-btn-secondary">
                <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Xóa cache
            </button>
        </div>

        <!-- Page Filter Tabs -->
        <div class="ads-tabs">
            <button @click="selectedPage = 'all'" class="ads-tab" :class="{ 'active': selectedPage === 'all' }">
                Tất cả vị trí
            </button>
            <button @click="selectedPage = 'homepage'" class="ads-tab" :class="{ 'active': selectedPage === 'homepage' }">
                Trang chủ
            </button>
            <button @click="selectedPage = 'article'" class="ads-tab" :class="{ 'active': selectedPage === 'article' }">
                Bài viết
            </button>
            <button @click="selectedPage = 'category'" class="ads-tab" :class="{ 'active': selectedPage === 'category' }">
                Danh mục
            </button>
        </div>

        <!-- Position Cards Grid -->
        <div class="ads-positions-grid mb-8">
            <!-- Homepage Section -->
            <div class="ads-page-section" x-show="selectedPage === 'all' || selectedPage === 'homepage'" x-transition>
                <div class="ads-page-header amber">
                    <div class="flex-center gap-3">
                        <div class="ads-page-header-icon">
                            <svg style="width: 1.25rem; height: 1.25rem; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                        </div>
                        <span class="ads-page-header-title">Trang chủ</span>
                    </div>
                    <span class="ads-page-header-badge">7 vị trí</span>
                </div>
                <div class="ads-page-body">
                    @foreach($this->positions['homepage'] as $key => $info)
                        <div class="ads-position-card" :class="hasAds('{{ $key }}') ? 'has-ads' : 'empty'">
                            <div class="ads-position-header">
                                <span class="ads-position-name">{{ $info['label'] }}</span>
                                <span class="ads-position-size">{{ $info['size'] }}</span>
                            </div>
                            <div class="ads-position-desc">{{ $info['description'] }}</div>
                            <div class="ads-position-badges">
                                <template x-if="hasAds('{{ $key }}')">
                                    <span class="ads-badge ads-badge-amber" x-text="getAdsForPosition('{{ $key }}').length + ' quảng cáo'"></span>
                                </template>
                                <template x-if="getActiveCount('{{ $key }}') > 0">
                                    <span class="ads-badge ads-badge-emerald">
                                        <span class="ads-badge-pulse"></span>
                                        <span x-text="getActiveCount('{{ $key }}') + ' hoạt động'"></span>
                                    </span>
                                </template>
                                <template x-if="!hasAds('{{ $key }}')">
                                    <span class="ads-badge ads-badge-gray">Trống</span>
                                </template>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Article Section -->
            <div class="ads-page-section" x-show="selectedPage === 'all' || selectedPage === 'article'" x-transition>
                <div class="ads-page-header blue">
                    <div class="flex-center gap-3">
                        <div class="ads-page-header-icon">
                            <svg style="width: 1.25rem; height: 1.25rem; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <span class="ads-page-header-title">Trang bài viết</span>
                    </div>
                    <span class="ads-page-header-badge">6 vị trí</span>
                </div>
                <div class="ads-page-body">
                    @foreach($this->positions['article'] as $key => $info)
                        <div class="ads-position-card" :class="hasAds('{{ $key }}') ? 'has-ads' : 'empty'">
                            <div class="ads-position-header">
                                <span class="ads-position-name">{{ $info['label'] }}</span>
                                <span class="ads-position-size">{{ $info['size'] }}</span>
                            </div>
                            <div class="ads-position-desc">{{ $info['description'] }}</div>
                            <div class="ads-position-badges">
                                <template x-if="hasAds('{{ $key }}')">
                                    <span class="ads-badge ads-badge-amber" x-text="getAdsForPosition('{{ $key }}').length + ' quảng cáo'"></span>
                                </template>
                                <template x-if="getActiveCount('{{ $key }}') > 0">
                                    <span class="ads-badge ads-badge-emerald">
                                        <span class="ads-badge-pulse"></span>
                                        <span x-text="getActiveCount('{{ $key }}') + ' hoạt động'"></span>
                                    </span>
                                </template>
                                <template x-if="!hasAds('{{ $key }}')">
                                    <span class="ads-badge ads-badge-gray">Trống</span>
                                </template>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Category Section -->
            <div class="ads-page-section" x-show="selectedPage === 'all' || selectedPage === 'category'" x-transition>
                <div class="ads-page-header purple">
                    <div class="flex-center gap-3">
                        <div class="ads-page-header-icon">
                            <svg style="width: 1.25rem; height: 1.25rem; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <span class="ads-page-header-title">Trang danh mục</span>
                    </div>
                    <span class="ads-page-header-badge">4 vị trí</span>
                </div>
                <div class="ads-page-body">
                    @foreach($this->positions['category'] as $key => $info)
                        <div class="ads-position-card" :class="hasAds('{{ $key }}') ? 'has-ads' : 'empty'">
                            <div class="ads-position-header">
                                <span class="ads-position-name">{{ $info['label'] }}</span>
                                <span class="ads-position-size">{{ $info['size'] }}</span>
                            </div>
                            <div class="ads-position-desc">{{ $info['description'] }}</div>
                            <div class="ads-position-badges">
                                <template x-if="hasAds('{{ $key }}')">
                                    <span class="ads-badge ads-badge-amber" x-text="getAdsForPosition('{{ $key }}').length + ' quảng cáo'"></span>
                                </template>
                                <template x-if="getActiveCount('{{ $key }}') > 0">
                                    <span class="ads-badge ads-badge-emerald">
                                        <span class="ads-badge-pulse"></span>
                                        <span x-text="getActiveCount('{{ $key }}') + ' hoạt động'"></span>
                                    </span>
                                </template>
                                <template x-if="!hasAds('{{ $key }}')">
                                    <span class="ads-badge ads-badge-gray">Trống</span>
                                </template>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Recent Ads List -->
        @if(count($this->adData) > 0)
            <div class="ads-list-panel">
                <div class="ads-list-header">
                    <h2 class="ads-list-title">Quảng cáo gần đây</h2>
                </div>
                @foreach(\App\Models\Ad::orderByDesc('updated_at')->limit(10)->get() as $ad)
                    <div class="ads-list-item">
                        <div class="ads-list-preview">
                            @if($ad->type === 'image' && $ad->image_url)
                                <img src="{{ $ad->image_url }}" alt="{{ $ad->name }}">
                            @else
                                <span class="text-xs font-mono" style="color: #9ca3af;">HTML/JS</span>
                            @endif
                        </div>
                        <div class="ads-list-info">
                            <div class="ads-list-name">{{ $ad->name }}</div>
                            <div class="ads-list-meta">
                                <span class="flex-center" style="gap: 0.25rem;">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    </svg>
                                    {{ $ad->position_label }}
                                </span>
                                <span class="flex-center" style="gap: 0.25rem;">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                    {{ $ad->page_label }}
                                </span>
                                <span class="font-mono">Ưu tiên: {{ $ad->priority }}</span>
                            </div>
                        </div>
                        <div class="ads-list-status">
                            @if($ad->is_active)
                                <span class="ads-status-badge ads-status-active">
                                    <span class="ads-badge-pulse"></span>
                                    Hoạt động
                                </span>
                            @else
                                <span class="ads-status-badge ads-status-inactive">
                                    <span style="width: 6px; height: 6px; background: #9ca3af; border-radius: 50%;"></span>
                                    Tắt
                                </span>
                            @endif
                        </div>
                        <div class="ads-list-actions">
                            <button wire:click="toggleAd({{ $ad->id }})" class="ads-action-btn {{ $ad->is_active ? 'ads-action-toggle' : 'ads-action-play' }}" title="{{ $ad->is_active ? 'Tắt' : 'Bật' }}">
                                @if($ad->is_active)
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @else
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @endif
                            </button>
                            <a href="{{ route('filament.admin.resources.ads.edit', $ad) }}" class="ads-action-btn ads-action-edit" title="Chỉnh sửa">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <button wire:click="deleteAd({{ $ad->id }})" wire:confirm="Bạn có chắc muốn xóa quảng cáo này?" class="ads-action-btn ads-action-delete" title="Xóa">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="ads-list-panel">
                <div class="ads-empty-state">
                    <div class="ads-empty-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <h3 class="ads-empty-title">Chưa có quảng cáo nào</h3>
                    <p class="ads-empty-text">Hãy tạo quảng cáo đầu tiên để bắt đầu hiển thị trên trang web</p>
                    <a href="{{ route('filament.admin.resources.ads.create') }}" class="ads-btn ads-btn-primary">
                        <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Thêm quảng cáo
                    </a>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
