<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;

class ActivityLogExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = Activity::query()->orderBy('created_at', 'desc');

        if ($this->request->filled('subject_type')) $query->where('subject_type', $this->request->subject_type);
        if ($this->request->filled('event'))        $query->where('event', $this->request->event);
        if ($this->request->filled('causer_id'))    $query->where('causer_id', $this->request->causer_id);
        if ($this->request->filled('log_name'))     $query->where('log_name', $this->request->log_name);
        if ($this->request->filled('date_from'))    $query->where('created_at', '>=', $this->request->date_from . ' 00:00:00');
        if ($this->request->filled('date_to'))      $query->where('created_at', '<=', $this->request->date_to . ' 23:59:59');
        if ($this->request->filled('search')) {
            $s = $this->request->search;
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                  ->orWhere('properties', 'like', "%{$s}%");
            });
        }

        return $query->limit(5000)->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Date & Time',
            'Log Name',
            'Event',
            'Model',
            'Model ID',
            'Description',
            'Admin ID',
            'Changed Fields',
            'Old Values',
            'New Values',
        ];
    }

    public function map($activity): array
    {
        $properties = $activity->properties ? $activity->properties->toArray() : [];
        $old = $properties['old'] ?? [];
        $attributes = $properties['attributes'] ?? [];

        // Build changed fields summary
        $changedFields = array_keys(array_merge($old, $attributes));

        return [
            $activity->id,
            $activity->created_at?->format('Y-m-d H:i:s'),
            $activity->log_name,
            $activity->event,
            $activity->subject_type ? class_basename($activity->subject_type) : '',
            $activity->subject_id,
            $activity->description,
            $activity->causer_id,
            implode(', ', $changedFields),
            $old ? json_encode($old, JSON_UNESCAPED_UNICODE) : '',
            $attributes ? json_encode($attributes, JSON_UNESCAPED_UNICODE) : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DC2626'],
                ],
            ],
        ];
    }
}
