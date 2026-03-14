<section class="card form-card">
    <div class="login-intro">
        <h2 class="login-title">Login</h2>
        <p class="login-subtitle">
            Sign in with one of the configured dashboard accounts to access protected reporting features.
        </p>
    </div>

    <?php
    $loginError = $errorMessage ?? $error ?? $message ?? null;
    ?>

    <?php if (!empty($loginError)): ?>
        <div class="notice-error">
            <?= htmlspecialchars($loginError) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="/login" class="stack-md">
        <div class="form-row">
            <label for="username">Username</label>
            <input
                type="text"
                id="username"
                name="username"
                required
                value="<?= htmlspecialchars($old['username'] ?? '') ?>"
            >
        </div>

        <div class="form-row">
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                required
            >
        </div>

        <div class="actions">
            <button type="submit">Log In</button>
        </div>
    </form>
</section>