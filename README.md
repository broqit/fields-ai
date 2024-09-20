# Fields AI:- Build AI-powered Filament forms.

**fields-ai is a powerful FilamentPHP plugin that integrates AI-powered features directly into your Filament forms.**

## What's New in Fields AI v0.1.1

1. **Support for Multiple OpenAI Endpoints**:
   - The latest OpenAI models, including the `gpt-4` and `gpt-3.5-turbo`, now use the **Chat API endpoint**, improving performance and response flexibility.
   - The `gpt-3.5-turbo-instruct` model will continue using the **Completion API endpoint**.

2. **Completion Models Now Considered Legacy**:
   - Please note that the models using the Completion endpoint, such as `gpt-3.5-turbo-instruct`, are now considered **legacy models**.
   - We highly recommend trying out the latest models for improved results and future-proofing. You can explore them here: [OpenAI Models Documentation](https://platform.openai.com/docs/models).

## Installation

```bash
composer require broqit/fields-ai
```
## Usage
1. After downloading, Run the package installation command:

```bash
php artisan fields-ai:install
```

**This command will:**
- Publish the configuration file for `Fields AI` Settings.
- Publish the configiration file for `openai-php/laravel` Settings.
- Create a symbolic link for storage

2. We use [openai-php/laravel](https://github.com/openai-php/laravel) package to connect with OpenAI API. Blank environment variables for the OpenAI API key and organization id are already appended to your .env file, Add your API key here.

```env
OPENAI_API_KEY=sk-...
OPENAI_ORGANIZATION=org-...
```
## Configuration

The `config/fields-ai.php` file allows you to set default values and templates:

* Set default AI models, max tokens, and temperature
* Add custom content templates

## Usage for Developers

### Adding AI to Built-in Filament Form Fields

You can add AI generation capabilities to `TextInput`, `Textarea`, `RichEditor`, `TinyEditor` and `EditorJS` fields using the `withAI()` method:

#### Without Options

```php
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Broqit\FilamentEditorJs\Forms\Components\EditorJs;
use Mohamedsabil83\FilamentFormsTinyeditor\Components\TinyEditor;

TextInput::make('title')
    ->withAI()

Textarea::make('description')
    ->withAI()

RichEditor::make('content')
    ->withAI()

EditorJS::make('content')
    ->withAI()

TinyEditor::make('content')
    ->withAI()
```

#### With Options

```php
TextInput::make('title')
    ->withAI([
        'model' => 'gpt-4',
        'max_tokens' => 100,
        'temperature' => 0.7,
    ])

Textarea::make('description')
    ->withAI([
        'model' => 'gpt-3.5-turbo',
        'max_tokens' => 200,
        'temperature' => 0.5,
    ])

RichEditor::make('content')
    ->withAI([
        'model' => 'gpt-4',
        'max_tokens' => 500,
        'temperature' => 0.8,
    ])

EditorJS::make('content')
    ->withAI([
        'model' => 'gpt-4',
        'max_tokens' => 500,
        'temperature' => 0.8,
    ])

TinyEditor::make('content')
    ->withAI([
        'model' => 'gpt-4',
        'max_tokens' => 500,
        'temperature' => 0.8,
    ])
```

## Usage for End-Users

### Generating Content

1. In a form with AI-enabled fields, users will see a "Generate with AI" link right upper to the field.
2. Clicking this link opens a modal where users can:
   * Enter a custom prompt
   * Choose from pre-defined templates (if configured)
   * Use existing content for modification
3. If modifying existing content, users can choose to:
   * Refine: Improve the existing text
   * Expand: Add more details to the existing text
   * Shorten: Summarize the existing text
4. After entering the prompt, selecting a template, or choosing a modification option, click "Generate" to create or modify the content.
5. The generated or modified content will be inserted into the form field.

## Advanced Features

* The package automatically checks for required dependencies and their versions, logging warnings if any are missing or outdated.
* Developers can add custom templates and prompts through the configuration file.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
