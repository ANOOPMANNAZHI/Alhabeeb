<?php

namespace Modules\Maintenance\Exports;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Modules\Maintenance\Services\Inspection;

/** The four inspection reports, one sheet each. */
class InspectionReportExport implements WithMultipleSheets, Export
{
    protected $d;

    public function __construct(array $data)
    {
        $this->d = $data;
    }

    private function name($id)
    {
        return isset($this->d['names'][$id]) ? $this->d['names'][$id] : 'User ' . $id;
    }

    public function sheets(): array
    {
        $d = $this->d;
        $title = 'Period: ' . $d['period'];

        $c = [[$title], ['Executive', 'Month', 'Monthly visits due', 'Completed', 'Compliance %']];
        foreach ($d['compliance']['rows'] as $e => $months) {
            foreach ($d['compliance']['months'] as $m => $label) {
                $v = isset($months[$m]) ? $months[$m] : [0, 0];
                $c[] = [$this->name($e), $label, $v[0], $v[1], $v[0] ? round($v[1] / $v[0] * 100) : ''];
            }
        }

        $buckets = array_keys(\Modules\Maintenance\Services\InspectionReports::AGE_BUCKETS);
        $a = [['Unclosed issues as on ' . date('d/m/Y') . ' (includes resolved, awaiting verification)'], array_merge(['Priority'], $buckets, ['Total'])];
        foreach ($d['aging'] as $p => $row) {
            $a[] = array_merge([Inspection::PRIORITIES[$p]], array_values($row), [array_sum($row)]);
        }

        $m = [[$title . ' - all issues raised, including closed'], array_merge(['Building'], array_values($d['matrix']['categories']), ['Total'])];
        foreach ($d['matrix']['rows'] as $building => $cats) {
            $line = [$building];
            foreach ($d['matrix']['categories'] as $id => $name) {
                $line[] = isset($cats[$id]) ? $cats[$id] : 0;
            }
            $line[] = array_sum($cats);
            $m[] = $line;
        }

        $s = [[$title . ' - closed means verified by a supervisor'], ['Executive', 'Visits done', 'Issues raised', 'Issues closed', 'Avg. days to close']];
        foreach ($d['summary'] as $e => $v) {
            $s[] = [$this->name($e), $v[0], $v[1], $v[2], $v[3] === null ? '' : $v[3]];
        }

        return [
            new InspectionSheet('Visit compliance', $c),
            new InspectionSheet('Open issue aging', $a),
            new InspectionSheet('Building x category', $m),
            new InspectionSheet('Executive summary', $s),
        ];
    }
}

class InspectionSheet implements FromArray, WithTitle, ShouldAutoSize
{
    private $title;
    private $rows;

    public function __construct($title, array $rows)
    {
        $this->title = $title;
        $this->rows = $rows;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function title(): string
    {
        return $this->title;
    }
}
