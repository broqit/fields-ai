<?php

namespace Broqit\FieldsAi\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

class ContentGeneratorAgent implements Agent
{
    use Promptable;

    public function __construct(private readonly ?string $customInstructions = null) {}

    public function instructions(): string
    {
        if ($this->customInstructions !== null) {
            return $this->customInstructions;
        }

        return 'You are a helpful content generation assistant. Generate high-quality, relevant content based on the provided context and instructions.';
    }

    /**
     * Generate content based on prompt and optional context
     */
    public function generate(string $prompt, ?string $context = null, ?string $provider = null, ?string $model = null): string
    {
        $fullPrompt = $context ? sprintf($prompt, $context) : $prompt;

        return $this->prompt($fullPrompt, provider: $provider, model: $model);
    }

    /**
     * Resolve provider from data
     */
    public static function resolveProvider(array $data): ?string
    {
        return $data['ai_provider'] ?? null;
    }

    /**
     * Resolve model from data
     */
    public static function resolveModel(array $data): ?string
    {
        return $data['ai_model'] ?? null;
    }
}
