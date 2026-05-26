<?php
/**
 * AI Sync — wraps Lovable AI Gateway / Gemini API for background sync jobs.
 *
 * Set ONE of these in config.php:
 *   define('LOVABLE_API_KEY', '...');   // preferred (uses gateway)
 *   define('GEMINI_API_KEY', '...');    // direct Google AI
 *   define('OPENAI_API_KEY', '...');    // OpenAI fallback
 *
 * Usage:
 *   $resp = aiAsk('Summarize: '.$text);
 *   $json = aiExtract('https://example.com/page', ['fields'=>['title','price']]);
 */

if (!function_exists('aiAvailable')) {
    function aiAvailable(): bool {
        return defined('LOVABLE_API_KEY') || defined('GEMINI_API_KEY') || defined('OPENAI_API_KEY');
    }
}

if (!function_exists('aiAsk')) {
    /**
     * Plain text completion. Returns string|null.
     * @param string $prompt
     * @param string $system optional system prompt
     * @param int    $timeout
     */
    function aiAsk(string $prompt, string $system = '', int $timeout = 25): ?string {
        if (defined('LOVABLE_API_KEY')) {
            return aiCallGateway($prompt, $system, $timeout);
        }
        if (defined('GEMINI_API_KEY')) {
            return aiCallGemini($prompt, $system, $timeout);
        }
        if (defined('OPENAI_API_KEY')) {
            return aiCallOpenAI($prompt, $system, $timeout);
        }
        return null;
    }
}

if (!function_exists('aiAskJson')) {
    /** Request JSON output. Returns decoded array|null. */
    function aiAskJson(string $prompt, string $system = '', int $timeout = 25): ?array {
        $sys = trim($system . "\nReturn ONLY a valid JSON object. No prose, no markdown fences.");
        $raw = aiAsk($prompt, $sys, $timeout);
        if (!$raw) return null;
        // Strip ```json fences if present
        $raw = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($raw));
        $j = json_decode($raw, true);
        if (is_array($j)) return $j;
        // try to extract first {...} block
        if (preg_match('/\{[\s\S]*\}/', $raw, $m)) {
            $j = json_decode($m[0], true);
            if (is_array($j)) return $j;
        }
        return null;
    }
}

if (!function_exists('aiCallGateway')) {
    function aiCallGateway(string $prompt, string $system, int $timeout): ?string {
        $url = 'https://ai.gateway.lovable.dev/v1/chat/completions';
        $payload = [
            'model' => 'google/gemini-2.5-flash',
            'messages' => array_filter([
                $system ? ['role'=>'system','content'=>$system] : null,
                ['role'=>'user','content'=>$prompt],
            ]),
        ];
        return aiHttpJson($url, $payload, [
            'Authorization: Bearer ' . LOVABLE_API_KEY,
            'Content-Type: application/json',
        ], $timeout, 'gateway');
    }
}

if (!function_exists('aiCallGemini')) {
    function aiCallGemini(string $prompt, string $system, int $timeout): ?string {
        $model = 'gemini-2.5-flash';
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . GEMINI_API_KEY;
        $text = $system ? ($system . "\n\n" . $prompt) : $prompt;
        $payload = ['contents' => [['parts' => [['text' => $text]]]]];
        $raw = aiHttpRaw($url, $payload, ['Content-Type: application/json'], $timeout);
        if (!$raw) return null;
        $j = json_decode($raw, true);
        return $j['candidates'][0]['content']['parts'][0]['text'] ?? null;
    }
}

if (!function_exists('aiCallOpenAI')) {
    function aiCallOpenAI(string $prompt, string $system, int $timeout): ?string {
        $url = 'https://api.openai.com/v1/chat/completions';
        $payload = [
            'model' => 'gpt-4o-mini',
            'messages' => array_filter([
                $system ? ['role'=>'system','content'=>$system] : null,
                ['role'=>'user','content'=>$prompt],
            ]),
        ];
        return aiHttpJson($url, $payload, [
            'Authorization: Bearer ' . OPENAI_API_KEY,
            'Content-Type: application/json',
        ], $timeout, 'openai');
    }
}

if (!function_exists('aiHttpJson')) {
    /** Calls an OpenAI-style chat endpoint and returns choices[0].message.content. */
    function aiHttpJson(string $url, array $payload, array $headers, int $timeout, string $tag): ?string {
        $raw = aiHttpRaw($url, $payload, $headers, $timeout);
        if (!$raw) return null;
        $j = json_decode($raw, true);
        return $j['choices'][0]['message']['content'] ?? null;
    }
}

if (!function_exists('aiHttpRaw')) {
    function aiHttpRaw(string $url, array $payload, array $headers, int $timeout): ?string {
        if (!function_exists('curl_init')) return null;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);
        $r = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);
        if ($r === false || $code >= 400) {
            @error_log("[ai-sync] HTTP $code ".$err.' '.substr((string)$r,0,200));
            return null;
        }
        return $r;
    }
}

if (!function_exists('aiSyncStatus')) {
    function aiSyncStatus(): array {
        return [
            'available' => aiAvailable(),
            'provider'  => defined('LOVABLE_API_KEY') ? 'lovable-gateway'
                         : (defined('GEMINI_API_KEY')  ? 'gemini'
                         : (defined('OPENAI_API_KEY')  ? 'openai' : 'none')),
        ];
    }
}
