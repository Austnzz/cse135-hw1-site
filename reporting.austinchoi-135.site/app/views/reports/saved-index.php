<?php
$reports = is_array($savedReports ?? null) ? $savedReports : [];

function reportDisplayValue(?string $value): string
{
    $value = trim((string) $value);
    if ($value === '') {
        return 'Unknown';
    }

    return ucwords(str_replace(['_', '-'], ' ', $value));
}

function previewText(?string $text, int $limit = 180): string
{
    $text = trim((string) $text);
    if ($text === '') {
        return 'No analyst commentary has been added for this saved report yet.';
    }

    if (mb_strlen($text) <= $limit) {
        return $text;
    }

    return rtrim(mb_substr($text, 0, $limit - 1)) . '…';
}
?>

<div class="section-stack">
    <section class="page-intro">
        <span class="page-kicker">Saved Reports</span>
        <h1>Curated Analytics Reports</h1>
        <p>
            Saved reports are the viewer-facing reporting layer of the platform. They package collected analytics into reviewable summaries with visualizations, supporting evidence, commentary, and export options.
        </p>
    </section>

    <?php if (empty($reports)): ?>
        <section class="card card-muted">
            <div class="stack-sm">
                <h2 class="section-title">No Saved Reports Yet</h2>
                <p class="section-copy">
                    There are currently no curated reports available to review. Once reports are published, they will appear here for browsing, export, and role-appropriate access.
                </p>
            </div>
        </section>
    <?php else: ?>
        <section class="card">
            <div class="split-header">
                <div class="stack-xs">
                    <h2 class="section-title">Available Reports</h2>
                    <p class="section-copy">
                        Browse the current set of published or available saved reports, then open a report to review its chart, evidence table, commentary, and export flow.
                    </p>
                </div>
            </div>

            <div class="report-card-grid">
                <?php foreach ($reports as $report): ?>
                    <?php
                    $title = $report['title'] ?? 'Untitled Report';
                    $slug = $report['slug'] ?? '';
                    $category = reportDisplayValue($report['category'] ?? '');
                    $section = reportDisplayValue($report['section'] ?? '');
                    $chartType = reportDisplayValue($report['chart_type'] ?? '');
                    $author = trim((string) ($report['author_name'] ?? ''));
                    $author = $author !== '' ? $author : 'Unknown';
                    $isPublished = !empty($report['is_published']);
                    $commentaryPreview = previewText($report['commentary'] ?? '');

                    $showSectionPill = strcasecmp($section, $category) !== 0;
                    ?>
                    <article class="report-card">
                        <div class="stack-md">
                            <div class="stack-xs">
                                <div class="pill-row">
                                    <span class="meta-pill"><?= htmlspecialchars($category) ?></span>

                                    <?php if ($showSectionPill): ?>
                                        <span class="meta-pill"><?= htmlspecialchars($section) ?></span>
                                    <?php endif; ?>

                                    <span class="meta-pill"><?= htmlspecialchars($chartType) ?> chart</span>
                                    <span class="meta-pill is-accent">
                                        <?= $isPublished ? 'Published' : 'Draft' ?>
                                    </span>
                                </div>

                                <h3 class="report-card-title">
                                    <a href="/saved-reports/<?= urlencode($slug) ?>">
                                        <?= htmlspecialchars($title) ?>
                                    </a>
                                </h3>

                                <p class="report-card-copy">
                                    <?= htmlspecialchars($commentaryPreview) ?>
                                </p>
                            </div>

                            <div class="report-card-meta">
                                <span><strong>Author:</strong> <?= htmlspecialchars($author) ?></span>
                                <span><strong>Slug:</strong> <span class="inline-code"><?= htmlspecialchars($slug) ?></span></span>
                            </div>

                            <div class="actions">
                                <a class="button-link" href="/saved-reports/<?= urlencode($slug) ?>">
                                    Open Report
                                </a>
                                <a class="button-link button-secondary" href="/export/report/<?= urlencode($slug) ?>">
                                    Export PDF
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="card card-muted">
            <div class="split-header">
                <div class="stack-xs">
                    <h2 class="section-title">How to Use This Page</h2>
                    <p class="section-copy">
                        Open a saved report to review the summary, chart, commentary, and supporting evidence. Use export when you want a more portable view for grading, sharing, or record keeping.
                    </p>
                </div>
            </div>
        </section>
    <?php endif; ?>
</div>