<?php
$reportTitle = $report['title'] ?? 'Saved Report';
$reportCategory = $report['category'] ?? 'unknown';
$reportSection = $report['section'] ?? 'unknown';
$reportChartType = $report['chart_type'] ?? 'bar';
$reportAuthor = $report['author_name'] ?? 'Unknown';
$isPublished = !empty($report['is_published']);

$cleanChartLabels = is_array($chartLabels ?? null) ? $chartLabels : [];
$cleanChartValues = is_array($chartValues ?? null) ? $chartValues : [];
$cleanTableRows = is_array($tableRows ?? null) ? $tableRows : [];

$cleanTrendLabels = is_array($trendLabels ?? null) ? $trendLabels : [];
$cleanTrendValues = is_array($trendValues ?? null) ? $trendValues : [];
$cleanRecentSessions = is_array($recentSessions ?? null) ? $recentSessions : [];

$totalChartValue = 0;
$topLabel = 'N/A';
$topValue = 0;

if (!empty($cleanChartValues)) {
    $totalChartValue = array_sum(array_map('intval', $cleanChartValues));
    $topIndex = array_keys($cleanChartValues, max($cleanChartValues))[0];
    $topLabel = $cleanChartLabels[$topIndex] ?? 'N/A';
    $topValue = (int) ($cleanChartValues[$topIndex] ?? 0);
}

$distinctLabelCount = count(array_unique($cleanChartLabels));

$publishedLabel = $isPublished ? 'Published' : 'Draft';

$categoryDisplay = ucwords(str_replace(['_', '-'], ' ', (string) $reportCategory));
$sectionDisplay = ucwords(str_replace(['_', '-'], ' ', (string) $reportSection));
$chartTypeDisplay = ucwords(str_replace(['_', '-'], ' ', (string) $reportChartType));

$showSectionPill = $sectionDisplay !== '' && strcasecmp($sectionDisplay, $categoryDisplay) !== 0;

$summaryText = trim((string) ($summaryText ?? ''));
$tableIntro = trim((string) ($tableIntro ?? ''));
$commentary = trim((string) ($report['commentary'] ?? ''));

$whyThisMatters = '';
$suggestedNextAction = '';

switch ($reportCategory) {
    case 'behavior':
        $whyThisMatters = 'Behavior reporting helps identify how people actually move through the site, which interactions are meaningful, and where engagement patterns appear strongest or weakest.';
        $suggestedNextAction = 'Review the most common event types and most active pages, then decide whether those interactions reflect the intended user journey or whether certain flows need simplification.';
        break;

    case 'errors':
        $whyThisMatters = 'Error reporting highlights where reliability breaks down. Repeated failures on specific pages can directly affect trust, completion rates, and the usefulness of analytics itself.';
        $suggestedNextAction = 'Prioritize the pages with the highest concentration of errors first, confirm reproducibility, and reduce recurring failures before expanding instrumentation further.';
        break;

    case 'performance':
        $whyThisMatters = 'Performance reporting helps show whether the observed experience is fast enough to support the intended workflow. Slow pages can reduce usability and distort downstream engagement signals.';
        $suggestedNextAction = 'Inspect the worst-performing or most frequently represented pages first, then determine whether front-end weight, asset loading, or collection overhead is contributing to slower interactions.';
        break;

    default:
        $whyThisMatters = 'This report summarizes a portion of the captured analytics data and turns it into a more reviewable, decision-oriented view.';
        $suggestedNextAction = 'Use the summary, chart, and supporting table together to identify the most important pattern before deciding what to investigate next.';
        break;
}

function formatPageLabel(?string $page): string
{
    if ($page === null || $page === '') {
        return '—';
    }

    return str_replace('https://test.austinchoi-135.site/', '', $page);
}
?>

