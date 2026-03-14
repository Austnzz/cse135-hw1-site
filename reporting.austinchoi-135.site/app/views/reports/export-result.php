<div class="card">
    <h2>Report Export Complete</h2>

    <div class="notice-info">
        Your PDF export was generated successfully.
    </div>

    <p><strong>Report:</strong> <?= htmlspecialchars($report['title'] ?? 'Unknown Report') ?></p>
    <p><strong>Saved File:</strong> <span class="inline-code"><?= htmlspecialchars($exportFilename ?? '') ?></span></p>

    <div class="actions">
        <a class="button-link" href="<?= htmlspecialchars($downloadUrl ?? '#') ?>" target="_blank" rel="noopener noreferrer">
            Open Exported PDF
        </a>
        <a class="button-link" href="/saved-reports/<?= urlencode($report['slug'] ?? '') ?>">
            Return to Report
        </a>
    </div>
</div>