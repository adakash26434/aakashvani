<?php
/**
 * DATA SCHEMA - Unified data structure definitions for all content
 * 
 * This file defines consistent data schemas used across the application.
 * All pages must validate data against these schemas before display.
 */

class DataSchema {
    
    /**
     * NEWS ARTICLE SCHEMA (RSS or Database)
     * Unified structure for all news articles across the app
     */
    public static function NewsArticle(): array {
        return [
            'id'           => null,           // Unique identifier (slug-based for RSS, int for DB)
            'title'        => '',             // Required - Full Nepali/English title
            'excerpt'      => '',             // Summary (250 chars max via truncateText)
            'content'      => '',             // Full body text or HTML
            'category'     => 'general',      // general|politics|economy|sports|technology|world|entertainment
            'source'       => 'Internal',     // News outlet name (OnlineKhabar, BBC Nepal, etc)
            'source_url'   => '',             // Original source URL (full article link)
            'image_url'    => '',             // Featured image URL
            'author'       => '',             // Byline/author name
            'published_at' => 0,              // Unix timestamp
            'updated_at'   => 0,              // Unix timestamp
            'language'     => 'ne',           // 'ne' or 'en'
            'fingerprint'  => '',             // MD5(title+url) for dedup detection
            'source_key'   => '',             // Internal source key (onlinekhabar, bbc, etc)
        ];
    }
    
    /**
     * MARKET DATA SCHEMA (Share prices, forex, crypto)
     * Unified structure for all market/financial data
     */
    public static function MarketData(): array {
        return [
            'id'           => '',             // Symbol/Code (NABIL, USD, etc)
            'name'         => '',             // Full name (Nabil Bank, US Dollar, etc)
            'category'     => 'stock',        // stock|forex|crypto|commodity
            'price'        => 0.0,            // Current price in NPR or relevant unit
            'change'       => 0.0,            // Price change amount
            'change_pct'   => 0.0,            // Percentage change
            'high'         => 0.0,            // Day high
            'low'          => 0.0,            // Day low
            'volume'       => 0,              // Trading volume
            'market_cap'   => 0,              // Market cap (if applicable)
            'updated_at'   => 0,              // Unix timestamp
            'source'       => '',             // Data source
            'currency'     => 'NPR',          // Base currency
            'logo_url'     => '',             // Logo/icon URL
        ];
    }
    
    /**
     * USER PROFILE SCHEMA
     * Unified structure for user data
     */
    public static function UserProfile(): array {
        return [
            'id'           => 0,              // User ID
            'name'         => '',             // Display name (Nepali/English)
            'email'        => '',             // Email address
            'phone'        => '',             // Phone number
            'avatar_url'   => '',             // Profile picture
            'bio'          => '',             // User bio (500 chars max)
            'role'         => 'user',         // user|editor|admin
            'is_admin'     => false,
            'created_at'   => 0,              // Registration timestamp
            'last_active'  => 0,              // Last login timestamp
        ];
    }
    
    /**
     * Validate against schema - ensures data integrity
     */
    public static function validate(array $data, array $schema): bool {
        foreach (array_keys($schema) as $key) {
            if (!isset($data[$key])) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Sanitize data to schema - converts types and applies limits
     */
    public static function sanitize(array $data, array $schema, string $schemaType): array {
        $result = [];
        
        foreach ($schema as $key => $default) {
            $value = $data[$key] ?? $default;
            
            // Type conversion
            if (is_int($default)) {
                $result[$key] = (int)$value;
            } elseif (is_float($default)) {
                $result[$key] = (float)$value;
            } elseif (is_bool($default)) {
                $result[$key] = (bool)$value;
            } else {
                $result[$key] = (string)$value;
            }
            
            // Apply schema-specific limits
            if ($schemaType === 'news') {
                if ($key === 'excerpt' && strlen($result[$key]) > 250) {
                    $result[$key] = mb_substr($result[$key], 0, 250, 'UTF-8');
                }
                if ($key === 'category' && !in_array($result[$key], 
                    ['general','politics','economy','sports','technology','world','entertainment'])) {
                    $result[$key] = 'general';
                }
            }
        }
        
        return $result;
    }
}
?>
