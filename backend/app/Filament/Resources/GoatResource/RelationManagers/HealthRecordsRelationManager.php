<?php

namespace App\Filament\Resources\GoatResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class HealthRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'healthRecords';

    protected static ?string $title = 'Riwayat Kesehatan';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->options([
                        'Vaksinasi' => 'Vaksinasi',
                        'Pengobatan' => 'Pengobatan',
                        'Pemeriksaan Rutin' => 'Pemeriksaan Rutin',
                        'Pemberian Vitamin' => 'Pemberian Vitamin',
                        'Lainnya' => 'Lainnya',
                    ])
                    ->required()
                    ->label('Jenis Layanan'),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->label('Judul/Tindakan'),
                Forms\Components\DatePicker::make('date_recorded')
                    ->required()
                    ->default(now())
                    ->label('Tanggal'),
                Forms\Components\Select::make('status')
                    ->options([
                        'scheduled' => 'Direncanakan',
                        'completed' => 'Selesai',
                    ])
                    ->required()
                    ->default('completed')
                    ->label('Status'),
                Forms\Components\Textarea::make('description')
                    ->label('Keterangan')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Vaksinasi' => 'success',
                        'Pengobatan' => 'danger',
                        default => 'info',
                    }),
                Tables\Columns\TextColumn::make('title')
                    ->label('Tindakan'),
                Tables\Columns\TextColumn::make('date_recorded')
                    ->label('Tanggal')
                    ->date(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'scheduled' => 'warning',
                        default => 'gray',
                    }),
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
