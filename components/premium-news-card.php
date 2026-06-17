<?php
/**
 * आकाशवाणी — Premium News Card Component
 * Elegant news cards with hover animations
 */

namespace Aakashvani\Components;

class NewsCard
{
    private array $news = [];
    private bool $featured = false;

    public function __construct(array $news = [], bool $featured = false)
    {
        $this->news = $news;
        $this->featured = $featured;
    }

    public function render(): string
    {
        if ($this->featured) {
            return $this->renderFeatured();
        }
        return $this->renderCard();
    }

    private function renderFeatured(): string
    {
        $title = $this->news['title'] ?? 'Featured News';
        $image = $this->news['image'] ?? '/assets/images/placeholder.svg';
        $category = $this->news['category'] ?? 'समाचार';
        $source = $this->news['source'] ?? 'आकाशवाणी';
        $time = $this->timeAgo($this->news['published_at'] ?? null);
        $slug = $this->news['slug'] ?? '#';

        return <<<HTML
        <a href="/news-post.php?slug={$slug}" class="featured-card">
            <div class="featured-image-wrapper">
                <img src="{$image}" alt="{$title}" class="featured-image" loading="eager">
                <div class="featured-overlay"></div>
            </div>
            <div class="featured-content">
                <span class="featured-badge">
                    <span class="badge-dot"></span>
                    {$category}
                </span>
                <h2 class="featured-title">{$title}</h2>
                <div class="featured-meta">
                    <span class="meta-source">{$source}</span>
                    <span class="meta-divider">•</span>
                    <span class="meta-time">{$time}</span>
                </div>
                <div class="featured-cta">
                    <span class="cta-text">{$this->t('थप पढ्नुहोस्', 'Read More')}</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </div>
            </div>
        </a>
        HTML;
    }

    private function renderCard(): string
    {
        $title = $this->news['title'] ?? 'News Title';
        $image = $this->news['image'] ?? '/assets/images/placeholder.svg';
        $category = $this->news['category'] ?? 'समाचार';
        $source = $this->news['source'] ?? 'आकाशवाणी';
        $time = $this->timeAgo($this->news['published_at'] ?? null);
        $slug = $this->news['slug'] ?? '#';

        return <<<HTML
        <a href="/news-post.php?slug={$slug}" class="news-card">
            <div class="card-image-wrapper">
                <img src="{$image}" alt="{$title}" class="card-image" loading="lazy">
                <span class="card-category-badge">{$category}</span>
            </div>
            <div class="card-body">
                <h3 class="card-title">{$title}</h3>
                <div class="card-meta">
                    <span class="meta-source">{$source}</span>
                    <span class="meta-time">{$time}</span>
                </div>
            </div>
        </a>
        HTML;
    }

    public static function renderMultiple(array $newsItems, bool $showFeatured = true): string
    {
        $html = '<div class="news-grid">';
        
        foreach ($newsItems as $index => $news) {
            $isFeatured = $showFeatured && $index === 0;
            $card = new self($news, $isFeatured);
            $html .= $card->render();
        }
        
        $html .= '</div>';
        return $html;
    }

    private function timeAgo(?string $datetime): string
    {
        if (!$datetime) return $this->t('अहिले', 'Just now');
        
        $time = strtotime($datetime);
        $diff = time() - $time;
        
        if ($diff < 60) return $diff . 's ' . $this->t('अघि', 'ago');
        if ($diff < 3600) return floor($diff / 60) . 'm ' . $this->t('अघि', 'ago');
        if ($diff < 86400) return floor($diff / 3600) . 'h ' . $this->t('अघि', 'ago');
        if ($diff < 604800) return floor($diff / 86400) . 'd ' . $this->t('अघि', 'ago');
        
        return date('j M', $time);
    }

    private function t(string $ne, string $en): string
    {
        return ($_SESSION['lang'] ?? 'ne') === 'en' ? $en : $ne;
    }
}
