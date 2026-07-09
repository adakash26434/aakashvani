<?php
/**
 * आकाशवाणी — User Profile Page
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/auth.php';

$lang = siteLang();
$isNepali = ($lang !== 'en');
$t = fn($ne, $en) => $isNepali ? $ne : $en;
$flash = getFlash();

// Check if user is logged in
$isLoggedIn = !empty($_SESSION['user_id']) || !empty($_SESSION['auth_user_id']);
$userName = $_SESSION['user_name'] ?? '';
$userEmail = '';

// If logged in, try to get user details from auth_users table
if ($isLoggedIn) {
    try {
        $pdo = db();
        if ($pdo) {
            $userId = $_SESSION['auth_user_id'] ?? $_SESSION['user_id'] ?? null;
            if ($userId) {
                $stmt = $pdo->prepare("SELECT email, full_name, phone, language, created_at FROM auth_users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    $userEmail = $user['email'] ?? '';
                    $userName = $user['full_name'] ?: $userName;
                    $userPhone = $user['phone'] ?? '';
                    $userLang = $user['language'] ?? 'ne';
                    $userCreated = $user['created_at'] ?? '';
                }
            }
        }
    } catch (Throwable $e) {
        error_log('[profile] user fetch: ' . $e->getMessage());
    }
} else {
    // Not logged in - redirect to login
    header('Location: /login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?= $isNepali ? 'ne' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t('प्रोफाइल', 'Profile') ?> | <?= $t('आकाशवाणी', 'Aakashvani') ?></title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/premium.css">
    <style>
        .profile-page { min-height: 100vh; background: linear-gradient(135deg, var(--dark-900), var(--dark-800)); padding: var(--sp-8); }
        .profile-card { background: #fff; border-radius: var(--radius-2xl); padding: var(--sp-8); width: 100%; max-width: 500px; margin: 0 auto; box-shadow: var(--shadow-xl); }
        .profile-header { text-align: center; margin-bottom: var(--sp-6); }
        .profile-avatar { width: 80px; height: 80px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 800; font-size: 2rem; margin: 0 auto var(--sp-4); }
        .profile-name { font-size: 1.5rem; font-weight: 700; color: var(--dark-900); }
        .profile-email { color: var(--dark-500); font-size: 0.875rem; margin-top: var(--sp-1); }
        .profile-section { margin-top: var(--sp-6); padding-top: var(--sp-6); border-top: 1px solid var(--dark-200); }
        .profile-section-title { font-weight: 600; color: var(--dark-700); margin-bottom: var(--sp-4); }
        .info-row { display: flex; justify-content: space-between; padding: var(--sp-3) 0; border-bottom: 1px solid var(--dark-100); }
        .info-label { color: var(--dark-500); font-size: 0.875rem; }
        .info-value { color: var(--dark-900); font-weight: 500; }
        .btn { display: inline-block; padding: var(--sp-3) var(--sp-6); border-radius: var(--radius-lg); font-weight: 600; text-decoration: none; transition: all var(--transition); cursor: pointer; border: none; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-600); }
        .btn-outline { background: transparent; color: var(--dark-700); border: 1px solid var(--dark-300); }
        .btn-outline:hover { background: var(--dark-100); }
        .flash-success { background: #f0fdf4; color: #15803d; padding: 12px 16px; border-radius: var(--radius-lg); margin-bottom: 16px; border: 1px solid #bbf7d0; }
        .flash-error { background: #fef2f2; color: #b91c1c; padding: 12px 16px; border-radius: var(--radius-lg); margin-bottom: 16px; border: 1px solid #fecaca; }
        .btn-danger { background: #dc2626; color: #fff; }
        .btn-danger:hover { background: #b91c1c; }
        .actions { display: flex; gap: var(--sp-3); margin-top: var(--sp-6); flex-wrap: wrap; }
    </style>
    <script src="/assets/js/lucide.min.js"></script>
</head>
<body>
    <div class="profile-page">
        <div class="profile-card">
            <?php if ($flash): ?>
                <div class="<?= $flash['type'] === 'error' ? 'flash-error' : 'flash-success' ?>">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>
            
            <div class="profile-header">
                <div class="profile-avatar"><?= brandInitials() ?></div>
                <h1 class="profile-name"><?= htmlspecialchars($userName ?: $t('User', 'प्रयोगकर्ता')) ?></h1>
                <p class="profile-email"><?= htmlspecialchars($userEmail) ?></p>
            </div>
            
            <div class="profile-section">
                <h2 class="profile-section-title"><?= $t('खाता जानकारी', 'Account Information') ?></h2>
                <div class="info-row">
                    <span class="info-label"><?= $t('इमेल', 'Email') ?></span>
                    <span class="info-value"><?= htmlspecialchars($userEmail ?: '-') ?></span>
                </div>
                <?php if (!empty($userPhone)): ?>
                <div class="info-row">
                    <span class="info-label"><?= $t('फोन', 'Phone') ?></span>
                    <span class="info-value"><?= htmlspecialchars($userPhone) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($userCreated)): ?>
                <div class="info-row">
                    <span class="info-label"><?= $t('दर्ता मिति', 'Registered') ?></span>
                    <span class="info-value"><?= date('j M Y', strtotime($userCreated)) ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="actions">
                <a href="/auth/logout.php" class="btn btn-danger"><?= $t('लग아उट', 'Logout') ?></a>
                <a href="/" class="btn btn-outline"><?= $t('गृहपृष्ठ', 'Home') ?></a>
            </div>
        </div>
    </div>
</body>
</html>
