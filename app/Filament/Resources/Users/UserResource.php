<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Pelanggan';

    protected static ?string $modelLabel = 'pelanggan';

    protected static ?string $pluralModelLabel = 'pelanggan';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_admin', false);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nama lengkap')->required()->maxLength(150),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required(fn (string $operation, ?User $record): bool => $operation === 'create' || $record?->hasPassword() === true)
                    ->maxLength(255)
                    ->mutateStateForValidationUsing(fn (?string $state): ?string => filled($state) ? Str::lower(trim($state)) : null)
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Str::lower(trim($state)) : null)
                    ->unique(ignoreRecord: true),
                TextInput::make('phone')->label('Nomor WhatsApp')->tel()->maxLength(32),
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->rules(fn (string $operation): array => $operation === 'create'
                        ? ['required', Password::defaults()]
                        : [Password::defaults()])
                    ->dehydrated(fn (?string $state): bool => filled($state)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                TextColumn::make('email')->label('Email')->searchable()->sortable(),
                TextColumn::make('phone')->label('WhatsApp')->placeholder('Belum diisi'),
                TextColumn::make('created_at')->label('Terdaftar')->dateTime('d M Y')->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUsers::route('/'),
        ];
    }
}
