<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Filament\Resources\BrandResource\RelationManagers;
use App\Models\Brand;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; // Added missing import

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Category Details')
                    ->description('Fill out the details of the category.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Category Name')
                            ->placeholder('Enter category name...')
                            ->maxLength(255)
                            ->required()
                            ->columnSpanFull()
                            ->live()
                            ->afterStateUpdated(fn (string $operation, $state, callable $set) => 
                                $operation === 'create' ? $set('slug', Str::slug($state)) : null
                            ),
                        
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug (URL)')
                            ->placeholder('Auto-generated if left empty')
                            ->maxLength(255)
                            ->unique(Brand::class, 'slug', ignoreRecord: true)
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->columnSpanFull(),
                        
                        Forms\Components\FileUpload::make('image')
                            ->label('Category Image')
                            ->image()
                            ->directory('brand-images')
                            ->imagePreviewHeight('150')
                            ->imageResizeTargetWidth('400')
                            ->imageResizeTargetHeight('300')
                            ->columnSpanFull()
                            ->disk('public') // Ensure the correct disk is used
                            ->deleteUploadedFileUsing(fn ($file) => Storage::disk('public')->delete($file))
                            ->preserveFilenames(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active Status')
                            ->helperText('Enable to activate this category')
                            ->default(false),
                    ])
                    ->columns(2), // Organizes fields into two columns
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('slug')->searchable(),
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\BooleanColumn::make('is_active'),
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
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
