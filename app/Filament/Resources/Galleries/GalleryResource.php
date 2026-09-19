<?php

namespace App\Filament\Resources\Galleries;

use App\Filament\Resources\Galleries\Pages\ManageGalleries;
use App\Models\Gallery;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class GalleryResource extends Resource
{
    protected static ?string $model = Gallery::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Gallery';

    protected static ?string $modelLabel = 'foto gallery';

    protected static ?string $pluralModelLabel = 'gallery';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')->label('Judul')->required()->maxLength(150),
                Textarea::make('description')->label('Keterangan')->rows(4)->maxLength(2000),
                TextInput::make('sort_order')->label('Urutan')->numeric()->integer()->minValue(0)->default(0)->required(),
                Toggle::make('is_published')->label('Tampilkan di website')->default(false),
                FileUpload::make('image_path')
                    ->label('Foto')
                    ->image()
                    ->disk('public')
                    ->directory('gallery')
                    ->visibility('public')
                    ->maxSize(2048)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->rules(['extensions:jpg,jpeg,png,webp', 'dimensions:max_width=4096,max_height=4096'])
                    ->getUploadedFileNameForStorageUsing(fn (TemporaryUploadedFile $file): string => $file->hashName())
                    ->preventFilePathTampering()
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')->label('Foto')->disk('public'),
                TextColumn::make('title')->label('Judul')->searchable()->sortable(),
                TextColumn::make('is_published')->label('Status')->badge()->formatStateUsing(fn (bool $state): string => $state ? 'Tayang' : 'Draft')->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                TextColumn::make('sort_order')->label('Urutan')->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_published')->label('Status'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageGalleries::route('/'),
        ];
    }
}
