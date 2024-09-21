<?php

namespace Broqit\FieldsAi;

use Broqit\FieldsAi\Services\OpenAIService;

class FieldsAi
{
    protected OpenAIService $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    public function generateContent(string $prompt, string $text, array $options = []): string
    {
        return $this->openAIService->generateContent($prompt, $text, $options);
    }

    public function getContentTemplates()
    {
        return config('fields-ai.content_templates', []);
    }
}
