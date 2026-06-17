<?php
/**
 * आकाशवाणी — Premium Market Widget Component
 * Real-time market data with elegant animations
 */

namespace Aakashvani\Components;

class MarketWidget
{
    public function render(): string
    {
        return <<<HTML
        <!-- Premium Market Section -->
        <section class="premium-market" id="marketSection">
            <div class="container">
                <div class="market-grid">
                    <!-- NEPSE Index -->
                    <div class="market-card" data-type="nepse">
                        <div class="card-icon">📈</div>
                        <div class="card-label">NEPSE</div>
                        <div class="card-value" id="nepse-value">
                            <span class="skeleton-pulse"></span>
                        </div>
                        <div class="card-change" id="nepse-change">
                            <span class="skeleton-pulse" style="width:80px"></span>
                        </div>
                        <div class="card-sparkline" id="nepse-sparkline"></div>
                    </div>
                    
                    <!-- Gold Price -->
                    <div class="market-card" data-type="gold">
                        <div class="card-icon">🥇</div>
                        <div class="card-label">{$this->t('सुन (10g)', 'Gold (10g)')}</div>
                        <div class="card-value" id="gold-value">
                            <span class="skeleton-pulse"></span>
                        </div>
                        <div class="card-meta" id="gold-meta">
                            <span class="skeleton-pulse" style="width:60px"></span>
                        </div>
                        <div class="card-badge gold-badge">
                            <span class="badge-icon">✨</span>
                            <span class="badge-text">{$this->t('प्रीमियम', 'Premium')}</span>
                        </div>
                    </div>
                    
                    <!-- USD Forex -->
                    <div class="market-card" data-type="forex">
                        <div class="card-icon">💵</div>
                        <div class="card-label">USD</div>
                        <div class="card-value" id="forex-value">
                            <span class="skeleton-pulse"></span>
                        </div>
                        <div class="card-meta" id="forex-meta">
                            <span class="skeleton-pulse" style="width:70px"></span>
                        </div>
                        <div class="card-sparkline forex-sparkline" id="forex-sparkline"></div>
                    </div>
                    
                    <!-- Petrol Price -->
                    <div class="market-card" data-type="petrol">
                        <div class="card-icon">⛽</div>
                        <div class="card-label">{$this->t('पेट्रोल', 'Petrol')}</div>
                        <div class="card-value" id="petrol-value">
                            <span class="skeleton-pulse"></span>
                        </div>
                        <div class="card-meta" id="petrol-meta">
                            <span class="skeleton-pulse" style="width:50px"></span>
                        </div>
                    </div>
                    
                    <!-- Electricity -->
                    <div class="market-card" data-type="electricity">
                        <div class="card-icon">⚡</div>
                        <div class="card-label">{$this->t('बिजुली', 'Electricity')}</div>
                        <div class="card-value" id="electricity-value">
                            <span class="skeleton-pulse"></span>
                        </div>
                        <div class="card-meta" id="electricity-meta">
                            <span class="skeleton-pulse" style="width:60px"></span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <script>
        // Premium Market Data Loader
        class MarketLoader {
            constructor() {
                this.apiUrl = '/api/market-data.php';
                this.retryCount = 0;
                this.maxRetries = 3;
            }
            
            async init() {
                try {
                    await this.loadMarketData();
                    this.startAutoRefresh();
                } catch (error) {
                    console.error('Market data load failed:', error);
                }
            }
            
            async loadMarketData() {
                try {
                    const response = await fetch(this.apiUrl + '?type=all');
                    if (!response.ok) throw new Error('API Error');
                    
                    const data = await response.json();
                    this.updateUI(data);
                    this.retryCount = 0;
                } catch (error) {
                    this.retryCount++;
                    if (this.retryCount < this.maxRetries) {
                        setTimeout(() => this.loadMarketData(), 2000 * this.retryCount);
                    }
                }
            }
            
            updateUI(data) {
                // NEPSE
                if (data.nepse) {
                    const n = data.nepse;
                    const valueEl = document.getElementById('nepse-value');
                    const changeEl = document.getElementById('nepse-change');
                    
                    valueEl.textContent = n.index ? n.index.toLocaleString('en-US', {maximumFractionDigits: 2}) : '2,755.41';
                    
                    const change = n.change ?? 0;
                    const pct = n.changePercent ?? 0;
                    const isUp = change >= 0;
                    
                    changeEl.innerHTML = `
                        <span class="change-value ${isUp ? 'up' : 'down'}">
                            ${isUp ? '+' : ''}${change.toFixed(2)} (${isUp ? '+' : ''}${pct.toFixed(2)}%)
                        </span>
                    `;
                    changeEl.className = `card-change ${isUp ? 'up' : 'down'}`;
                }
                
                // Gold
                if (data.gold) {
                    const g = data.gold;
                    const valueEl = document.getElementById('gold-value');
                    const metaEl = document.getElementById('gold-meta');
                    
                    if (g.hallmarkPerTola) {
                        valueEl.textContent = 'रु ' + Number(g.hallmarkPerTola).toLocaleString('en-US');
                    }
                    
                    if (g.source) {
                        metaEl.innerHTML = `<span class="meta-source">${g.source}</span>`;
                    }
                }
                
                // Forex
                if (data.forex && data.forex.length > 0) {
                    const usd = data.forex.find(r => r.code === 'USD');
                    if (usd) {
                        document.getElementById('forex-value').textContent = 'रु ' + usd.sell.toFixed(2);
                        document.getElementById('forex-meta').innerHTML = `
                            <span class="meta-buy">{$this->t('किन्नु:', 'Buy:')} रु ${usd.buy.toFixed(2)}</span>
                        `;
                    }
                }
                
                // Petrol
                if (data.petrol) {
                    const p = data.petrol;
                    if (p.petrol) {
                        document.getElementById('petrol-value').textContent = 'रु ' + p.petrol;
                    }
                    if (p.diesel) {
                        document.getElementById('electricity-value').textContent = 'रु ' + p.diesel;
                    }
                }
                
                // Animate cards
                document.querySelectorAll('.market-card').forEach((card, index) => {
                    setTimeout(() => {
                        card.classList.add('loaded');
                    }, index * 100);
                });
            }
            
            startAutoRefresh() {
                // Refresh every 5 minutes
                setInterval(() => this.loadMarketData(), 5 * 60 * 1000);
            }
        }
        
        // Initialize on DOM ready
        document.addEventListener('DOMContentLoaded', () => {
            new MarketLoader().init();
        });
        </script>
        HTML;
    }
    
    private function t(string $ne, string $en): string
    {
        return ($_SESSION['lang'] ?? 'ne') === 'en' ? $en : $ne;
    }
}
