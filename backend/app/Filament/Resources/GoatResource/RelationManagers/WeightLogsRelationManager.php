<?php

namespace App\Filament\Resources\GoatResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class WeightLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'weightLogs';

    protected static ?string $title = 'Riwayat Penimbangan';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('weight')
                    ->required()
                    ->numeric()
                    ->label('Berat (kg)')
                    ->suffix('kg'),
                Forms\Components\DatePicker::make('date_recorded')
                    ->required()
                    ->default(now())
                    ->label('Tanggal'),
                Forms\Components\Textarea::make('note')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('weight')
            ->columns([
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
                    ->label('Catatan'),
            ])
            ->defaultSort('date_recorded', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
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
}
