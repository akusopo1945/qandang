<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GoatResource\Pages;
use App\Filament\Resources\GoatResource\RelationManagers;
use App\Models\Goat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;

class GoatResource extends Resource
{
    protected static ?string $model = Goat::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationLabel = 'Data Kambing';

    protected static ?string $modelLabel = 'Kambing';

    protected static ?string $pluralModelLabel = 'Data Kambing';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->imageEditor()
                            ->directory('goats')
                            ->label('Foto Kambing')
                            ->columnSpan(1)
                            ->maxSize(2048)
                            ->rules(['image', 'max:2048'])
                            ->validationMessages([
                                'max' => 'Ukuran foto maksimal 2MB. Silakan kompres foto Anda atau gunakan foto yang lebih kecil.',
                            ])
                            ->disk('public'),
                        Forms\Components\Group::make([
                            Forms\Components\TextInput::make('name')
                                ->label('Nama Kambing')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('qr_code')
                                ->label('Kode QR / ID')
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),
                        ])->columnSpan(1)->columns(1),
                        Forms\Components\Select::make('gender')
                            ->label('Jenis Kelamin')
                            ->options([
                                'male' => 'Jantan',
                                'female' => 'Betina',
                            ])
                            ->required(),
                        Forms\Components\Select::make('purpose')
                            ->label('Tujuan Pemeliharaan')
                            ->options([
                                'fattening' => 'Penggemukan (Fattening)',
                                'breeding' => 'Pembibitan (Breeding)',
                            ])
                            ->required()
                            ->live()
                            ->default('fattening'),
                        Forms\Components\Select::make('reproduction_status')
                            ->label('Status Reproduksi')
                            ->options([
                                'empty' => 'Kosong',
                                'heat' => 'Masa Birahi (Heat)',
                                'pregnant' => 'Bunting',
                                'lactating' => 'Menyusui',
                                'dry' => 'Kering Susu',
                            ])
                            ->visible(fn (Forms\Get $get): bool => 
                                $get('purpose') === 'breeding' && $get('gender') === 'female'
                            )
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state === 'pregnant') {
                                    $set('estimated_delivery_date', now()->addDays(150)->format('Y-m-d'));
                                } else {
                                    $set('estimated_delivery_date', null);
                                }
                            }),
                        Forms\Components\DatePicker::make('estimated_delivery_date')
                            ->label('Estimasi Kelahiran (HPL)')
                            ->helperText('Otomatis diisi 150 hari jika status "Bunting"')
                            ->visible(fn (Forms\Get $get): bool => $get('reproduction_status') === 'pregnant'),
                        Forms\Components\TextInput::make('breed')
                            ->label('Ras/Jenis')
                            ->placeholder('Contoh: Etawa, Boer, dll')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Marketplace (Katalog & Lelang)')
                    ->description('Atur harga dan status tampilan di halaman depan')
                    ->schema([
                        Forms\Components\Select::make('sale_status')
                            ->label('Status Jual')
                            ->options([
                                'internal' => 'Internal (Tidak Dijual)',
                                'for_sale' => 'Dijual (Katalog)',
                                'auction' => 'Dilelang',
                                'sold' => 'Terjual',
                            ])
                            ->required()
                            ->default('internal'),
                        Forms\Components\TextInput::make('price')
                            ->label('Harga (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->helperText('Kosongkan jika ingin lelang tanpa harga pembuka'),
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Tampilkan di Banner Utama (Hero)')
                            ->default(false),
                    ])->columns(3),

                Forms\Components\Section::make('Detail Pertumbuhan')
                    ->schema([
                        Forms\Components\DatePicker::make('birth_date')
                            ->label('Tanggal Lahir'),
                        Forms\Components\TextInput::make('initial_weight')
                            ->label('Berat Awal (kg)')
                            ->numeric()
                            ->suffix('kg'),
                        Forms\Components\TextInput::make('current_weight')
                            ->label('Berat Saat Ini (kg)')
                            ->numeric()
                            ->suffix('kg'),
                        Forms\Components\TextInput::make('height')
                            ->label('Tinggi Badan (cm)')
                            ->numeric()
                            ->suffix('cm'),
                        Forms\Components\TextInput::make('target_weight')
                            ->label('Target Berat (kg)')
                            ->numeric()
                            ->suffix('kg')
                            ->visible(fn (Forms\Get $get): bool => $get('purpose') === 'fattening'),
                        Forms\Components\Textarea::make('description')
                            ->label('Catatan Tambahan')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Silsilah (Pedigree)')
                    ->schema([
                        Forms\Components\Select::make('dam_id')
                            ->label('Induk (Dam)')
                            ->relationship('dam', 'name', fn ($query) => $query->where('gender', 'female'))
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('sire_id')
                            ->label('Bapak (Sire)')
                            ->relationship('sire', 'name', fn ($query) => $query->where('gender', 'male'))
                            ->searchable()
                            ->preload(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Foto')
                    ->circular()
                    ->disk('public')
                    ->action(
                        Tables\Actions\Action::make('viewImage')
                            ->modalHeading('Preview Foto')
                            ->modalContent(fn (Goat $record) => $record->image ? view('filament.components.image-preview', [
                                'imageUrl' => Storage::disk('public')->url($record->image),
                            ]) : null)
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Tutup')
                    ),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('qr_code')
                    ->label('Kode QR')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->description(fn (Goat $record): \Illuminate\Support\HtmlString => new \Illuminate\Support\HtmlString(
                        \SimpleSoftwareIO\QrCode\Facades\QrCode::size(40)->generate(route('catalog.show', $record->qr_code))
                    )),
                Tables\Columns\TextColumn::make('breed')
                    ->label('Ras')
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'boer' => 'warning',
                        'etawa' => 'success',
                        'saanen' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sale_status')
                    ->label('Status Jual')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'internal' => 'Internal',
                        'for_sale' => 'Dijual',
                        'auction' => 'Lelang',
                        'sold' => 'Terjual',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'for_sale' => 'success',
                        'auction' => 'warning',
                        'sold' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('purpose')
                    ->label('Tujuan')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'fattening' => 'Penggemukan',
                        'breeding' => 'Pembibitan',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'fattening' => 'warning',
                        'breeding' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('reproduction_status')
                    ->label('Status Reproduksi')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'empty' => 'Kosong',
                        'heat' => 'Birahi',
                        'pregnant' => 'Bunting',
                        'lactating' => 'Menyusui',
                        'dry' => 'Kering',
                        default => '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'pregnant' => 'danger',
                        'heat' => 'warning',
                        'lactating' => 'info',
                        default => 'gray',
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('estimated_delivery_date')
                    ->label('HPL')
                    ->date()
                    ->sortable()
                    ->color('danger')
                    ->description(fn (Goat $record): ?string => 
                        $record->estimated_delivery_date ? now()->diffInDays($record->estimated_delivery_date, false) . ' hari lagi' : null
                    )
                    ->visible(fn ($livewire) => true),
                Tables\Columns\TextColumn::make('gender')
                    ->label('JK')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'male' => 'Jantan',
                        'female' => 'Betina',
                        default => '?',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'male' => 'info',
                        'female' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('birth_date')
                    ->label('Tgl Lahir')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('initial_weight')
                    ->label('Berat')
                    ->numeric()
                    ->suffix(' kg')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('purpose')
                    ->label('Tujuan')
                    ->options([
                        'fattening' => 'Penggemukan',
                        'breeding' => 'Pembibitan',
                    ]),
                Tables\Filters\SelectFilter::make('reproduction_status')
                    ->label('Status Reproduksi')
                    ->options([
                        'empty' => 'Kosong',
                        'heat' => 'Birahi',
                        'pregnant' => 'Bunting',
                        'lactating' => 'Menyusui',
                        'dry' => 'Kering',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('exportCsv')
                    ->label('Ekspor Data (CSV/Excel)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->action(function () {
                        $goats = Goat::all();
                        
                        $callback = function() use ($goats) {
                            $file = fopen('php://output', 'w');
                            // Add UTF-8 BOM for proper excel loading
                            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                            
                            fputcsv($file, ['ID', 'Kode QR', 'Nama', 'Jenis Kelamin', 'Ras', 'Berat Awal', 'Berat Sekarang', 'Tujuan', 'HPL']);
                            
                            foreach ($goats as $goat) {
                                fputcsv($file, [
                                    $goat->id,
                                    $goat->qr_code,
                                    $goat->name,
                                    $goat->gender == 'male' ? 'Jantan' : 'Betina',
                                    $goat->breed,
                                    $goat->initial_weight,
                                    $goat->current_weight,
                                    $goat->purpose == 'milk' ? 'Susu' : ($goat->purpose == 'meat' ? 'Pedaging' : 'Breeding'),
                                    $goat->estimated_delivery_date
                                ]);
                            }
                            fclose($file);
                        };
                        
                        return response()->streamDownload($callback, "data-kambing-" . date('Y-m-d-His') . ".csv", [
                            "Content-Type" => "text/csv; charset=UTF-8",
                            "Pragma" => "no-cache",
                            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                            "Expires" => "0"
                        ]);
                    })
            ])
            ->actions([
                Tables\Actions\Action::make('downloadCertificate')
                    ->label('Sertifikat')
                    ->icon('heroicon-o-document-text')
                    ->color('danger')
                    ->url(fn (Goat $record): string => route('goat.pdf', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('predict')
                    ->label('AI Prediksi')
                    ->icon('heroicon-o-sparkles')
                    ->color('warning')
                    ->url(fn (Goat $record): string => static::getUrl('predict', ['record' => $record->id])),
                Tables\Actions\Action::make('downloadQr')
                    ->label('Download QR')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn (Goat $record): string => route('qr.download', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
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
            RelationManagers\WeightLogsRelationManager::class,
            RelationManagers\HealthRecordsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGoats::route('/'),
            'create' => Pages\CreateGoat::route('/create'),
            'edit' => Pages\EditGoat::route('/{record}/edit'),
            'predict' => Pages\AIPrediction::route('/{record}/predict'),
        ];
    }
}
