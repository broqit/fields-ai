<?php

namespace Broqit\FieldsAi\Services;

use Broqit\FieldsAi\Traits\CanChunkedHtml;
use OpenAI\Laravel\Facades\OpenAI;

class OpenAIService
{
    use CanChunkedHtml;

    public function generateContent(string $prompt, string $text = null, array $options = []): string
    {
        $model = $options['model'] ?? config('fields-ai.default_model');
        $maxTokens = $options['max_tokens'] ?? config('fields-ai.default_max_tokens');
        $temperature = $options['temperature'] ?? config('fields-ai.default_temperature');
        $chunkLongText = $options['chunk_long_text'] ?? false;

        $params = [
            'model' => $model,
            'max_tokens' => $maxTokens,
            'temperature' => $temperature,
        ];

        if ($options['max_tokens'] === null) {
            unset($params['max_tokens']);
        }

        if ($options['temperature'] === null) {
            unset($params['temperature']);
        }

        $chunkedText = $chunkLongText ? $this->chunkLongText($text) : [];
        if (empty($chunkedText)) {
            $chunkedText = [$text];
        }

        $results = [];
        foreach ($chunkedText as $item) {
            $promptItem = sprintf($prompt, $item);

            if ($model === 'gpt-3.5-turbo-instruct') {
                $completion = OpenAI::completions()->create($params + [
                        'prompt' => $promptItem,
                    ]);

                $results[] = $this->formated($completion['choices'][0]['text']);
            } else {
                $result = OpenAI::chat()->create($params + [
                        'messages' => [
                            [
                                'role' => 'user',
                                'content' => $promptItem,
                            ],
                        ],
                    ]);

                $results[] = $this->formated($result->choices[0]->message->content);
            }
        }

        return implode($results);
    }
}
