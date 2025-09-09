<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SWOT Analysis Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        .header .subtitle {
            color: #666;
            margin-top: 5px;
        }
        .report-info {
            margin-bottom: 20px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .report-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .report-info td {
            padding: 5px 10px;
            border: none;
        }
        .report-info .label {
            font-weight: bold;
            width: 150px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            border-left: 4px solid #007bff;
        }
        .stat-card.strengths { border-left-color: #28a745; }
        .stat-card.weaknesses { border-left-color: #dc3545; }
        .stat-card.opportunities { border-left-color: #ffc107; }
        .stat-card.threats { border-left-color: #6f42c1; }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .swot-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .swot-table th,
        .swot-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        .swot-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .swot-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .type-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            min-width: 20px;
        }
        .type-S { background-color: #d4edda; color: #155724; }
        .type-W { background-color: #f8d7da; color: #721c24; }
        .type-O { background-color: #fff3cd; color: #856404; }
        .type-T { background-color: #e2e3e5; color: #383d41; }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
        }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-approved { background-color: #d4edda; color: #155724; }
        .status-rejected { background-color: #f8d7da; color: #721c24; }
        .description {
            max-width: 300px;
            word-wrap: break-word;
        }
        .swot-matrix {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 30px 0;
        }
        .matrix-quadrant {
            border: 2px solid #ddd;
            padding: 15px;
            border-radius: 5px;
        }
        .matrix-quadrant h3 {
            margin: 0 0 15px 0;
            text-align: center;
            padding: 10px;
            border-radius: 3px;
        }
        .strengths-quadrant h3 { background-color: #d4edda; color: #155724; }
        .weaknesses-quadrant h3 { background-color: #f8d7da; color: #721c24; }
        .opportunities-quadrant h3 { background-color: #fff3cd; color: #856404; }
        .threats-quadrant h3 { background-color: #e2e3e5; color: #383d41; }
        .quadrant-item {
            margin-bottom: 10px;
            padding: 8px;
            background-color: #f9f9f9;
            border-radius: 3px;
            font-size: 11px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>SWOT Analysis Report</h1>
        <div class="subtitle">
            {{ $format === 'detailed' ? 'Detailed Analysis' : 'Summary Report' }}
        </div>
    </div>

    <div class="report-info">
        <table>
            <tr>
                <td class="label">College Filter:</td>
                <td>{{ $college ? $college->name : 'All Colleges' }}</td>
                <td class="label">Generated On:</td>
                <td>{{ $generated_at->format('F j, Y \a\t g:i A') }}</td>
            </tr>
            <tr>
                <td class="label">Area Filter:</td>
                <td>{{ $area ? $area->name : 'All Areas' }}</td>
                <td class="label">Generated By:</td>
                <td>{{ $generated_by }}</td>
            </tr>
            <tr>
                <td class="label">Type Filter:</td>
                <td>{{ $filters['type'] ? ['S' => 'Strengths', 'W' => 'Weaknesses', 'O' => 'Opportunities', 'T' => 'Threats'][$filters['type']] : 'All Types' }}</td>
                <td class="label">Total Records:</td>
                <td>{{ $swotEntries->count() }}</td>
            </tr>
            <tr>
                <td class="label">Status Filter:</td>
                <td>{{ $filters['status'] ? ucfirst($filters['status']) : 'All Statuses' }}</td>
                <td class="label">Date Range:</td>
                <td>
                    @if($filters['date_from'] || $filters['date_to'])
                        {{ $filters['date_from'] ?? 'Start' }} to {{ $filters['date_to'] ?? 'End' }}
                    @else
                        All Dates
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- Statistics Overview -->
    <div class="stats-grid">
        <div class="stat-card strengths">
            <div class="stat-number">{{ $stats['by_type']['S'] ?? 0 }}</div>
            <div class="stat-label">Strengths</div>
        </div>
        <div class="stat-card weaknesses">
            <div class="stat-number">{{ $stats['by_type']['W'] ?? 0 }}</div>
            <div class="stat-label">Weaknesses</div>
        </div>
        <div class="stat-card opportunities">
            <div class="stat-number">{{ $stats['by_type']['O'] ?? 0 }}</div>
            <div class="stat-label">Opportunities</div>
        </div>
        <div class="stat-card threats">
            <div class="stat-number">{{ $stats['by_type']['T'] ?? 0 }}</div>
            <div class="stat-label">Threats</div>
        </div>
    </div>

    @if($swotEntries->count() > 0)
        @if($format === 'detailed')
            <!-- SWOT Matrix View -->
            <div class="page-break"></div>
            <h2>SWOT Matrix Analysis</h2>
            
            <div class="swot-matrix">
                <div class="matrix-quadrant strengths-quadrant">
                    <h3>Strengths</h3>
                    @foreach($swotEntries->where('type', 'S')->take(10) as $entry)
                        <div class="quadrant-item">
                            <strong>{{ $entry->college->name }} - {{ $entry->area->name }}</strong><br>
                            {{ Str::limit($entry->description, 150) }}
                        </div>
                    @endforeach
                </div>
                
                <div class="matrix-quadrant weaknesses-quadrant">
                    <h3>Weaknesses</h3>
                    @foreach($swotEntries->where('type', 'W')->take(10) as $entry)
                        <div class="quadrant-item">
                            <strong>{{ $entry->college->name }} - {{ $entry->area->name }}</strong><br>
                            {{ Str::limit($entry->description, 150) }}
                        </div>
                    @endforeach
                </div>
                
                <div class="matrix-quadrant opportunities-quadrant">
                    <h3>Opportunities</h3>
                    @foreach($swotEntries->where('type', 'O')->take(10) as $entry)
                        <div class="quadrant-item">
                            <strong>{{ $entry->college->name }} - {{ $entry->area->name }}</strong><br>
                            {{ Str::limit($entry->description, 150) }}
                        </div>
                    @endforeach
                </div>
                
                <div class="matrix-quadrant threats-quadrant">
                    <h3>Threats</h3>
                    @foreach($swotEntries->where('type', 'T')->take(10) as $entry)
                        <div class="quadrant-item">
                            <strong>{{ $entry->college->name }} - {{ $entry->area->name }}</strong><br>
                            {{ Str::limit($entry->description, 150) }}
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Detailed Table -->
        <div class="{{ $format === 'detailed' ? 'page-break' : '' }}">
            <h2>{{ $format === 'detailed' ? 'Complete SWOT Entries' : 'SWOT Entries' }}</h2>
            
            <table class="swot-table">
                <thead>
                    <tr>
                        <th style="width: 8%">ID</th>
                        <th style="width: 15%">College</th>
                        <th style="width: 15%">Area</th>
                        <th style="width: 8%">Type</th>
                        <th style="width: 35%">Description</th>
                        <th style="width: 10%">Status</th>
                        <th style="width: 9%">Created Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($swotEntries as $entry)
                        <tr>
                            <td>{{ $entry->id }}</td>
                            <td>{{ $entry->college->name }}</td>
                            <td>{{ $entry->area->name }}</td>
                            <td>
                                <span class="type-badge type-{{ $entry->type }}">
                                    {{ $entry->type_label }}
                                </span>
                            </td>
                            <td class="description">
                                {{ $format === 'detailed' ? $entry->description : Str::limit($entry->description, 100) }}
                            </td>
                            <td>
                                <span class="status-badge status-{{ $entry->status }}">
                                    {{ ucfirst($entry->status) }}
                                </span>
                            </td>
                            <td>{{ $entry->created_at->format('M j, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($format === 'detailed')
            <!-- Status Analysis -->
            <div class="page-break">
                <h2>Status Analysis</h2>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number">{{ $stats['by_status']['pending'] ?? 0 }}</div>
                        <div class="stat-label">Pending Review</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">{{ $stats['by_status']['approved'] ?? 0 }}</div>
                        <div class="stat-label">Approved</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">{{ $stats['by_status']['rejected'] ?? 0 }}</div>
                        <div class="stat-label">Rejected</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">{{ number_format(($stats['by_status']['approved'] ?? 0) / max($stats['total'], 1) * 100, 1) }}%</div>
                        <div class="stat-label">Approval Rate</div>
                    </div>
                </div>

                @if($stats['by_college']->count() > 0)
                    <h3>College Performance</h3>
                    <table class="swot-table">
                        <thead>
                            <tr>
                                <th>College</th>
                                <th>Total Entries</th>
                                <th>Approved</th>
                                <th>Pending</th>
                                <th>Rejected</th>
                                <th>Approval Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stats['by_college'] as $collegeName => $count)
                                @php
                                    $collegeEntries = $swotEntries->where('college.name', $collegeName);
                                    $approved = $collegeEntries->where('status', 'approved')->count();
                                    $pending = $collegeEntries->where('status', 'pending')->count();
                                    $rejected = $collegeEntries->where('status', 'rejected')->count();
                                    $approvalRate = $count > 0 ? ($approved / $count) * 100 : 0;
                                @endphp
                                <tr>
                                    <td>{{ $collegeName }}</td>
                                    <td>{{ $count }}</td>
                                    <td>{{ $approved }}</td>
                                    <td>{{ $pending }}</td>
                                    <td>{{ $rejected }}</td>
                                    <td>{{ number_format($approvalRate, 1) }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @endif
    @else
        <div style="text-align: center; padding: 50px; color: #666;">
            <h3>No SWOT data available</h3>
            <p>No SWOT entries found for the selected criteria.</p>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated automatically by the Accreditation Management System.</p>
        <p>Report ID: {{ md5($generated_at . $generated_by) }} | Generated: {{ $generated_at->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>