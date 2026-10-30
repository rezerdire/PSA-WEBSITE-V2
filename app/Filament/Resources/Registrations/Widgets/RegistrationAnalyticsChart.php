<?php

namespace App\Filament\Resources\Registrations\Widgets;

use App\Models\Registration;
use App\Models\WorkshopRegistration;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

class RegistrationAnalyticsChart extends ChartWidget
{
    public ?string $filter = 'day';

    protected ?string $heading = 'Registration Activity';

    protected ?string $description = 'Compare annual convention and workshop registrations by day, week, or month';

    protected int|string|array $columnSpan = 'full';

    protected function getFilters(): ?array
    {
        return [
            'day' => 'Daily (last 30 days)',
            'week' => 'Weekly (last 12 weeks)',
            'month' => 'Monthly (last 12 months)',
        ];
    }

    protected function getData(): array
    {
        $filter = match ($this->filter) {
            'week' => 'week',
            'month' => 'month',
            default => 'day',
        };

        $today = CarbonImmutable::now();
        $dates = $this->getBucketDates($filter, $today);
        $start = $dates[0]->startOfDay();
        $lastDate = $dates[array_key_last($dates)];
        $end = match ($filter) {
            'week' => $lastDate->endOfWeek(),
            'month' => $lastDate->endOfMonth(),
            default => $lastDate->endOfDay(),
        };
        $annualCounts = $this->getCounts(Registration::class, $start, $end, $filter);
        $workshopCounts = $this->getCounts(WorkshopRegistration::class, $start, $end, $filter);

        return [
            'datasets' => [
                [
                    'label' => 'Annual registrations',
                    'data' => array_map(
                        fn (CarbonImmutable $date): int => $annualCounts[$this->bucketKey($date, $filter)] ?? 0,
                        $dates,
                    ),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Workshop registrants',
                    'data' => array_map(
                        fn (CarbonImmutable $date): int => $workshopCounts[$this->bucketKey($date, $filter)] ?? 0,
                        $dates,
                    ),
                    'borderColor' => '#2563eb',
                    'backgroundColor' => 'rgba(37, 99, 235, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => array_map(
                fn (CarbonImmutable $date): string => $this->labelFor($date, $filter),
                $dates,
            ),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * @return array<int, CarbonImmutable>
     */
    private function getBucketDates(string $filter, CarbonImmutable $today): array
    {
        return match ($filter) {
            'week' => array_map(
                fn (int $offset): CarbonImmutable => $today->startOfWeek()->subWeeks(11 - $offset),
                range(0, 11),
            ),
            'month' => array_map(
                fn (int $offset): CarbonImmutable => $today->startOfMonth()->subMonths(11 - $offset),
                range(0, 11),
            ),
            default => array_map(
                fn (int $offset): CarbonImmutable => $today->startOfDay()->subDays(29 - $offset),
                range(0, 29),
            ),
        };
    }

    /**
     * @return array<string, int>
     */
    private function getCounts(
        string $model,
        CarbonImmutable $start,
        CarbonImmutable $end,
        string $filter,
    ): array {
        return ($model)::query()
            ->whereBetween('created_at', [$start, $end])
            ->get(['created_at'])
            ->groupBy(fn ($registration): string => $this->bucketKey(
                CarbonImmutable::parse($registration->created_at),
                $filter,
            ))
            ->map->count()
            ->all();
    }

    private function bucketKey(CarbonImmutable $date, string $filter): string
    {
        return match ($filter) {
            'week' => $date->startOfWeek()->toDateString(),
            'month' => $date->format('Y-m'),
            default => $date->toDateString(),
        };
    }

    private function labelFor(CarbonImmutable $date, string $filter): string
    {
        return match ($filter) {
            'week' => 'Week of ' . $date->format('M j'),
            'month' => $date->format('M Y'),
            default => $date->format('M j'),
        };
    }
}
