<?php

namespace App\Filament\Resources\Bookings;

use App\Actions\Bookings\ChangeBookingStatus;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\Bookings\Pages\ManageBookings;
use App\Models\Booking;
use BackedEnum;
use DomainException;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Booking';

    protected static ?string $modelLabel = 'booking';

    protected static ?string $pluralModelLabel = 'booking';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi acara')
                    ->schema([
                        TextInput::make('couple_name')->label('Nama pelanggan / pasangan')->disabled()->dehydrated(false),
                        DatePicker::make('event_date')->label('Tanggal acara')->disabled()->dehydrated(false),
                        Select::make('status')->label('Status booking')->options(collect(BookingStatus::cases())->mapWithKeys(fn (BookingStatus $status): array => [$status->value => $status->label()])->all())->disabled()->dehydrated(false),
                        Select::make('user_id')->label('Pelanggan')->relationship('user', 'name')->disabled()->dehydrated(false),
                        Select::make('wedding_package_id')->label('Paket saat ini')->relationship('weddingPackage', 'name')->disabled()->dehydrated(false),
                        TextInput::make('package_name_snapshot')->label('Nama paket saat diajukan')->disabled()->dehydrated(false),
                        TextInput::make('package_price_snapshot')->label('Harga saat diajukan')->disabled()->dehydrated(false)->formatStateUsing(fn (?int $state): ?string => $state === null ? null : 'Rp '.number_format($state, 0, ',', '.')),
                        Textarea::make('event_location')->label('Lokasi acara')->disabled()->dehydrated(false)->columnSpanFull(),
                        Textarea::make('notes')->label('Catatan pelanggan')->disabled()->dehydrated(false)->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Operasional')
                    ->description('Isi vendor/personel yang benar-benar digunakan untuk acara ini. Data ini terpisah dari rekomendasi vendor pada katalog paket.')
                    ->schema([
                        TextInput::make('makeup')->label('Make Up')->maxLength(255),
                        TextInput::make('henna')->label('Henna')->maxLength(255),
                        TextInput::make('photographer')->label('Photographer')->maxLength(255),
                        TextInput::make('mc')->label('MC')->maxLength(255),
                        TextInput::make('entertainment')->label('Hiburan')->maxLength(255),
                        TextInput::make('traditional_ceremony')->label('Upacara Adat')->maxLength(255),
                        TextInput::make('eo')->label('EO')->maxLength(255),
                        TextInput::make('videographer')->label('Videographer')->maxLength(255),
                        TextInput::make('wedding_content_creator')->label('Wedding Content Creator')->maxLength(255),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Pembayaran dan catatan internal')
                    ->schema([
                        Select::make('payment_status')->label('Status pembayaran')->options(collect(PaymentStatus::cases())->mapWithKeys(fn (PaymentStatus $status): array => [$status->value => $status->label()])->all())->required(),
                        Textarea::make('cancellation_reason')->label('Alasan pembatalan')->maxLength(500),
                        DateTimePicker::make('terms_accepted_at')->label('Terms disetujui pada')->disabled()->dehydrated(false),
                        TextInput::make('terms_version')->label('Versi terms')->disabled()->dehydrated(false),
                        Textarea::make('terms_snapshot')->label('Ketentuan yang disetujui')->disabled()->dehydrated(false)->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Textarea::make('package_description_snapshot')->label('Ringkasan paket saat diajukan')->disabled()->dehydrated(false)->columnSpanFull(),
                Textarea::make('package_sections_snapshot')
                    ->label('Section paket saat diajukan')
                    ->formatStateUsing(fn (?array $state): string => collect($state ?? [])->map(fn (array $section): string => ($section['title'] ?? 'Section').":\n".collect($section['items'] ?? [])->filter(fn ($item): bool => filled($item))->map(fn ($item): string => '- '.$item)->implode("\n"))->implode("\n\n"))
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                TextColumn::make('user.name')->label('Pelanggan')->searchable()->sortable(),
                TextColumn::make('package_name_snapshot')->label('Paket')->searchable(),
                TextColumn::make('event_date')->label('Tanggal acara')->date('d M Y')->sortable(),
                TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn (BookingStatus $state): string => $state->label())->color(fn (BookingStatus $state): string => $state->color()),
                TextColumn::make('payment_status')->label('Pembayaran')->badge()->formatStateUsing(fn (PaymentStatus $state): string => $state->label())->color(fn (PaymentStatus $state): string => $state->color()),
            ])
            ->filters([
                SelectFilter::make('status')->options(collect(BookingStatus::cases())->mapWithKeys(fn (BookingStatus $status): array => [$status->value => $status->label()])->all()),
            ])
            ->recordActions([
                EditAction::make()->label('Edit detail'),
                ViewAction::make()->label('Lihat'),
                Action::make('contactCustomer')
                    ->label('Hubungi pelanggan')
                    ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                    ->color('success')
                    ->url(function (Booking $record): ?string {
                        $phone = preg_replace('/\\D+/', '', (string) $record->user?->phone);
                        $phone = str_starts_with($phone, '0') ? '62'.substr($phone, 1) : $phone;

                        return filled($phone) ? 'https://wa.me/'.$phone : null;
                    })
                    ->openUrlInNewTab()
                    ->visible(fn (Booking $record): bool => filled($record->user?->phone)),
                Action::make('changeStatus')
                    ->label('Ubah status')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('primary')
                    ->authorize(fn (Booking $record): bool => auth()->user()?->can('update', $record) ?? false)
                    ->visible(fn (Booking $record): bool => $record->status->transitions() !== [])
                    ->schema([
                        Select::make('status')
                            ->label('Status baru')
                            ->options(fn (Booking $record): array => collect($record->status->transitions())->mapWithKeys(fn (BookingStatus $status): array => [$status->value => $status->label()])->all())
                            ->required(),
                        Textarea::make('cancellation_reason')->label('Alasan pembatalan')->requiredIf('status', BookingStatus::Cancelled->value)->maxLength(500)->helperText('Wajib diisi ketika status Dibatalkan.'),
                    ])
                    ->action(function (Booking $record, array $data, Action $action): void {
                        try {
                            app(ChangeBookingStatus::class)->handle($record, BookingStatus::from($data['status']), $data['cancellation_reason'] ?? null);
                            Notification::make()->title('Status booking diperbarui.')->success()->send();
                        } catch (DomainException $exception) {
                            Notification::make()->title($exception->getMessage())->danger()->send();
                            $action->halt();
                        }
                    }),
            ])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageBookings::route('/'),
        ];
    }
}
