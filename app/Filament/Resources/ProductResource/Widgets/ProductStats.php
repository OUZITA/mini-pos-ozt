<?php

namespace App\Filament\Resources\ProductResource\Widgets;

use App\Filament\Resources\ProductResource\Pages\ListProducts;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListProducts::class;
    }

    protected function getStats(): array
    {
        return [
<<<<<<< HEAD
            Stat::make('Total Unique Products', $this->getPageTableQuery()->count())->chart([27, 27])
                ->color('info'),
=======
            Stat::make('Total Products', $this->getPageTableQuery()->where('active', true)->count())
                ->description('32k increase')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'wire:click' => "\$dispatch('setStatusFilter', { filter: 'processed' })",
                ]),
>>>>>>> fc9abc19883a4d43d2d6d4d549e388cbf79819c1

            Stat::make('Product Inventory', $this->getPageTableQuery()->sum('stock'))->chart([27, 27])
                ->color('info'),
            Stat::make('Average Price', '$ ' . number_format($this->getPageTableQuery()->avg('price'), 2))->chart([27, 27])
                ->color('info'),
        ];
    }
}
