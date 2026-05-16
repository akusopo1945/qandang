<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HealthRecordResource\Pages;
use App\Models\HealthRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HealthRecordResource extends Resource
{
    protected static ?string $model = HealthRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';
    
    protected static ?string $navigationLabel = 'Rekam Kesehatan';
    
    protected static ?string $modelLabel = 'Rekam Kesehatan';

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
                    ->maxLength(255)
                    ->label('Judul/Tindakan')
                    ->placeholder('Contoh: Vaksin Antraks'),
                Forms\Components\DatePicker::make('date_recorded')
                    ->required()
                    ->default(now())
                    ->label('Tanggal Tindakan'),
                Forms\Components\Select::make('status')
                    ->options([
                        'scheduled' => 'Direncanakan',
                        'completed' => 'Selesai',
                    ])
                    ->required()
                    ->default('completed')
                    ->label('Status'),
                Forms\Components\Textarea::make('description')
                    ->label('Keterangan/Resep')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->imageEditor()
                    ->directory('health-records')
                    ->label('Foto Dokumentasi')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Foto')
                    ->disk('public')
                    ->square(),
                Tables\Columns\TextColumn::make('goat.name')
                    ->label('Kambing')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Vaksinasi' => 'success',
                        'Pengobatan' => 'danger',
                        'Pemeriksaan Rutin' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('title')
                    ->label('Tindakan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_recorded')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'scheduled' => 'warning',
                        default => 'gray',
                    }),
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
            'index' => Pages\ListHealthRecords::route('/'),
            'create' => Pages\CreateHealthRecord::route('/create'),
            'edit' => Pages\EditHealthRecord::route('/{record}/edit'),
        ];
    }
}
