<?php

namespace App\Filament\Resources\Registrations\Widgets;

use App\Models\Registration;
use App\Models\WorkshopRegistration;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

class RegistrationAnalyticsChart extends ChartWidget
{
    protected ?string $heading = 'Registration Activity';

    protected ?string $description = 'Annual convention and workshop registrants over the last 30 days';
    protected int|string|array $columnSpan = 'full';


    protected function getData(): array
    {
        $today = CarbonImmutable::now()->startOfDay();
        $start = $today->subDays(29);
        $dates = collect(range(0, 29))
            ->map(fn (int $offset): CarbonImmutable => $start->addDays($offset));

        $annualCounts = Registration::query()
            ->whereBetween('created_at', [$start, $today->endOfDay()])
            ->get(['created_at'])
            ->groupBy(fn (Registration $registration): string => $registration->created_at->toDateString())
            ->map->count();

        $workshopCounts = WorkshopRegistration::query()
            ->whereBetween('created_at', [$start, $today->endOfDay()])
            ->get(['created_at'])
            ->groupBy(fn (WorkshopRegistration $registration): string => $registration->created_at->toDateString())
            ->map->count();

        return [
            'datasets' => [
                [
                    'label' => 'Annual registrations',
                    'data' => $dates->map(fn (CarbonImmutable $date): int => $annualCounts->get($date->toDateString(), 0))->all(),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Workshop registrants',
                    'data' => $dates->map(fn (CarbonImmutable $date): int => $workshopCounts->get($date->toDateString(), 0))->all(),
                    'borderColor' => '#2563eb',
                    'backgroundColor' => 'rgba(37, 99, 235, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $dates->map(fn (CarbonImmutable $date): string => $date->format('M j'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
