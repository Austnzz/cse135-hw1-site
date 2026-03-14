<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($report['title']) ?></title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
        }

        h1, h2, h3 {
            margin-bottom: 6px;
        }

        p {
            margin: 6px 0 10px;
        }

        .section {
            margin-top: 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th, td {
            border: 1px solid #999;
            padding: 6px;
            vertical-align: top;
            text-align: left;
            font-size: 11px;
        }

        th {
            background: #eee;
        }

        .meta-table td:first-child {
            width: 180px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1><?= htmlspecialchars($report['title']) ?></h1>
    <p><?= htmlspecialchars($summaryText ?? '') ?></p>

    <div class="section">
        <h2>Report Details</h2>
        <table class="meta-table">
            <tr><td>Category</td><td><?= htmlspecialchars($report['category']) ?></td></tr>
            <tr><td>Section</td><td><?= htmlspecialchars($report['section']) ?></td></tr>
            <tr><td>Chart Type</td><td><?= htmlspecialchars($report['chart_type']) ?></td></tr>
            <tr><td>Author</td><td><?= htmlspecialchars($report['author_name'] ?? 'Unknown') ?></td></tr>
            <tr><td>Published</td><td><?= !empty($report['is_published']) ? 'Yes' : 'No' ?></td></tr>
        </table>
    </div>

    <div class="section">
        <h2>Analyst Commentary</h2>
        <p><?= nl2br(htmlspecialchars($report['commentary'] ?? '')) ?></p>
    </div>

    <div class="section">
        <h2>Visualization Data Summary</h2>
        <?php if (empty($chartLabels)): ?>
            <p>No chart data is available for this report.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Dimension</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($chartLabels as $index => $label): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $label) ?></td>
                            <td><?= htmlspecialchars((string) ($chartValues[$index] ?? '')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>Supporting Table</h2>
        <p><?= htmlspecialchars($tableIntro ?? '') ?></p>

        <?php if (empty($tableRows)): ?>
            <p>No supporting table data is available for this report.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Received At</th>
                        <th>Event Type</th>
                        <th>Page</th>
                        <th>Session ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tableRows as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $row['id']) ?></td>
                            <td><?= htmlspecialchars($row['received_at']) ?></td>
                            <td><?= htmlspecialchars($row['event_type']) ?></td>
                            <td><?= htmlspecialchars(str_replace('https://test.austinchoi-135.site/', '', $row['page'] ?? '')) ?></td>
                            <td><?= htmlspecialchars($row['session_id']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>