<?php

namespace Broqit\FieldsAi;

use Broqit\FieldsAi\Services\AiService;

class FieldsAi
{
    protected AiService $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function generateContent(string $prompt, ?string $text = null, array $options = []): string
    {
        return $this->aiService->generateContent($prompt, $text, $options);
    }

    public function getContentTemplates()
    {
        return config('fields-ai.content_templates', []);
    }

    public function getAvailableProviders(): array
    {
        return $this->aiService->getAvailableProviders();
    }

    public function getAvailableModels(string $provider): array
    {
        return $this->aiService->getAvailableModels($provider);
    }

    public function usedFallback(): bool
    {
        return $this->aiService->usedFallback();
    }
}
