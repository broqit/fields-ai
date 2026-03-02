<?php

namespace Broqit\FieldsAi\Forms\Actions;

use Filament\Forms\Components\MarkdownEditor;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Broqit\FieldsAi\FieldsAi;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;

class GenerateContentAction
{
    protected static function resolveProvider(array $data): string
    {
        return $data['ai_provider'] ?? config('ai.default', 'openai');
    }

    protected static function resolveModel(array $data): string
    {
        return $data['ai_model'] ?? config('fields-ai.default_model');
    }

    public function execute($field, $record, $data, array $options = [])
    {
        $contentActions = config('fields-ai.content_actions');

        // Створюємо новий масив на базі ключів
        $formattedActions = array_map(function($key) {
            return ucfirst($key); // Змінюємо першу літеру на велику
        }, array_keys($contentActions));

        // Створюємо асоціативний масив, де ключі залишаються ті самі, а значення = ключ з великою першою літерою
        $formattedActions = array_combine(array_keys($contentActions), $formattedActions);

        return Action::make('generateContent')
            ->label(__('fields-ai::form.generate_with_ai'))
            ->icon('heroicon-s-sparkles')
            ->form([
                Grid::make(2)->schema([
                    Select::make('ai_provider')
                        ->label('AI Provider')
                        ->options(function () {
                            return app(FieldsAi::class)->getAvailableProviders();
                        })
                        ->default(config('ai.default', 'openai'))
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            // Reset model when provider changes
                            $set('ai_model', null);
                        }),

                    Select::make('ai_model')
                        ->label('Model')
                        ->options(function (callable $get) {
                            $provider = $get('ai_provider') ?? config('ai.default', 'openai');
                            return app(FieldsAi::class)->getAvailableModels($provider);
                        })
                        ->default(config('fields-ai.default_model'))
                        ->required()
                        ->searchable()
                        ->native(false)
                        ->helperText('Select from recommended models or enter any model ID'),
                ]),

                Toggle::make('use_existing_content')
                    ->label(__('fields-ai::form.use_existing_content'))
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $set('ai_prompt', null);
                            $set('template', null);
                        } else {
                            $set('existing_content_action', null);
                        }
                    }),

                Select::make('existing_content_action')
                    ->label(__('fields-ai::form.action_on_existing_content'))
                    ->options($formattedActions)
                    ->visible(fn (callable $get) => $get('use_existing_content'))
                    ->required(fn (callable $get) => $get('use_existing_content')),

                Textarea::make('ai_prompt')
                    ->label(__('fields-ai::form.enter_your_prompt'))
                    ->required()
                    ->visible(fn (callable $get) => !$get('use_existing_content')),

                Select::make('template')
                    ->label(__('fields-ai::form.choose_template'))
                    ->options(function () {
                        return app(FieldsAi::class)->getContentTemplates();
                    })
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $set('ai_prompt', $state);
                        }
                    })
                    ->visible(fn (callable $get) => !$get('use_existing_content')),
            ])
            ->after(fn ($livewire) => $livewire->dispatch('fields-ai-content-generate'))
            ->action(function (array $data) use ($field, $options, $contentActions) {
                $provider = static::resolveProvider($data);
                $model = static::resolveModel($data);
                $providerKey = config("ai.providers.{$provider}.key");

                if (!$providerKey) {
                    Notification::make()
                        ->warning()
                        ->title('API Key Missing')
                        ->body("Please configure the API key for {$provider} in your .env file")
                        ->send();
                    return;
                }

                try {
                    $currentContent = $field->getState();

                    // Convert EditorJs content to HTML if needed
                    if ($field instanceof \Broqit\FilamentEditorJs\Forms\Components\EditorJs && is_array($currentContent)) {
                        $currentContent = \Durlecode\EJSParser\Parser::parse(json_encode($currentContent))->toHtml();
                    }

                    if ($data['use_existing_content']) {
                        $action = $data['existing_content_action'];

                        if (empty($contentActions[$action])) {
                            throw new \Exception(__('fields-ai::form.invalid_action_for_existing_content'));
                        }

                        $prompt = $contentActions[$action];
                    } else {
                        $prompt = $data['ai_prompt'] ?? null;

                        if (empty($prompt)) {
                            throw new \Exception(__('fields-ai::form.prompt_is_empty_or_null') . json_encode($data));
                        }

                        // For new content generation, don't use existing content
                        $currentContent = null;
                    }

                    $aiOptions = array_merge($options, [
                        'provider' => $provider,
                        'model' => $model,
                    ]);

                    $fieldsAi = app(FieldsAi::class);
                    $generatedContent = $fieldsAi->generateContent($prompt, $currentContent, $aiOptions);

                    $textInputContent = $generatedContent;

                    // Remove incomplete sentences
                    $generatedContent = $this->removeIncompleteSentences($generatedContent);

                    // Append the new content to the existing content
                    if ($data['use_existing_content'] && $data['existing_content_action'] === 'expand') {
                        $generatedContent = $generatedContent;
                    }

                    // Append the new content to the existing content for non-existing content actions
                    if ($field instanceof RichEditor) {
                        $newContent = $generatedContent;
                    } elseif ($field instanceof \Broqit\FilamentEditorJs\Forms\Components\EditorJs) {
                        $parser = new \Durlecode\EJSParser\HtmlParser($textInputContent);
                        $blocks = $parser->toBlocks();

                        $newContent = json_decode($blocks, true);
                    } elseif ($field instanceof MarkdownEditor) {
                        $newContent = $generatedContent;
                    } elseif ($field instanceof Textarea) {
                        $newContent = $generatedContent;
                    } else {
                        $newContent = $textInputContent;
                    }

                    // Set the new content
                    $field->state($newContent);

                    // Notify the user of successful content generation
                    $notification = Notification::make()
                        ->success()
                        ->title(__('fields-ai::form.content_generated_successfully'));

                    if ($fieldsAi->usedFallback()) {
                        $fallbackProvider = $aiOptions['fallback_provider'] ?? config('fields-ai.fallback_provider');
                        $notification->body("Content generated using fallback provider: {$fallbackProvider}");
                    } else {
                        $notification->body(__('fields-ai::form.ai_content_added_to_field'));
                    }

                    $notification->send();

                } catch (\Laravel\Ai\Exceptions\RateLimitException $e) {
                    Notification::make()
                        ->warning()
                        ->title('Rate Limit Exceeded')
                        ->body("Provider {$provider} has rate limited your requests. Please try again later or use a different provider.")
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->danger()
                        ->title(__('fields-ai::form.error_generating_content'))
                        ->body(__('fields-ai::form.error_occurred_while_generating_content') . $e->getMessage())
                        ->send();
                }
            })
            ->modalHeading(__('fields-ai::form.generate_content_with_ai'))
            ->modalSubmitActionLabel(__('fields-ai::form.generate'));
    }

    private function removeIncompleteSentences($content)
    {
        $sentences = preg_split('/(?<=[.!?])\s+/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $lastSentence = end($sentences);

        // Check if the last sentence ends with a period, exclamation mark, or question mark
        if (!preg_match('/[.!?]$/', $lastSentence)) {
            // Remove the last sentence if it's incomplete
            array_pop($sentences);
        }

        return implode(' ', $sentences);
    }
}
