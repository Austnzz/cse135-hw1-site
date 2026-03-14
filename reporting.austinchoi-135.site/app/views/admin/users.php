<?php
$dashboardUsers = is_array($users ?? null) ? $users : [];

$totalUsers = count($dashboardUsers);
$superAdminCount = 0;
$analystCount = 0;
$viewerCount = 0;
$recentLoginCount = 0;

foreach ($dashboardUsers as $user) {
    $role = (string) ($user['role'] ?? '');

    if ($role === 'super_admin') {
        $superAdminCount++;
    } elseif ($role === 'analyst') {
        $analystCount++;
    } elseif ($role === 'viewer') {
        $viewerCount++;
    }

    if (!empty($user['last_login'])) {
        $recentLoginCount++;
    }
}

function displayRole(?string $role): string
{
    $role = trim((string) $role);
    if ($role === '') {
        return 'Unknown';
    }

    return ucwords(str_replace('_', ' ', $role));
}

function displaySections(?string $sections): string
{
    $sections = trim((string) $sections);
    if ($sections === '') {
        return 'All / Not Specified';
    }

    return $sections;
}

function displayTimestamp(?string $value): string
{
    $value = trim((string) $value);
    if ($value === '') {
        return '—';
    }

    return $value;
}
?>

<div class="section-stack">
    <section class="page-intro">
        <span class="page-kicker">Admin</span>
        <h1>User Management</h1>
        <p>
            This section is restricted to super administrators. It provides a reviewable view of configured dashboard users, assigned roles, section scope, and login activity across the reporting platform.
        </p>

        <div class="actions">
            <a class="button-link button-secondary" href="/reports">Back to Overview</a>
            <a class="button-link" href="/saved-reports">Open Saved Reports</a>
        </div>
    </section>

    <section class="kpi-grid">
        <article class="kpi-card">
            <p class="kpi-label">Total Users</p>
            <p class="kpi-value"><?= htmlspecialchars((string) $totalUsers) ?></p>
            <p class="kpi-note">Configured dashboard accounts currently available.</p>
        </article>

        <article class="kpi-card">
            <p class="kpi-label">Role Mix</p>
            <p class="kpi-value"><?= htmlspecialchars((string) $superAdminCount) ?> / <?= htmlspecialchars((string) $analystCount) ?> / <?= htmlspecialchars((string) $viewerCount) ?></p>
            <p class="kpi-note">Super Admin / Analyst / Viewer account counts.</p>
        </article>

        <article class="kpi-card">
            <p class="kpi-label">Users With Login Activity</p>
            <p class="kpi-value"><?= htmlspecialchars((string) $recentLoginCount) ?></p>
            <p class="kpi-note">Accounts that currently show a recorded last login.</p>
        </article>
    </section>

    <section class="card">
        <div class="split-header">
            <div class="stack-xs">
                <h2 class="section-title">Administrative Context</h2>
                <p class="section-copy">
                    This page supports role review and basic account oversight. It is intentionally read-focused and lightweight, which keeps the administrative surface clear while still demonstrating authorization boundaries for the assignment.
                </p>
            </div>
        </div>

        <div class="pill-row">
            <span class="meta-pill">Super Admin Only</span>
            <span class="meta-pill">Role-Based Access</span>
            <span class="meta-pill">Users Table</span>
            <span class="meta-pill is-accent">Restricted Surface</span>
        </div>
    </section>

    <section class="card">
        <div class="split-header">
            <div class="stack-xs">
                <h2 class="section-title">Configured Dashboard Users</h2>
                <p class="section-copy">
                    Review current usernames, display names, roles, allowed sections, and account timestamps. This table acts as the detailed evidence layer beneath the higher-level summary above.
                </p>
            </div>
        </div>

        <?php if (empty($dashboardUsers)): ?>
            <div class="notice-info">
                No dashboard users were found.
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Display Name</th>
                            <th>Role</th>
                            <th>Allowed Sections</th>
                            <th>Created At</th>
                            <th>Last Login</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dashboardUsers as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars((string) ($user['id'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string) ($user['username'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string) ($user['display_name'] ?? '')) ?></td>
                                <td><?= htmlspecialchars(displayRole($user['role'] ?? '')) ?></td>
                                <td><?= htmlspecialchars(displaySections($user['allowed_sections'] ?? '')) ?></td>
                                <td><?= htmlspecialchars(displayTimestamp($user['created_at'] ?? '')) ?></td>
                                <td><?= htmlspecialchars(displayTimestamp($user['last_login'] ?? '')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>