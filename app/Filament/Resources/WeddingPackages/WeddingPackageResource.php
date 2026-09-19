<?php

namespace App\Filament\Resources\WeddingPackages;

use App\Filament\Resources\WeddingPackages\Pages\ManageWeddingPackages;
use App\Models\WeddingPackage;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class WeddingPackageResource extends Resource
{
    protected static ?string $model = WeddingPackage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Paket Wedding';

    protected static ?string $modelLabel = 'paket wedding';

    protected static ?string $pluralModelLabel = 'paket wedding';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nama paket')->required()->maxLength(150),
                TextInput::make('slug')->label('Slug')->required()->maxLength(180)->regex('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/')->helperText('Gunakan huruf kecil, angka, dan tanda hubung. Contoh: paket-gold.')->unique(ignoreRecord: true),
                TextInput::make('tagline')->label('Tagline')->maxLength(255),
                Textarea::make('description')->label('Deskripsi dan isi layanan')->required()->rows(8)->maxLength(10000)->columnSpanFull(),
                Repeater::make('sections')
                    ->label('Section isi paket')
                    ->schema([
                        TextInput::make('title')->label('Nama section')->required()->maxLength(100),
                        Repeater::make('items')
                            ->label('Daftar isi')
                            ->simple(TextInput::make('item')->label('Item')->required()->maxLength(500))
                            ->default([])
                            ->addActionLabel('Tambah item')
                            ->minItems(1),
                    ])
                    ->default([])
                    ->addActionLabel('Tambah section')
                    ->collapsible()
                    ->columnSpanFull(),
                TextInput::make('price')->label('Harga')->numeric()->integer()->minValue(0)->required()->prefix('Rp'),
                TextInput::make('sort_order')->label('Urutan')->numeric()->integer()->minValue(0)->default(0)->required(),
                Toggle::make('is_active')->label('Tampilkan di website')->default(false),
                FileUpload::make('image_path')
                    ->label('Foto paket')
                    ->image()
                    ->disk('public')
                    ->directory('packages')
                    ->visibility('public')
                    ->maxSize(2048)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->rules(['extensions:jpg,jpeg,png,webp', 'dimensions:max_width=4096,max_height=4096'])
                    ->getUploadedFileNameForStorageUsing(fn (TemporaryUploadedFile $file): string => $file->hashName())
                    ->preventFilePathTampering()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                TextColumn::make('price')->label('Harga')->state(fn (WeddingPackage $record): string => $record->formatted_price)->sortable(),
                TextColumn::make('is_active')->label('Status')->badge()->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                TextColumn::make('sort_order')->label('Urutan')->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Status'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn (WeddingPackage $record): bool => ! $record->bookings()->exists())
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageWeddingPackages::route('/'),
        ];
    }
}
