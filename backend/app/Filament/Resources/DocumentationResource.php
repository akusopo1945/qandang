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

                Forms\Components\Placeholder::make('navigation')
                    ->label('')
                    ->visible(fn ($operation) => $operation === 'view')
                    ->content(function (Documentation $record) {
                        $prev = Documentation::where('order', '<', $record->order)
                            ->orWhere(function($query) use ($record) {
                                $query->where('order', $record->order)
                                      ->where('id', '<', $record->id);
                            })
                            ->orderBy('order', 'desc')
                            ->orderBy('id', 'desc')
                            ->first();

                        $next = Documentation::where('order', '>', $record->order)
                            ->orWhere(function($query) use ($record) {
                                $query->where('order', $record->order)
                                      ->where('id', '>', $record->id);
                            })
                            ->orderBy('order', 'asc')
                            ->orderBy('id', 'asc')
                            ->first();

                        $prevHtml = $prev 
                            ? '<a href="'.DocumentationResource::getUrl('view', ['record' => $prev->id]).'" class="fi-btn fi-btn-size-md relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-gray fi-btn-color-gray bg-white text-gray-950 shadow-sm ring-1 ring-gray-950/10 hover:bg-gray-50 px-3 py-2 text-sm inline-grid gap-1.5">&larr; Sebelumnya: '.$prev->title.'</a>' 
                            : '<span></span>';

                        $nextHtml = $next 
                            ? '<a href="'.DocumentationResource::getUrl('view', ['record' => $next->id]).'" class="fi-btn fi-btn-size-md relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-primary fi-btn-color-primary bg-primary-600 text-white shadow-sm hover:bg-primary-500 px-3 py-2 text-sm inline-grid gap-1.5">Selanjutnya: '.$next->title.' &rarr;</a>' 
                            : '<span></span>';

                        return new \Illuminate\Support\HtmlString('
                            <div class="flex justify-between items-center mt-8 pt-8 border-t border-gray-100">
                                '.$prevHtml.'
                                '.$nextHtml.'
                            </div>
                        ');
                    })
                    ->columnSpanFull(),
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
