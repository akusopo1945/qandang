<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Actions\Action;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;

class CustomLogin extends BaseLogin
{
    public function mount(): void
    {
        parent::mount();
        
        $this->regenerateCaptcha();
    }

    public function regenerateCaptcha(): void
    {
        $ops = ['+', '-', '*'];
        $op = $ops[array_rand($ops)];
        
        $quest = '';
        $ans = 0;
        
        switch ($op) {
            case '+':
                $a = rand(15, 60);
                $b = rand(15, 60);
                $quest = "{$a} + {$b}";
                $ans = $a + $b;
                break;
            case '-':
                $a = rand(30, 99);
                $b = rand(10, 29);
                $quest = "{$a} - {$b}";
                $ans = $a - $b;
                break;
            case '*':
                $a = rand(4, 9);
                $b = rand(7, 14);
                $quest = "{$a} x {$b}";
                $ans = $a * $b;
                break;
        }
        
        session(['login_captcha_ans' => $ans]);
        session(['login_captcha_quest' => "Berapa {$quest}?"]);
    }

    public function form(Form $form): Form
    {
        $form = parent::form($form);
        
        $components = $form->getComponents();
        
        // Append Captcha input with suffix action to refresh the question
        $components[] = TextInput::make('captcha_input')
            ->label(fn () => session('login_captcha_quest', 'Berapa 5 + 3?'))
            ->required()
            ->numeric()
            ->extraAttributes(['autocomplete' => 'off'])
            ->placeholder('Hasil perhitungan')
            ->suffixAction(
                Action::make('refresh')
                    ->icon('heroicon-m-arrow-path')
                    ->tooltip('Ganti soal')
                    ->action(function ($livewire) {
                        $livewire->regenerateCaptcha();
                    })
            )
            ->rules([
                fn (): \Closure => function (string $attribute, $value, \Closure $fail) {
                    if ((int) $value !== (int) session('login_captcha_ans')) {
                        $this->regenerateCaptcha();
                        $fail('Jawaban Captcha salah! Soal sudah diperbarui.');
                    }
                },
            ]);
            
        return $form->components($components);
    }
    
    public function authenticate(): ?LoginResponse
    {
        $response = parent::authenticate();
        
        if ($response) {
            session()->forget(['login_captcha_ans', 'login_captcha_quest']);
        }
        
        return $response;
    }
}
