<?php

namespace App\Providers\Filament;

use App\Filament\Resources\SaleResource\Widgets\SaleChart;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Dashboard;
use App\Http\Middleware\EnsureUserIsActive;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Auth\Register;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Container\Attributes\Storage;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
<<<<<<< HEAD
use Illuminate\Support\Facades\Storage as FacadesStorage;
=======
use Illuminate\Support\Facades\Storage;
>>>>>>> fc9abc19883a4d43d2d6d4d549e388cbf79819c1
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->profile(EditProfile::class, isSimple: false)
<<<<<<< HEAD
            // ->brandName('TL Gold Computer')
            ->brandLogo(FacadesStorage::url(path: 'default/bg.png'))
=======
            ->brandLogo(Storage::url(path: 'default/bg.png'))
            ->brandLogoHeight('60px')
            // ->brandName('Computer Shop')
>>>>>>> fc9abc19883a4d43d2d6d4d549e388cbf79819c1
            ->id('admin')
            ->globalSearch(false)
            ->path('admin')
            ->login(Login::class)
            ->colors([
                'primary' => color::Blue,
                'secondery' => color::Blue
            ])
            ->sidebarWidth('16rem')
            ->navigationGroups([
                NavigationGroup::make('Inventory'),
                NavigationGroup::make('Customer & Supplier'),
                NavigationGroup::make('User Management'),
                NavigationGroup::make('Reports')
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
<<<<<<< HEAD
                \App\Filament\Pages\Dashboard1::class,
                Pages\Dashboard::class,
            ])
            // ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                //  Widgets\AccountWidget::class,
                //  Widgets\FilamentInfoWidget::class,
                \App\Filament\Widgets\SaleStats::class,
                //\App\Filament\Widgets\RevenueStats::class,
                \App\Filament\Widgets\InventoryStats::class,


                \App\Filament\Widgets\SalesChart::class,
                \App\Filament\Widgets\ProductImportChart::class,

                \App\Filament\Widgets\Brandpie::class,
                //\App\Filament\Widgets\Categorypie::class,

                \App\Filament\Widgets\LatestSale::class,
                \App\Filament\Widgets\SaleActivityChart::class,
            ])
=======
                Dashboard::class
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            // ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
                // SaleChart::class
            // ])
>>>>>>> fc9abc19883a4d43d2d6d4d549e388cbf79819c1
            ->middleware([
                // EnsureUserIsActive::class,
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
