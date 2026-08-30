<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Filament\Navigation\NavigationGroup;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->databaseNotifications()
            ->brandLogo(fn () => view('filament.logo'))
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_START,
                fn (): string => '<style>
                    /* Custom Green Sidebar Styles */
                    aside.fi-sidebar, .fi-sidebar { background-color: #0b7a42 !important; }
                    
                    /* Default text color white */
                    .fi-sidebar-item-label, .fi-sidebar-item-icon, .fi-sidebar-group-label { color: #ffffff !important; }
                    
                    /* Hover effect */
                    .fi-sidebar-item-button:hover { background-color: rgba(255, 255, 255, 0.1) !important; }
                    
                    /* Active Item: Make text black so it is readable on the white/light background */
                    .fi-sidebar-item-active .fi-sidebar-item-label, 
                    .fi-sidebar-item-active .fi-sidebar-item-icon { 
                        color: #000000 !important; 
                    }
                </style>'
            )
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Menu Operator'),
                NavigationGroup::make()
                    ->label('Master Data'),
                NavigationGroup::make()
                    ->label('Transaksi'),
                NavigationGroup::make()
                    ->label('Laporan'),
                NavigationGroup::make()
                    ->label('Menu Approval'),
                NavigationGroup::make()
                    ->label('Detail Project'),
                NavigationGroup::make()
                    ->label('Administrasi'),
                NavigationGroup::make()
                    ->label('Menu Admin'),
                NavigationGroup::make()
                    ->label('Filament Shield'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
