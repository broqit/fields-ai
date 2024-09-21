<?php

namespace Broqit\FieldsAi\Forms\Actions;

use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Broqit\FieldsAi\FieldsAi;
use Filament\Notifications\Notification;

class GenerateContentAction
{
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
                Toggle::make('use_existing_content')
                    ->label(__('fields-ai::form.use_existing_content'))
                    ->reactive()
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
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $set('ai_prompt', $state);
                        }
                    })
                    ->visible(fn (callable $get) => !$get('use_existing_content')),
            ])
            ->action(function (array $data) use ($field, $options, $contentActions) {
                if (!env('OPENAI_API_KEY')) {
                    Notification::make()
                        ->warning()
                        ->title(__('fields-ai::form.openai_api_key_missing'))
                        ->body(__('fields-ai::form.add_openai_key_to_env'))
                        ->send();
                    return;
                }

                try {
                    $currentContent = $field->getState();

                    if ($data['use_existing_content']) {
                        $action = $data['existing_content_action'];

                        if (empty($contentActions[$action])) {
                            throw new \Exception(__('fields-ai::form.invalid_action_for_existing_content'));
                        }

                        if ($field instanceof \Broqit\FilamentEditorJs\Forms\Components\EditorJs) {
                            $currentContent = \Durlecode\EJSParser\Parser::parse(json_encode($currentContent))->toHtml();
                        }

                        $prompt = $contentActions[$action];
                    } else {
                        $prompt = $data['ai_prompt'] ?? null;

                        if (empty($prompt)) {
                            throw new \Exception(__('fields-ai::form.prompt_is_empty_or_null') . json_encode($data));
                        }
                    }

                    $generatedContent = app(FieldsAi::class)->generateContent($prompt, $currentContent, $options);

                    $textInputContent = $generatedContent;
                    // Remove incomplete sentences
                    $generatedContent = $this->removeIncompleteSentences($generatedContent);

                    // Append the new content to the existing content
                    if ($data['use_existing_content'] && $data['existing_content_action'] === 'expand') {
                        $generatedContent = $currentContent . "\n\n" . $generatedContent;
                    }

                    // Append the new content to the existing content for non-existing content actions
                    if ($field instanceof RichEditor) {
                        $newContent = $generatedContent;
                    } elseif ($field instanceof \Broqit\FilamentEditorJs\Forms\Components\EditorJs) {
                        $parser = new \Durlecode\EJSParser\HtmlParser($generatedContent);
                        $blocks = $parser->toBlocks();

                        $newContent = json_decode($blocks, true);
                    } elseif ($field instanceof MarkdownEditor) {
                        $newContent = $generatedContent;
                    } elseif ($field instanceof \Mohamedsabil83\FilamentFormsTinyeditor\Components\TinyEditor) {
                        $newContent = $generatedContent;
                    } elseif ($field instanceof Textarea) {
                        $newContent = $generatedContent;
                    } else {
                        $newContent = $textInputContent;
                    }

                    // Set the new content
                    $field->state($newContent);

                    // Notify the user of successful content generation
                    Notification::make()
                        ->success()
                        ->title(__('fields-ai::form.content_generated_successfully'))
                        ->body(__('fields-ai::form.ai_content_added_to_field'))
                        ->send();

                } catch (\Exception $e) {
                    // Notify the user if an error occurs
                    Notification::make()
                        ->danger()
                        ->title(__('fields-ai::form.error_generating_content'))
                        ->body(__('fields-ai::form.error_occurred_while_generating_content') . $e->getMessage())
                        ->send();
                }
            })
            ->modalHeading(__('fields-ai::form.generate_content_with_ai'))
            ->modalButton(__('fields-ai::form.generate'));
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