<div class="section-stack">
    <section class="page-intro">
        <span class="page-kicker"><?= htmlspecialchars($categoryDisplay) ?> Report</span>
        <h1><?= htmlspecialchars($reportTitle) ?></h1>
        <p>
            <?= htmlspecialchars($summaryText !== '' ? $summaryText : 'A curated saved report with charted analytics, supporting evidence, and analyst interpretation.') ?>
        </p>

        <div class="actions">
            <a class="button-link" href="/export/report/<?= urlencode($report['slug']) ?>">
                Export as PDF
            </a>
            <a class="button-link button-secondary" href="/saved-reports">
                Back to Saved Reports
            </a>
        </div>
    </section>

    <section class="card">
        <div class="split-header">
            <div class="stack-xs">
                <h2 class="section-title">Report Snapshot</h2>
                <p class="section-copy">
                    High-level metadata describing this saved report and how it is presented.
                </p>
            </div>
        </div>

        <div class="pill-row">
            <span class="meta-pill"><?= htmlspecialchars($categoryDisplay) ?></span>

            <?php if ($showSectionPill): ?>
                <span class="meta-pill"><?= htmlspecialchars($sectionDisplay) ?></span>
            <?php endif; ?>

            <span class="meta-pill"><?= htmlspecialchars($chartTypeDisplay) ?> chart</span>
            <span class="meta-pill"><?= htmlspecialchars($reportAuthor) ?></span>
            <span class="meta-pill is-accent"><?= htmlspecialchars($publishedLabel) ?></span>
        </div>
    </section>

    <section class="kpi-grid">
        <article class="kpi-card">
            <p class="kpi-label">Total Measured Value</p>
            <p class="kpi-value"><?= htmlspecialchars((string) $totalChartValue) ?></p>
            <p class="kpi-note">Combined value across the current charted dataset.</p>
        </article>

        <article class="kpi-card">
            <p class="kpi-label">Top Dimension</p>
            <p class="kpi-value"><?= htmlspecialchars((string) $topValue) ?></p>
            <p class="kpi-note"><?= htmlspecialchars((string) $topLabel) ?></p>
        </article>

        <article class="kpi-card">
            <p class="kpi-label">Distinct Labels</p>
            <p class="kpi-value"><?= htmlspecialchars((string) $distinctLabelCount) ?></p>
            <p class="kpi-note">Unique chart groupings represented in this report.</p>
        </article>
    </section>

    <section class="card">
        <div class="split-header">
            <div class="stack-xs">
                <h2 class="section-title">Visualization</h2>
                <p class="section-copy">
                    Summary-first view of the most important grouped values in this report.
                </p>
            </div>
        </div>

        <?php if (empty($cleanChartLabels)): ?>
            <div class="notice-info">
                No chart data is available for this report yet.
            </div>
        <?php else: ?>
            <div class="chart-shell">
                <canvas id="savedReportChart"></canvas>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
            const savedReportChartLabels = <?= json_encode($cleanChartLabels) ?>;
            const savedReportChartValues = <?= json_encode($cleanChartValues) ?>;

            const savedReportCtx = document.getElementById('savedReportChart').getContext('2d');

            new Chart(savedReportCtx, {
                type: <?= json_encode($reportChartType) ?>,
                data: {
                    labels: savedReportChartLabels,
                    datasets: [{
                        label: <?= json_encode($chartDatasetLabel ?? 'Report Data') ?>,
                        data: savedReportChartValues,
                        borderWidth: 1.5,
                        tension: 0.25
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: true
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
                                text: 'Count'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Dimension'
                            }
                        }
                    }
                }
            });
            </script>
        <?php endif; ?>
    </section>
    <?php if ($reportCategory === 'behavior'): ?>
        <section class="card">
            <div class="split-header">
                <div class="stack-xs">
                    <h2 class="section-title"><?= htmlspecialchars($trendTitle !== '' ? $trendTitle : 'Behavior Over Time') ?></h2>
                    <p class="section-copy">
                        <?= htmlspecialchars($trendIntro !== '' ? $trendIntro : 'Recent meaningful behavior activity grouped over time.') ?>
                    </p>
                </div>
            </div>

            <?php if (empty($cleanTrendLabels)): ?>
                <div class="notice-info">
                    No trend data is available for this report yet.
                </div>
            <?php else: ?>
                <div class="chart-shell">
                    <canvas id="behaviorTrendChart"></canvas>
                </div>

                <script>
                const behaviorTrendLabels = <?= json_encode($cleanTrendLabels) ?>;
                const behaviorTrendValues = <?= json_encode($cleanTrendValues) ?>;

                const behaviorTrendCtx = document.getElementById('behaviorTrendChart').getContext('2d');

                new Chart(behaviorTrendCtx, {
                    type: 'line',
                    data: {
                        labels: behaviorTrendLabels,
                        datasets: [{
                            label: 'Meaningful Events Over Time',
                            data: behaviorTrendValues,
                            borderWidth: 2,
                            tension: 0.3,
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: true
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
                                    text: 'Event Count'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Time Bucket'
                                }
                            }
                        }
                    }
                });
                </script>
            <?php endif; ?>
        </section>
    <?php endif; ?>
    
    <?php if ($reportCategory === 'behavior'): ?>
        <section class="card">
            <div class="split-header">
                <div class="stack-xs">
                    <h2 class="section-title">Recent Session Summaries</h2>
                    <p class="section-copy">
                         A compact session-level summary of recent user activity that gives behavioral context without requiring full session playback.
                    </p>
                </div>
            </div>

            <?php if (empty($cleanRecentSessions)): ?>
                <div class="notice-info">
                    No recent session summaries are available for this report.
                </div>
            <?php else: ?>
                <div class="session-summary-grid">
                    <?php foreach ($cleanRecentSessions as $session): ?>
                        <article class="session-summary-card">
                            <div class="stack-sm">
                                <div class="pill-row">
                                    <span class="meta-pill">Events: <?= htmlspecialchars((string) ($session['event_count'] ?? 0)) ?></span>
                                    <span class="meta-pill">Pages: <?= htmlspecialchars((string) ($session['distinct_pages'] ?? 0)) ?></span>

                                    <?php if (!empty($session['error_count'])): ?>
                                        <span class="meta-pill">Errors: <?= htmlspecialchars((string) $session['error_count']) ?></span>
                                    <?php endif; ?>

                                    <?php if (!empty($session['performance_count'])): ?>
                                        <span class="meta-pill">Performance: <?= htmlspecialchars((string) $session['performance_count']) ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="stack-xs">
                                    <?php
                                    $rawSessionId = (string) ($session['session_id'] ?? 'Unknown');
                                    $displaySessionId = strlen($rawSessionId) > 16 ? substr($rawSessionId, 0, 16) . '…' : $rawSessionId;
                                    ?>
                                    <h3 class="session-summary-title">
                                        Session <?= htmlspecialchars($displaySessionId) ?>
                                    </h3>
                                    <p class="session-summary-id">Full ID: <span class="inline-code"><?= htmlspecialchars($rawSessionId) ?></span></p>
                                    <p class="session-summary-copy">
                                        Sample page: <?= htmlspecialchars(formatPageLabel($session['sample_page'] ?? '')) ?>
                                    </p>
                                </div>

                                <div class="session-summary-meta">
                                    <span><strong>First Seen:</strong> <?= htmlspecialchars((string) ($session['first_seen'] ?? '—')) ?></span>
                                    <span><strong>Last Seen:</strong> <?= htmlspecialchars((string) ($session['last_seen'] ?? '—')) ?></span>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <section class="card">
        <div class="split-header">
            <div class="stack-xs">
                <h2 class="section-title">Analyst Commentary</h2>
                <p class="section-copy">
                    Interpretation of the report from a reporting and decision-making perspective.
                </p>
            </div>
        </div>

        <?php if ($commentary === ''): ?>
            <div class="notice-info">
                No analyst commentary has been added for this report yet.
            </div>
        <?php else: ?>
            <p><?= nl2br(htmlspecialchars($commentary)) ?></p>
        <?php endif; ?>
    </section>

    <section class="card">
        <div class="split-header">
            <div class="stack-xs">
                <h2 class="section-title">Supporting Table</h2>
                <p class="section-copy">
                    <?= htmlspecialchars($tableIntro !== '' ? $tableIntro : 'Detailed rows that support and contextualize the summarized chart data.') ?>
                </p>
            </div>
        </div>

        <?php if (empty($cleanTableRows)): ?>
            <div class="notice-info">
                No supporting table data is available for this report.
            </div>
        <?php else: ?>
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
                        <?php foreach ($cleanTableRows as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars((string) ($row['id'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string) ($row['received_at'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string) ($row['event_type'] ?? '')) ?></td>
                                <td><?= htmlspecialchars(formatPageLabel($row['page'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string) ($row['session_id'] ?? '')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="card card-muted">
        <div class="split-header">
            <div class="stack-xs">
                <h2 class="section-title">Key Takeaways</h2>
                <p class="section-copy">
                    A concise interpretation of the main pattern shown in this report and the most reasonable follow-up focus.
                </p>
            </div>
        </div>

        <div class="stack-md">
            <div>
                <h3>Interpretation</h3>
                <p><?= htmlspecialchars($whyThisMatters) ?></p>
            </div>

            <div>
                <h3>Recommended Focus</h3>
                <p><?= htmlspecialchars($suggestedNextAction) ?></p>
            </div>
        </div>
    </section>
</div>