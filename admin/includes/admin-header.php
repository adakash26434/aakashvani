<?php
/**
 * Admin Header Include
 */
if (!defined('AAK_INIT')) die('Direct access not permitted');

$userName = $_SESSION['user_name'] ?? 'Admin';
$userRole = $_SESSION['user_role'] ?? 'admin';
?>
<header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-40">
    <div class="flex items-center justify-between px-4 py-3">
        <div class="flex items-center gap-4">
            <a href="/admin/dashboard.php" class="flex items-center gap-2">
                <img src="/favicon.svg" alt="Logo" class="w-8 h-8">
                <span class="font-bold text-lg text-gray-900 dark:text-white hidden md:block">Admin Panel</span>
            </a>
        </div>
        
        <div class="flex items-center gap-4">
            <!-- Dark Mode Toggle -->
            <button onclick="toggleDarkMode()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg" title="Toggle Dark Mode">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-300 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-300 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </button>
            
            <!-- View Site -->
            <a href="/" target="_blank" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg" title="View Site">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
            </a>
            
            <!-- User Menu -->
            <div class="relative">
                <button onclick="toggleUserMenu()" class="flex items-center gap-2 p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                    <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center text-white font-medium">
                        <?= strtoupper(substr($userName, 0, 1)) ?>
                    </div>
                    <span class="hidden md:block text-sm font-medium text-gray-700 dark:text-gray-300"><?= htmlspecialchars($userName) ?></span>
                </button>
                
                <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-2">
                    <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700">
                        <div class="font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($userName) ?></div>
                        <div class="text-xs text-gray-500 capitalize"><?= str_replace('_', ' ', $userRole) ?></div>
                    </div>
                    <a href="/admin/profile.php" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                        Profile Settings
                    </a>
                    <a href="/admin/logout.php" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-red-600">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
function toggleDarkMode() {
    document.documentElement.classList.toggle('dark');
    localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
}

function toggleUserMenu() {
    document.getElementById('userMenu').classList.toggle('hidden');
}

// Check dark mode preference
if (localStorage.getItem('darkMode') === 'true' || 
    (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
}
</script>
