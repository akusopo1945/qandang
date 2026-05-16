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
                            ->columnSpan(1),
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
                        Forms\Components\TextInput::make('breed')
                            ->label('Ras/Jenis')
                            ->placeholder('Contoh: Etawa, Boer, dll')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Detail Pertumbuhan')
                    ->schema([
                        Forms\Components\DatePicker::make('birth_date')
                            ->label('Tanggal Lahir'),
                        Forms\Components\TextInput::make('initial_weight')
                            ->label('Berat Awal (kg)')
                            ->numeric()
                            ->suffix('kg'),
                        Forms\Components\Textarea::make('description')
                            ->label('Catatan Tambahan')
                            ->columnSpanFull(),
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
                    ->disk('public'),
                Tables\Columns\TextColumn::make('qr_code')
                    ->label('Kode QR')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->formatStateUsing(fn (string $state): string => $state)
                    ->description(fn (Goat $record): \Illuminate\Support\HtmlString => new \Illuminate\Support\HtmlString(
                        \SimpleSoftwareIO\QrCode\Facades\QrCode::size(50)->generate($record->qr_code ?? $record->id)
                    )),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('breed')
                    ->label('Ras')
                    ->searchable(),
                Tables\Columns\IconColumn::make('gender')
                    ->label('JK')
                    ->icon(fn (string $state): string => match ($state) {
                        'male' => 'heroicon-m-user',
                        'female' => 'heroicon-m-user-minus', // Just placeholders, could use custom SVG
                        default => 'heroicon-m-question-mark-circle',
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
                //
            ])
            ->actions([
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
