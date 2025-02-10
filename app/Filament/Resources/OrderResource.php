<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\OrderResource\RelationManagers\AddressRelationManager;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Set;
use Illuminate\Support\Number;
use Filament\Resources\Pages\ViewRecord;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Left Section: Order Details (Customer, Currency, Payment Methods)
                Forms\Components\Section::make('Order Details')
                    ->columns(2)  // Left-right division (2 columns)
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Customer')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),
    
                        Forms\Components\Select::make('currency')
                            ->label('Currency')
                            ->options([
                                'USD' => 'USD ($)',
                                'EUR' => 'EUR (€)',
                                'GBP' => 'GBP (£)',
                                'KHR' => 'KHR (៛)',
                            ])
                            ->default('KHR')
                            ->required()
                            ->columnSpan(1),
    
                        Forms\Components\Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'stripe' => 'Stripe',
                                'cod' => 'Cash on Delivery',
                            ])
                            ->required()
                            ->columnSpan(1),
    
                        Forms\Components\Select::make('payment_status')
                            ->label('Payment Status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'failed' => 'Failed',
                            ])
                            ->default('pending')
                            ->required()
                            ->columnSpan(1),
                    ]),
    
                // Right Section: Order Status
                Forms\Components\Section::make('Order Status')
                    ->schema([
                        Forms\Components\ToggleButtons::make('status')
                            ->label('Order Status')
                            ->inline()
                            ->default('new')
                            ->required()
                            ->options([
                                'new' => 'New',
                                'processing' => 'Processing',
                                'shipped' => 'Shipped',
                                'delivered' => 'Delivered',
                                'cancelled' => 'Cancelled',
                            ])
                            ->colors([
                                'new' => 'info',
                                'processing' => 'warning',
                                'shipped' => 'success',
                                'delivered' => 'success',
                                'cancelled' => 'danger',
                            ])
                            ->icons([
                                'new' => 'heroicon-m-sparkles',
                                'processing' => 'heroicon-m-arrow-path',
                                'shipped' => 'heroicon-m-truck',
                                'delivered' => 'heroicon-o-check-badge',
                                'cancelled' => 'heroicon-m-x-circle',
                            ]),
                    ])
                    ->columnSpan(1),
    
                // Shipping Details on the Right
                Forms\Components\Section::make('Shipping Details')
                    ->schema([
                        Forms\Components\Select::make('shipping_method')
                            ->label('Shipping Method')
                            ->options([
                                'VET' => 'VET',
                                'express' => 'Express',
                                'overnight' => 'Overnight',
                            ])
                            ->columnSpan(1),
    
                        Forms\Components\Textarea::make('notes')
                            ->label('Additional Notes')
                            ->columnSpanFull()
                            ->rows(3),
                    ]),
    
                // Left Section: Order Items (with Repeater)
                Forms\Components\Section::make('Order Items')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Product')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->columnSpan(2)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $product = Product::find($state);
                                        $price = $product ? $product->price : 0;
                                    
                                        $set('unit_amount', $price);
                                        $set('total_amount', $price);
                                    }),
    
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Qty')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->columnSpan(1)
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, Set $set, Get $get) => $set('total_amount', $state * $get('unit_amount'))),
    
                                Forms\Components\TextInput::make('unit_amount')
                                    ->label('Unit Price')
                                    ->numeric()
                                    ->required()
                                    ->disabled()
                                    ->prefix('$')
                                    ->columnSpan(1),
    
                                Forms\Components\TextInput::make('total_amount')
                                    ->label('Total')
                                    ->numeric()
                                    ->required()
                                    ->prefix('$')
                                    ->columnSpan(1),
                            ])
                            ->columns(5)
                            ->collapsible()
                            ->grid(2),
                    ])
                    ->columnSpan(2),
    
                // Grand Total
                Forms\Components\Section::make('Grand Total')
                    ->schema([
                        Forms\Components\Placeholder::make('grand_total')
                            ->label('Grand Total')
                            ->columnSpanFull()
                            ->content(function (Get $get, Set $set) {
                                $total = 0;
                                if (!$repeaters = $get('items')) {
                                    return $total;
                                }
                                foreach ($repeaters as $key => $repeater) {
                                    $total += $get("items.{$key}.total_amount");
                                }
                                $set('grand_total', $total);
                                return Number::currency($total, 'KHR');
                            }),

                        Forms\Components\Hidden::make('grand_total')
                            ->default(0)
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([ 
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('grand_total')
                    ->numeric()
                    ->sortable()
                    ->money('KHR'),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('currency')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('shipping_method')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\SelectColumn::make('status')
                    ->label('Order Status')
                    ->options([
                        'new' => 'New',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ])
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
             ])
            ->filters([ /* Define your table filters here */ ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AddressRelationManager::class,
        ];
    }

    public static function getNavigationBadge(): ?string {
        return static::getModel()::count();
    }
    
    public static function getNavigationBadgeColor(): string|array|null {
        return static::getModel()::count() > 10 ? 'success' : 'danger';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
