<?php
$cleanEvents = is_array($events ?? null) ? $events : [];
$cleanEventCounts = is_array($eventCounts ?? null) ? $eventCounts : [];

$chartLabels = array_map(
    fn($row) => (string) ($row['event_type'] ?? 'unknown'),
    $cleanEventCounts
);

$chartValues = array_map(
    fn($row) => (int) ($row['total'] ?? 0),
    $cleanEventCounts
);

$totalRecentEvents = count($cleanEvents);
$totalGroupedEvents = array_sum($chartValues);
$distinctEventTypes = count($cleanEventCounts);

$topEventType = 'N/A';
$topEventTypeCount = 0;

if (!empty($cleanEventCounts)) {
    $topRow = $cleanEventCounts[0];
    foreach ($cleanEventCounts as $row) {
        $rowTotal = (int) ($row['total'] ?? 0);
        if ($rowTotal > $topEventTypeCount) {
            $topEventTypeCount = $rowTotal;
            $topEventType = (string) ($row['event_type'] ?? 'N/A');
        }
    }
}

function overviewDisplayPage(?string $page): string
{
    $page = (string) $page;
    if ($page === '') {
        return '—';
    }

    return str_replace('https://test.austinchoi-135.site/', '', $page);
}
?>

<div class="section-stack">
    <section class="page-intro">
        <span class="page-kicker">Dashboard Overview</span>
        <h1>Analytics Overview</h1>
        <p>
            A summary-first view of the current analytics platform, highlighting collected event volume, grouped event activity, and the most recent evidence flowing into the reporting layer.
        </p>

        <div class="actions">
            <a class="button-link" href="/saved-reports">Browse Saved Reports</a>
            <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'super_admin'): ?>
                <a class="button-link button-secondary" href="/admin/users">Manage Users</a>
            <?php endif; ?>
        </div>
    </section>

    <section class="kpi-grid">
        <article class="kpi-card">
            <p class="kpi-label">Recent Rows Shown</p>
            <p class="kpi-value"><?= htmlspecialchars((string) $totalRecentEvents) ?></p>
            <p class="kpi-note">Number of recent event rows currently displayed below.</p>
        </article>

        <article class="kpi-card">
            <p class="kpi-label">Distinct Event Types</p>
            <p class="kpi-value"><?= htmlspecialchars((string) $distinctEventTypes) ?></p>
            <p class="kpi-note">Unique event categories represented in the grouped chart.</p>
        </article>

        <article class="kpi-card">
            <p class="kpi-label">Top Event Type</p>
            <p class="kpi-value"><?= htmlspecialchars((string) $topEventTypeCount) ?></p>
            <p class="kpi-note"><?= htmlspecialchars($topEventType) ?></p>
        </article>
    </section>

    <section class="card">
        <div class="split-header">
            <div class="stack-xs">
                <h2 class="section-title">Dashboard Context</h2>
                <p class="section-copy">
                    You are signed in as <strong><?= htmlspecialchars($username) ?></strong>. This overview page is intended to provide a high-level operational summary before moving into curated saved reports for deeper interpretation and export.
                </p>
            </div>
        </div>

        <div class="pill-row">
            <span class="meta-pill">Overview Dashboard</span>
            <span class="meta-pill">Session Protected</span>
            <span class="meta-pill">
                <?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) ($_SESSION['role'] ?? 'Unknown')))) ?>
            </span>
            <span class="meta-pill is-accent">Live Analytics Data</span>
        </div>
    </section>

    <section class="card">
        <div class="split-header">
            <div class="stack-xs">
                <h2 class="section-title">Event Counts by Event Type</h2>
                <p class="section-copy">
                    This chart summarizes grouped event activity across the currently collected analytics data. It provides a quick comparison of which event categories are appearing most frequently.
                </p>
            </div>
        </div>

        <?php if (empty($chartLabels)): ?>
            <div class="notice-info">
                No grouped event data is available for this dashboard yet.
            </div>
        <?php else: ?>
            <div class="chart-shell">
                <canvas id="eventTypeChart"></canvas>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
            const chartLabels = <?= json_encode($chartLabels, JSON_UNESCAPED_SLASHES) ?>;
            const chartValues = <?= json_encode($chartValues, JSON_UNESCAPED_SLASHES) ?>;

            const ctx = document.getElementById('eventTypeChart').getContext('2d');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Event Count',
                        data: chartValues,
                        borderWidth: 1.5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Events'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Event Type'
                            }
                        }
                    }
                }
            });
            </script>
        <?php endif; ?>
    </section>

    <section class="card">
        <div class="split-header">
            <div class="stack-xs">
                <h2 class="section-title">Recent Event Data</h2>
                <p class="section-copy">
                    Showing the most recent <?= htmlspecialchars((string) $totalRecentEvents) ?> events returned from the analytics datastore. This table provides supporting evidence for the higher-level dashboard view.
                </p>
            </div>
        </div>

        <?php if (!empty($cleanEvents)): ?>
            <div class="table-wrap">
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
                        <?php foreach ($cleanEvents as $event): ?>
                            <tr>
                                <td><?= htmlspecialchars((string) ($event['id'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string) ($event['received_at'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string) ($event['event_type'] ?? '')) ?></td>
                                <td><?= htmlspecialchars(overviewDisplayPage($event['page'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string) ($event['session_id'] ?? '')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="notice-info">
                No event rows were returned from the database.
            </div>
        <?php endif; ?>
    </section>

    <section class="card card-muted">
        <div class="split-header">
            <div class="stack-xs">
                <h2 class="section-title">What to Do Next</h2>
                <p class="section-copy">
                    Use this overview to identify which event categories deserve closer inspection, then move into saved reports for more decision-oriented views with commentary and export support.
                </p>
            </div>
        </div>

        <div class="actions">
            <a class="button-link" href="/saved-reports">Open Saved Reports</a>
        </div>
    </section>
</div>