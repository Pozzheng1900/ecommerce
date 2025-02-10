<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; // Added missing import

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2) // Creates a two-column layout
                    ->schema([
                        // Left Side: Product Details
                        Forms\Components\Section::make('Product Details')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $operation, $state, callable $set) => 
                                        $operation === 'create' ? $set('slug', Str::slug($state)) : null
                                    ),
                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Product::class, 'slug', ignoreRecord: true)
                                    ->disabled()
                                    ->dehydrated(),
                                Forms\Components\MarkdownEditor::make('description')
                                    ->columnSpanFull()
                                    ->fileAttachmentsDirectory('products'),
                                Forms\Components\FileUpload::make('images')
                                    ->multiple()
                                    ->directory('products')
                                    ->maxFiles(5)
                                    ->reorderable()
                                    ->disk('public') // Ensure the correct disk is used
                                    ->deleteUploadedFileUsing(fn ($file) => Storage::disk('public')->delete($file))
                                    ->preserveFilenames(),
                            ])
                            ->columnSpan(1), // Assign to left side

                        // Right Side: Pricing, Category, and Status
                        Forms\Components\Section::make('Pricing & Status')
                            ->schema([
                                Forms\Components\TextInput::make('price')
                                    ->numeric()
                                    ->required()
                                    ->prefix('Rield'),
                                Forms\Components\Select::make('category_id')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->relationship('category', 'name'),
                                Forms\Components\Select::make('brand_id')
                                    ->required()
                                    ->searchable()
                                    ->preload() 
                                    ->relationship('brand', 'name'),

                                // Product Status Toggles
                                Forms\Components\Section::make('Product Status')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_stocked')
                                            ->required()
                                            ->default(true),
                                        Forms\Components\Toggle::make('is_active')
                                            ->required()
                                            ->default(true),
                                        Forms\Components\Toggle::make('is_featured')
                                            ->required()
                                            ->default(false),
                                        Forms\Components\Toggle::make('on_sale')
                                            ->required()
                                            ->default(false),
                                    ])
                                    ->columns(2), // Display status toggles in two columns
                            ])
                            ->columnSpan(1), // Assign to right side
                    ]),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('brand.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('KHR')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_featured')->boolean(),
                Tables\Columns\BooleanColumn::make('on_sale'),
                Tables\Columns\BooleanColumn::make('is_stocked'),
                Tables\Columns\BooleanColumn::make('is_active'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault:true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault:true),
                
            ])
            ->filters([
                //
            ])
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
