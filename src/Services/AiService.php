<?php

namespace Broqit\FieldsAi\Services;

use Broqit\FieldsAi\Agents\ContentGeneratorAgent;
use Broqit\FieldsAi\Traits\CanChunkedHtml;
use Laravel\Ai\AiManager;

class AiService
{
    use CanChunkedHtml;

    protected bool $usedFallback = false;

    /**
     * Get available AI providers from Laravel AI config
     */
    public function getAvailableProviders(): array
    {
        $providers = config('ai.providers', []);
        $available = [];

        $providerLabels = [
            'openai' => 'OpenAI',
            'anthropic' => 'Anthropic (Claude)',
            'gemini' => 'Google Gemini',
            'cohere' => 'Cohere',
            'groq' => 'Groq',
            'xai' => 'xAI (Grok)',
            'mistral' => 'Mistral AI',
            'deepseek' => 'DeepSeek',
            'ollama' => 'Ollama (Local)',
            'openrouter' => 'OpenRouter',
        ];

        foreach ($providers as $name => $config) {
            if (!empty($config['key']) || $name === 'ollama') {
                $available[$name] = $providerLabels[$name] ?? ucfirst($name);
            }
        }

        return $available;
    }

    /**
     * Get available models for a provider from Laravel AI
     */
    public function getAvailableModels(string $provider): array
    {
        try {
            $aiProvider = app(AiManager::class)->textProvider($provider);
            
            $models = [];
            
            // Get default, cheapest, and smartest models from Laravel AI
            if (method_exists($aiProvider, 'defaultTextModel')) {
                $default = $aiProvider->defaultTextModel();
                $models[$default] = $this->formatModelLabel($default, 'Default');
            }
            
            if (method_exists($aiProvider, 'cheapestTextModel')) {
                $cheapest = $aiProvider->cheapestTextModel();
                if (!isset($models[$cheapest])) {
                    $models[$cheapest] = $this->formatModelLabel($cheapest, 'Cheapest');
                }
            }
            
            if (method_exists($aiProvider, 'smartestTextModel')) {
                $smartest = $aiProvider->smartestTextModel();
                if (!isset($models[$smartest])) {
                    $models[$smartest] = $this->formatModelLabel($smartest, 'Smartest');
                }
            }
            
            return $models;
            
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Format model label for display
     */
    protected function formatModelLabel(string $model, string $suffix): string
    {
        $label = ucfirst(str_replace(['-', '_'], ' ', $model));
        return "{$label} ({$suffix})";
    }

    public function generateContent(string $prompt, ?string $text = null, array $options = []): string
    {
        $chunkLongText = $options['chunk_long_text'] ?? false;

        $chunkedText = $chunkLongText && $text ? $this->chunkLongText($text) : [];
        if (empty($chunkedText)) {
            $chunkedText = [$text ?? ''];
        }

        $agent = new ContentGeneratorAgent(
            customInstructions: $options['custom_instructions'] ?? null
        );

        if (isset($options['temperature'])) {
            $agent->temperature($options['temperature']);
        }

        if (isset($options['max_tokens'])) {
            $agent->maxTokens($options['max_tokens']);
        }

        $provider = $options['provider'] ?? null;
        $model = $options['model'] ?? null;
        $fallbackProvider = $options['fallback_provider'] ?? config('fields-ai.fallback_provider');

        $this->usedFallback = false;
        $results = [];

        foreach ($chunkedText as $item) {
            try {
                $response = $agent->generate(
                    prompt: $prompt,
                    context: $item,
                    provider: $provider,
                    model: $model
                );

                $results[] = $this->formated($response);
            } catch (\Laravel\Ai\Exceptions\RateLimitException $e) {
                if ($fallbackProvider && $fallbackProvider !== $provider) {
                    $this->usedFallback = true;

                    $response = $agent->generate(
                        prompt: $prompt,
                        context: $item,
                        provider: $fallbackProvider,
                        model: null
                    );

                    $results[] = $this->formated($response);
                } else {
                    throw $e;
                }
            }
        }

        return implode($results);
    }

    /**
     * Check if fallback provider was used in last generation
     */
    public function usedFallback(): bool
    {
        return $this->usedFallback;
    }
}
