<div class="fi-resource-relation-manager flex flex-col gap-y-6">
    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_TABS_START, scopes: $this->getRenderHookScopes()) }}
    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABS_START, scopes: $this->getRenderHookScopes()) }}
    <x-filament-panels::resources.tabs />
    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_TABS_END, scopes: $this->getRenderHookScopes()) }}
    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABS_END, scopes: $this->getRenderHookScopes()) }}

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_RELATION_MANAGER_BEFORE, scopes: $this->getRenderHookScopes()) }}

    {{ $this->table }}

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_RELATION_MANAGER_AFTER, scopes: $this->getRenderHookScopes()) }}

    <x-filament-panels::unsaved-action-changes-alert />
</div>
