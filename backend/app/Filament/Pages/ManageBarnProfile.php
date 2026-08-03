<?php

namespace App\Filament\Pages;

use App\Models\BarnProfile;
use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ManageBarnProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    
    protected static ?string $navigationLabel = 'Profil Kandang';
    
    protected static ?string $title = 'Profil Kandang';

    protected static string $view = 'filament.pages.manage-barn-profile';

    public ?array $data = [];

    public function mount(): void
    {
        $profile = BarnProfile::first();
        
        if ($profile) {
            $this->form->fill($profile->toArray());
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Umum Kandang')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Kandang')
                            ->required(),
                        TextInput::make('owner_name')
                            ->label('Nama Pemilik')
                            ->required(),
                        TextInput::make('phone')
                            ->label('No. WhatsApp Admin / CS')
                            ->tel()
                            ->required(),
                        TextInput::make('capacity')
                            ->label('Kapasitas Kandang (Ekor)')
                            ->numeric()
                            ->required(),
                    ])->columns(2),

                Section::make('Alamat & Lokasi')
                    ->schema([
                        TextInput::make('address')
                            ->label('Alamat Lengkap')
                            ->required(),
                        TextInput::make('village')
                            ->label('Desa / Kelurahan')
                            ->required(),
                        TextInput::make('district')
                            ->label('Kecamatan')
                            ->required(),
                        TextInput::make('city')
                            ->label('Kabupaten / Kota')
                            ->required(),
                        TextInput::make('province')
                            ->label('Provinsi')
                            ->required(),
                    ])->columns(2),

                Section::make('Deskripsi & Catatan')
                    ->schema([
                        Textarea::make('description')
                            ->label('Deskripsi Tambahan')
                            ->rows(4),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Perubahan')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $profile = BarnProfile::first();
        
        if (!$profile) {
            $profile = new BarnProfile();
        }
        
        $profile->fill($this->form->getState());
        $profile->save();

        Notification::make()
            ->title('Berhasil disimpan!')
            ->success()
            ->send();
    }
}
