<?php

namespace Broqit\FieldsAi;

use Broqit\FieldsAi\Services\OpenAIService;

class FieldsAi
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    public function generateContent(string $prompt, array $options = [])
    {
        return $this->openAIService->generateContent($prompt, $options);
    }

    public function getContentTemplates()
    {
        return config('fields-ai.content_templates', []);
    }
}
