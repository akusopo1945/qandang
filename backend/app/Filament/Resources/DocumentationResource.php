<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentationResource\Pages;
use App\Models\Documentation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DocumentationResource extends Resource
{
    protected static ?string $model = Documentation::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    
    protected static ?int $navigationSort = 100;
    
    protected static ?string $navigationLabel = 'Dokumentasi';
    
    protected static ?string $modelLabel = 'Dokumen';

    protected static ?string $pluralModelLabel = 'Dokumentasi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Meta Data')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Dokumen')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('category')
                            ->label('Kategori')
                            ->options([
                                'Panduan Pengguna' => 'Panduan Pengguna',
                                'Dokumentasi Teknis' => 'Dokumentasi Teknis',
                                'API Reference' => 'API Reference',
                                'Changelog' => 'Changelog',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('order')
                            ->label('Urutan Tampilan')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Toggle::make('is_published')
                            ->label('Publikasikan')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Konten')
                    ->schema([
                        Forms\Components\MarkdownEditor::make('content')
                            ->label('Isi Dokumentasi (Markdown Support)')
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Panduan Pengguna' => 'success',
                        'Dokumentasi Teknis' => 'info',
                        'API Reference' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('order')
                    ->label('Urutan')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Status')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diupdate')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('order', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'Panduan Pengguna' => 'Panduan Pengguna',
                        'Dokumentasi Teknis' => 'Dokumentasi Teknis',
                        'API Reference' => 'API Reference',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListDocumentations::route('/'),
            'create' => Pages\CreateDocumentation::route('/create'),
            'view' => Pages\ViewDocumentation::route('/{record}'),
            'edit' => Pages\EditDocumentation::route('/{record}/edit'),
        ];
    }
}
