/**
 * PWA Install Handler
 * Handles the beforeinstallprompt event and shows install UI
 */
(function() {
    'use strict';
    
    let deferredPrompt = null;
    
    // Listen for beforeinstallprompt
    window.addEventListener('beforeinstallprompt', function(e) {
        e.preventDefault();
        deferredPrompt = e;
        showInstallUI();
    });
    
    // Show install button/UI
    function showInstallUI() {
        const existing = document.getElementById('pwa-install-banner');
        if (existing) return;
        
        const banner = document.createElement('div');
        banner.id = 'pwa-install-banner';
        banner.innerHTML = `
            <style>
                #pwa-install-banner {
                    position: fixed;
                    bottom: 20px;
                    left: 50%;
                    transform: translateX(-50%);
                    background: linear-gradient(135deg, #0f766e, #059669);
                    color: white;
                    padding: 16px 24px;
                    border-radius: 12px;
                    box-shadow: 0 8px 32px rgba(15,118,110,0.4);
                    z-index: 9999;
                    display: flex;
                    align-items: center;
                    gap: 16px;
                    font-family: 'Inter', 'Noto Sans Devanagari', sans-serif;
                    animation: slideUp 0.3s ease;
                }
                @keyframes slideUp {
                    from { transform: translateX(-50%) translateY(100px); opacity: 0; }
                    to { transform: translateX(-50%) translateY(0); opacity: 1; }
                }
                #pwa-install-banner .install-text {
                    font-size: 14px;
                    font-weight: 500;
                }
                #pwa-install-banner .install-btn {
                    background: white;
                    color: #0f766e;
                    border: none;
                    padding: 8px 16px;
                    border-radius: 8px;
                    font-weight: 600;
                    cursor: pointer;
                    font-size: 13px;
                }
                #pwa-install-banner .install-btn:hover {
                    background: #f0fdfa;
                }
                #pwa-install-banner .close-btn {
                    background: none;
                    border: none;
                    color: rgba(255,255,255,0.7);
                    cursor: pointer;
                    font-size: 18px;
                    padding: 4px 8px;
                }
                #pwa-install-banner .close-btn:hover {
                    color: white;
                }
            </style>
            <span class="install-text">📲 Install आकाशवाणी App</span>
            <button class="install-btn" id="pwa-do-install">Install गर्नुस्</button>
            <button class="close-btn" id="pwa-dismiss-install">×</button>
        `;
        document.body.appendChild(banner);
        
        document.getElementById('pwa-do-install').addEventListener('click', async function() {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            deferredPrompt = null;
            hideInstallUI();
        });
        
        document.getElementById('pwa-dismiss-install').addEventListener('click', hideInstallUI);
    }
    
    function hideInstallUI() {
        const banner = document.getElementById('pwa-install-banner');
        if (banner) banner.remove();
    }
    
    // Listen for app installed
    window.addEventListener('appinstalled', function() {
        deferredPrompt = null;
        hideInstallUI();
        console.log('PWA installed successfully');
    });
})();
