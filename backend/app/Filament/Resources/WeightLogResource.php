<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WeightLogResource\Pages;
use App\Models\WeightLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WeightLogResource extends Resource
{
    protected static ?string $model = WeightLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';
    
    protected static ?string $navigationLabel = 'Monitoring Berat';
    
    protected static ?string $modelLabel = 'Catatan Berat';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('goat_id')
                    ->relationship('goat', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Kambing'),
                Forms\Components\TextInput::make('weight')
                    ->required()
                    ->numeric()
                    ->label('Berat (kg)')
                    ->suffix('kg'),
                Forms\Components\DatePicker::make('date_recorded')
                    ->required()
                    ->default(now())
                    ->label('Tanggal Penimbangan'),
                Forms\Components\Textarea::make('note')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('goat.name')
                    ->label('Kambing')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('weight')
                    ->label('Berat')
                    ->numeric()
                    ->suffix(' kg')
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_recorded')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('note')
                    ->label('Catatan')
                    ->limit(50),
            ])
            ->defaultSort('date_recorded', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWeightLogs::route('/'),
            'create' => Pages\CreateWeightLog::route('/create'),
            'edit' => Pages\EditWeightLog::route('/{record}/edit'),
        ];
    }
}
