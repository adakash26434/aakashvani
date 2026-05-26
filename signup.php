<?php
/**
 * आकाशवाणी — signup.php (FIXED v3)
 * Bug fix: POST handler & redirects now run BEFORE header.php output.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/includes/auth.php';

$error = '';
$success = '';

if (isLoggedIn()) {
    header('Location: /');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email           = $_POST['email']            ?? '';
    $password        = $_POST['password']         ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $fullName        = $_POST['full_name']        ?? '';
    $phone           = $_POST['phone']            ?? '';

    if ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        try {
            $result = registerUser($email, $password, $fullName, $phone);
            if (isset($result['error'])) {
                $error = $result['error'];
            } else {
                // auto-login newly registered user, then redirect
                $login = loginUser($email, $password);
                if (!isset($login['error'])) {
                    header('Location: /?welcome=1');
                    exit;
                }
                header('Location: /login.php?registered=1');
                exit;
            }
        } catch (Throwable $e) {
            error_log('[signup] ' . $e->getMessage());
            $error = 'Sign-up service unavailable. Please try again in a moment.';
        }
    }
}

$pageTitle = 'Sign Up - आकाशवाणी';
$pageDesc  = 'Create your account to save preferences and track government services';
include __DIR__ . '/header.php';
?>

<section class="min-h-screen bg-gradient-to-br from-stone-50 to-emerald-50 py-12 px-4">
  <div class="max-w-md mx-auto">
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-emerald-600 rounded-2xl mb-4">
        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
        </svg>
      </div>
      <h1 class="text-3xl font-bold text-slate-900 mb-2"><?= t('खाता बनाउनुस्', 'Create Account') ?></h1>
      <p class="text-slate-500 text-sm"><?= t('तपाईंको data save गर्न र preferences राख्न', 'Save your data and personalize your experience') ?></p>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border border-stone-200 p-8">
      <?php if ($error): ?>
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
          <p class="text-red-700 text-sm font-medium"><?= h($error) ?></p>
        </div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-lg">
          <p class="text-emerald-700 text-sm font-medium"><?= h($success) ?></p>
        </div>
      <?php endif; ?>

      <form method="POST" action="/signup.php" class="space-y-4">
        <div>
          <label for="email" class="block text-sm font-medium text-slate-700 mb-2"><?= t('ईमेल', 'Email') ?></label>
          <input type="email" id="email" name="email" required
                 class="w-full px-4 py-2.5 border border-stone-300 rounded-lg focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-slate-900"
                 placeholder="you@example.com" />
        </div>
        <div>
          <label for="full_name" class="block text-sm font-medium text-slate-700 mb-2"><?= t('पूरा नाम', 'Full Name') ?></label>
          <input type="text" id="full_name" name="full_name" required
                 class="w-full px-4 py-2.5 border border-stone-300 rounded-lg focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-slate-900"
                 placeholder="तपाईंको नाम" />
        </div>
        <div>
          <label for="phone" class="block text-sm font-medium text-slate-700 mb-2"><?= t('फोन नम्बर (वैकल्पिक)', 'Phone (Optional)') ?></label>
          <input type="tel" id="phone" name="phone"
                 class="w-full px-4 py-2.5 border border-stone-300 rounded-lg focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-slate-900"
                 placeholder="+977 9841234567" />
        </div>
        <div>
          <label for="password" class="block text-sm font-medium text-slate-700 mb-2"><?= t('पासवर्ड', 'Password') ?></label>
          <input type="password" id="password" name="password" required
                 class="w-full px-4 py-2.5 border border-stone-300 rounded-lg focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-slate-900"
                 placeholder="••••••••" />
          <p class="text-xs text-slate-500 mt-2"><?= t('कम्तीमा 8 अक्षर, अपरकेस र संख्या चाहिन्छ', 'Min 8 chars, uppercase & numbers') ?></p>
        </div>
        <div>
          <label for="confirm_password" class="block text-sm font-medium text-slate-700 mb-2"><?= t('पासवर्ड पुष्टि गर्नुस्', 'Confirm Password') ?></label>
          <input type="password" id="confirm_password" name="confirm_password" required
                 class="w-full px-4 py-2.5 border border-stone-300 rounded-lg focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-slate-900"
                 placeholder="••••••••" />
        </div>
        <button type="submit" class="w-full btn-primary py-2.5 rounded-lg font-semibold text-white mt-6">
          <?= t('खाता बनाउनुस्', 'Create Account') ?>
        </button>
      </form>

      <div class="my-6 flex items-center gap-4">
        <div class="flex-1 h-px bg-stone-200"></div>
        <span class="text-xs text-slate-500"><?= t('वा', 'or') ?></span>
        <div class="flex-1 h-px bg-stone-200"></div>
      </div>

      <p class="text-center text-sm text-slate-600">
        <?= t('खाता छ?', 'Have an account?') ?>
        <a href="/login.php" class="text-emerald-600 font-semibold hover:text-emerald-700">
          <?= t('लगइन गर्नुस्', 'Login here') ?>
        </a>
      </p>
    </div>
  </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>
