<?php

namespace App\Filament\Pages;

use App\Services\TableImportExportService;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Schema as DBSchema;
use Illuminate\Support\Facades\Storage;

class ImportExport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationLabel = 'Import / Export';
    protected static ?string $title = 'Import / Export';

    protected string $view = 'filament.pages.import-export';

    // ---- IMPORT STATE ----
    public ?array $importData = [];

    // Set when an uploaded file's headers don't match any existing table.
    // Triggers the "name this table" modal in the Blade view.
    public bool $showTableNameModal = false;
    public ?string $pendingImportPath = null;
    public ?array $pendingImportHeaders = [];
    public ?string $newTableName = null;

    public function mount(): void
    {
        $this->importForm->fill();
    }

    protected function getForms(): array
    {
        return [
            'importForm',
        ];
    }

    public function importForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Radio::make('import_mode')
                    ->label('Import Type')
                    ->options([
                        'table' => 'Single Table (CSV or SQL)',
                    ])
                    ->default('table')
                    ->live(),

                FileUpload::make('file')
                    ->label('File')
                    ->rules(['file', 'mimes:csv,sql,txt'])
                    ->disk('local')
                    ->directory('imports')
                    ->required(),
            ])
            ->statePath('importData');
    }

    protected function getExistingTables(): array
    {
        $tables = DBSchema::getTableListing();

        return collect($tables)
            ->reject(fn ($t) => in_array($t, ['migrations', 'password_reset_tokens', 'failed_jobs', 'personal_access_tokens']))
            ->mapWithKeys(fn ($t) => [$t => $t])
            ->toArray();
    }

    // ---------------------------------------------------------------
    // IMPORT
    // ---------------------------------------------------------------

    public function runImport(TableImportExportService $service): void
    {
        $state = $this->importForm->getState();

        $path = Storage::disk('local')->path($state['file']);

        // No target table selection — always resolve the table name via
        // the "name your table" modal (it will create the table if the
        // name doesn't exist yet, or import into it if it does).
        $headers = $service->peekHeaders($path);

        $this->pendingImportPath = $state['file'];
        $this->pendingImportHeaders = $headers;
        $this->showTableNameModal = true;
    }

    /**
     * Called from the "name your table" modal once the user confirms a name.
     */
    public function confirmNewTableName(TableImportExportService $service): void
    {
        $this->validate([
            'newTableName' => ['required', 'string', 'regex:/^[a-zA-Z_][a-zA-Z0-9_]*$/', 'max:64'],
        ]);

        $path = Storage::disk('local')->path($this->pendingImportPath);
        $this->processTableImport($service, $path, $this->newTableName, createIfMissing: true);

        $this->showTableNameModal = false;
        $this->pendingImportPath = null;
        $this->pendingImportHeaders = [];
        $this->newTableName = null;
    }

    public function cancelTableNameModal(): void
    {
        $this->showTableNameModal = false;
        $this->pendingImportPath = null;
        $this->pendingImportHeaders = [];
        $this->newTableName = null;
    }

    protected function processTableImport(
        TableImportExportService $service,
        string $path,
        string $table,
        bool $createIfMissing = false
    ): void {
        try {
            $result = $service->importTable($path, $table, $createIfMissing);

            Notification::make()
                ->title("Import into `{$table}` complete")
                ->body("{$result['inserted']} new rows inserted, {$result['skipped']} existing rows skipped.")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Import failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}