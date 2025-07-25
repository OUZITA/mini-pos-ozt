<?php

namespace App\Filament\Resources\ProductResource\Widgets;

use App\Filament\Resources\ProductResource\Pages\ListProducts;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductStats extends BaseWidget
{
    use InteractsWithPageTable;
    // Disable polling (manual refresh only)
    protected static ?string $pollingInterval = null;

    /**
     * Define which page this widget is tied to
     */
    protected function getTablePage(): string
    {
        return ListProducts::class;
    }

    /**
     * Return array of stats to display
     */
    protected function getStats(): array
    {
        $query = $this->getPageTableQuery();

        return [
            // Total number of unique products
            Stat::make('Total Unique Products', $query->count())
                ->chart([27, 27])
                ->color('info'),

            // Total inventory stock (sum of all product stock values)
            Stat::make('Product Inventory', $query->sum('stock'))
                ->chart([27, 27])
                ->color('info'),

            // Average price, formatted as USD with 2 decimals
            Stat::make('Average Price', '$ ' . number_format($query->avg('price'), 2))
                ->chart([27, 27])
                ->color('info'),
        ];
    }
}
