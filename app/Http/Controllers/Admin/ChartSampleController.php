<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChartSampleController extends Controller
{
    public function index(Request $request)
    {
        $tabs = [
            // ── Core Charts ──
            'line'         => ['label' => 'Line',         'icon' => 'fas fa-chart-line',      'group' => 'Core'],
            'bar'          => ['label' => 'Bar',          'icon' => 'fas fa-chart-bar',       'group' => 'Core'],
            'pie'          => ['label' => 'Pie',          'icon' => 'fas fa-chart-pie',       'group' => 'Core'],
            'scatter'      => ['label' => 'Scatter',      'icon' => 'fas fa-braille',         'group' => 'Core'],
            'map'          => ['label' => 'GEO/Map',      'icon' => 'fas fa-globe-asia',      'group' => 'Core'],
            'candlestick'  => ['label' => 'Candlestick',  'icon' => 'fas fa-chart-area',      'group' => 'Core'],
            'radar'        => ['label' => 'Radar',        'icon' => 'fas fa-satellite-dish',  'group' => 'Core'],
            'boxplot'      => ['label' => 'Boxplot',      'icon' => 'fas fa-box',             'group' => 'Core'],
            'heatmap'      => ['label' => 'Heatmap',      'icon' => 'fas fa-th',              'group' => 'Core'],
            'graph'        => ['label' => 'Graph',        'icon' => 'fas fa-project-diagram', 'group' => 'Core'],

            // ── Hierarchy ──
            'tree'         => ['label' => 'Tree',         'icon' => 'fas fa-sitemap',         'group' => 'Hierarchy'],
            'treemap'      => ['label' => 'Treemap',      'icon' => 'fas fa-th-large',        'group' => 'Hierarchy'],
            'sunburst'     => ['label' => 'Sunburst',     'icon' => 'fas fa-sun',             'group' => 'Hierarchy'],

            // ── Relation / Flow ──
            'parallel'     => ['label' => 'Parallel',     'icon' => 'fas fa-grip-lines',      'group' => 'Relation'],
            'sankey'       => ['label' => 'Sankey',       'icon' => 'fas fa-stream',          'group' => 'Relation'],
            'funnel'       => ['label' => 'Funnel',       'icon' => 'fas fa-filter',          'group' => 'Relation'],
            'gauge'        => ['label' => 'Gauge',        'icon' => 'fas fa-tachometer-alt',  'group' => 'Relation'],

            // ── Specialized ──
            'pictorialBar' => ['label' => 'PictorialBar', 'icon' => 'fas fa-shapes',          'group' => 'Specialized'],
            'themeRiver'   => ['label' => 'ThemeRiver',   'icon' => 'fas fa-water',           'group' => 'Specialized'],
            'calendar'     => ['label' => 'Calendar',     'icon' => 'fas fa-calendar-alt',    'group' => 'Specialized'],
            'matrix'       => ['label' => 'Matrix',       'icon' => 'fas fa-border-all',      'group' => 'Specialized'],
            'chord'        => ['label' => 'Chord',        'icon' => 'fas fa-circle-notch',    'group' => 'Specialized'],

            // ── Utility / Advanced ──
            'custom'       => ['label' => 'Custom',       'icon' => 'fas fa-code',            'group' => 'Utility'],
            'dataset'      => ['label' => 'Dataset',      'icon' => 'fas fa-database',        'group' => 'Utility'],
            'dataZoom'     => ['label' => 'DataZoom',     'icon' => 'fas fa-search-plus',     'group' => 'Utility'],
            'graphic'      => ['label' => 'Graphic',      'icon' => 'fas fa-draw-polygon',    'group' => 'Utility'],
            'rich'         => ['label' => 'Rich Text',    'icon' => 'fas fa-font',            'group' => 'Utility'],
            'gl'           => ['label' => '3D / GL',      'icon' => 'fas fa-cube',            'group' => 'Utility'],
        ];

        $activeTab = $request->get('tab', 'line');
        if (!isset($tabs[$activeTab])) $activeTab = 'line';

        // Count charts per tab for badge display
        $chartCounts = $this->getChartCounts();

        return view('admin.pages.charts.index', compact('tabs', 'activeTab', 'chartCounts'));
    }

    private function getChartCounts(): array
    {
        return [
            'line' => 15, 'bar' => 14, 'pie' => 10, 'scatter' => 8,
            'map' => 5,  'candlestick' => 5, 'radar' => 5, 'boxplot' => 4,
            'heatmap' => 5, 'graph' => 7, 'tree' => 7, 'treemap' => 5,
            'sunburst' => 5, 'parallel' => 4, 'sankey' => 5, 'funnel' => 4,
            'gauge' => 8, 'pictorialBar' => 5, 'themeRiver' => 2,
            'calendar' => 5, 'matrix' => 4, 'chord' => 4,  'custom' => 6,
            'dataset' => 5, 'dataZoom' => 4, 'graphic' => 4,  'rich' => 3,
            'gl' => 6,
            // Total: 164 chart samples across 28 categories
        ];
    }
}
