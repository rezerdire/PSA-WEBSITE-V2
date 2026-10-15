<?php

namespace App\Filament\Resources\Booths\Tables;

use App\Models\Booth;
use App\Support\BoothQr;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class BoothsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('booth_qr')
                    ->label('QR')
                    ->state(fn (Booth $record) => BoothQr::url($record->booth_qr))
                    ->height(70)
                    ->extraImgAttributes(['style' => 'background:#fff;padding:2px;border:1px solid #e5e7eb;']),
                TextColumn::make('booth_type')->searchable(),
                TextColumn::make('booth_name')->searchable(),
                TextColumn::make('location')->searchable(),
                TextColumn::make('booth_code')->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('generateQr')
                    ->label(fn (Booth $record) => blank(BoothQr::url($record->booth_qr)) ? 'Generate QR' : 'Regenerate QR')
                    ->icon('heroicon-m-qr-code')
                    ->action(function (Booth $record) {
                        $record->update(['booth_qr' => BoothQr::generate($record->booth_code)]);

                        Notification::make()->title('QR generated')->success()->send();
                    }),

                Action::make('downloadPdf')
                    ->label('PDF')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->visible(fn (Booth $record) => filled(BoothQr::url($record->booth_qr)))
                    ->action(fn (Booth $record) => self::downloadPdf(
                        collect([$record]),
                        "booth-{$record->booth_code}.pdf"
                    )),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('generateQrs')
                        ->label('Generate QR')
                        ->icon('heroicon-m-qr-code')
                        ->action(function (Collection $records) {
                            $records->each(fn (Booth $b) => $b->update(['booth_qr' => BoothQr::generate($b->booth_code)]));

                            Notification::make()->title('QR codes generated')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('downloadPdfs')
                        ->label('Download PDF')
                        ->icon('heroicon-m-arrow-down-tray')
                        ->action(fn (Collection $records) => self::downloadPdf(
                            $records->filter(fn ($b) => filled(BoothQr::url($b->booth_qr))),
                            'booth-qr-codes.pdf'
                        )),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function downloadPdf($booths, string $filename)
    {
        $items = $booths->map(function (Booth $b) {
            $path = public_path($b->booth_qr);

            return [
                'name' => $b->booth_name,
                'type' => $b->booth_type,
                'location' => $b->location,
                'code' => $b->booth_code,
                'qr' => file_exists($path)
                    ? 'data:image/svg+xml;base64,' . base64_encode(file_get_contents($path))
                    : null,
            ];
        });

        $pdf = Pdf::loadView('pdf.booth-qr', ['booths' => $items])->setPaper('a4', 'portrait');

        return response()->streamDownload(fn () => print($pdf->output()), $filename);
    }
}