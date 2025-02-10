<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AddressRelationManager extends RelationManager
{
    protected static string $relationship = 'address';

    public function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Section::make('Personal Information')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('first_name')
                        ->label('First Name')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('last_name')
                        ->label('Last Name')
                        ->required()
                        ->maxLength(255),
                ]),

            Forms\Components\Section::make('Contact Details')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('phone')
                        ->label('Phone Number')
                        ->required()
                        ->tel()
                        ->prefix('+')
                        ->maxLength(20)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('zip_code')
                        ->label('ZIP Code')
                        ->required()
                        ->numeric()
                        ->maxLength(20)
                        ->columnSpan(1),
                ]),

            Forms\Components\Section::make('Address Details')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('city')
                        ->label('City')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('state')
                        ->label('State')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Textarea::make('street_address')
                        ->label('Street Address')
                        ->required()
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
}


    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('street_address')
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('zip_code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('state')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('street_address')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
