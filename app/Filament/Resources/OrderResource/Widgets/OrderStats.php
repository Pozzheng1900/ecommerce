<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class OrderStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('New Orders', Order::query()->where('status', 'new')->count())
                ->icon('heroicon-o-shopping-cart') // 🛒 Shopping Cart Icon
                ->color('info'), // Optional color

            Stat::make('Processing Orders', Order::query()->where('status', 'processing')->count())
                ->icon('heroicon-o-cog') // ⚙️ Processing Icon
                ->color('warning'),

            Stat::make('Shipped Orders', Order::query()->where('status', 'shipped')->count())
                ->icon('heroicon-o-truck') // 🚚 Truck Icon
                ->color('success'),

            Stat::make('Average Price', Number::currency(Order::query()->avg('grand_total'), 'KHR(៛)'))
            ->icon('heroicon-o-currency-dollar')
                ->description('Average order value in Khmer Riel'),
        ];
    }
}
