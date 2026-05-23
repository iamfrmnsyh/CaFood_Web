<?php
$user = $_SESSION['user'] ?? null;
?>
<header>
    <nav style="padding: 14px 18px; display:flex; justify-content:space-between; align-items:center;">
        <div style="font-weight:800; color:#6C4CF1;">CaFood</div>
        <div>
            <?php if (!$user): ?>
                <a href="/public/login.php" style="margin-right:12px; text-decoration:none; color:#374151; font-weight:600;">Login</a>
                <a href="/public/register.php" style="text-decoration:none; color:#374151; font-weight:600;">Register</a>
            <?php else: ?>
                <span style="margin-right:14px; color:#374151; font-weight:700;">
                    Halo, <?= htmlspecialchars($user['name'] ?? 'User', ENT_QUOTES, 'UTF-8') ?>
                </span>
                <a href="/public/keranjang.php" style="margin-right:12px; text-decoration:none; color:#374151; font-weight:600;">Cart</a>

                <?php if (($user['role'] ?? 'customer') === 'admin'): ?>
                    <a href="/public/dashboard-admin.php" style="margin-right:12px; text-decoration:none; color:#374151; font-weight:600;">Admin</a>
                <?php elseif (($user['role'] ?? 'customer') === 'stand'): ?>
                    <a href="/public/dashboard-stand.php" style="margin-right:12px; text-decoration:none; color:#374151; font-weight:600;">Stand</a>
                <?php endif; ?>

                <form method="POST" action="/public/api/auth/logout.php" style="display:inline;">
                    <button type="submit" style="border:none; background:#6C4CF1; color:#fff; padding:8px 14px; border-radius:999px; cursor:pointer; font-weight:700;">
                        Logout
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </nav>
</header>

