# Fields AI - Build AI-powered Filament forms with Laravel AI

**fields-ai is a powerful FilamentPHP plugin that integrates AI-powered features directly into your Filament forms using the official Laravel AI SDK.**

## What's New in v2.0 🎉

1. **Migrated to Laravel AI SDK**:
   - Now using the official `laravel/ai` package for better integration and reliability
   - Unified API for multiple AI providers
   - Better performance and error handling

2. **Multi-Provider Support**:
   - ✅ **OpenAI** - GPT-4o, GPT-4, GPT-3.5
   - ✅ **Anthropic** - Claude Sonnet, Opus, Haiku
   - ✅ **Google Gemini** - Gemini 2.0, 1.5 Pro/Flash
   - ✅ **Groq** - Llama 3.3, Mixtral (fastest inference)
   - ✅ **Mistral AI** - Mistral Large, Codestral
   - ✅ **xAI** - Grok models
   - ✅ **DeepSeek** - DeepSeek Chat/Coder
   - ✅ **Ollama** - Local models

3. **Dynamic Provider & Model Selection**:
   - Users can select AI provider and model directly in the modal
   - Models are automatically loaded from Laravel AI configuration
   - Support for custom and fine-tuned models

4. **Automatic Fallback**:
   - Configure fallback provider for rate limiting scenarios
   - Seamless failover when primary provider is unavailable
   - User notifications about fallback usage

## Requirements

- PHP ^8.3
- Laravel ^11.0 | ^12.0
- Filament ^4.0 | ^5.0
- Laravel AI ^0.2

## Installation

```bash
composer require broqit/fields-ai
```

## Setup

1. Install the package:

```bash
php artisan fields-ai:install
```

**This command will:**
- Publish the configuration file for `Fields AI` settings
- Create a symbolic link for storage

2. Configure your AI provider(s) in `config/ai.php`:

```php
return [
    'default' => env('AI_DRIVER', 'openai'),
    
    'providers' => [
        'openai' => [
            'driver' => 'openai',
            'key' => env('OPENAI_API_KEY'),
        ],
        'anthropic' => [
            'driver' => 'anthropic',
            'key' => env('ANTHROPIC_API_KEY'),
        ],
        // Add more providers as needed
    ],
];
```

3. Add your API key(s) to `.env`:

```env
# Primary provider
OPENAI_API_KEY=sk-...

# Optional: Fallback provider for rate limiting
AI_FALLBACK_DRIVER=groq
GROQ_API_KEY=gsk_...
```

## Configuration

The `config/fields-ai.php` file allows you to customize:

```php
return [
    // Default AI provider
    'default_provider' => env('AI_DRIVER', 'openai'),
    
    // Default model
    'default_model' => env('AI_MODEL', 'gpt-4o'),
    
    // Fallback provider (optional)
    'fallback_provider' => env('AI_FALLBACK_DRIVER', null),
    
    // Generation parameters
    'default_max_tokens' => 150,
    'default_temperature' => 0.7,
    
    // Content templates
    'content_templates' => [
        'product_description' => 'Write a compelling product description for a [product name].',
        'blog_intro' => 'Write an engaging introduction for a blog post about [topic].',
        // Add your custom templates
    ],
    
    // Content actions
    'content_actions' => [
        'refine' => 'Refine the following text: %s',
        'expand' => 'Expand on the following text: %s',
        'shorten' => 'Shorten the following text: %s',
        'translate' => 'Translate the following text to Ukrainian: %s',
    ],
];
```

## Usage for Developers

### Adding AI to Filament Form Fields

Add AI generation capabilities to any text field using the `withAI()` method:

#### Basic Usage

```php
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\MarkdownEditor;
use Broqit\FilamentEditorJs\Forms\Components\EditorJs;

TextInput::make('title')
    ->withAI(),

Textarea::make('description')
    ->withAI(),

RichEditor::make('content')
    ->withAI(),

MarkdownEditor::make('content')
    ->withAI(),

EditorJs::make('content')
    ->withAI(),
```

#### With Custom Options

```php
RichEditor::make('content')
    ->withAI([
        'max_tokens' => 500,
        'temperature' => 0.8,
        'chunk_long_text' => true,
    ])
```

**Note:** Provider and model are selected by users in the modal. You don't need to hardcode them!

## Usage for End-Users

### Generating Content

1. In a form with AI-enabled fields, click the **"Generate with AI"** sparkle icon ✨
2. A modal opens with options:
   - **AI Provider** - Select from configured providers (OpenAI, Anthropic, Gemini, etc.)
   - **Model** - Choose from available models (automatically loaded from Laravel AI)
   - **Use Existing Content** - Toggle to modify current content
   - **Prompt/Template** - Enter custom prompt or select template

3. For **new content generation**:
   - Enter a prompt like "Write a blog post about Laravel AI"
   - Or select from pre-defined templates

4. For **modifying existing content**:
   - Toggle "Use Existing Content"
   - Choose action: Refine, Expand, Shorten, or Translate
   - The AI will process your current content

5. Click **"Generate"** and the content appears in your field!

## Features

### 🌟 Multi-Provider Support
Select from multiple AI providers in the same form:
- OpenAI (GPT-4o, GPT-4, GPT-3.5)
- Anthropic (Claude Sonnet, Opus, Haiku)
- Google Gemini (2.0 Flash, 1.5 Pro)
- Groq (Llama 3.3, Mixtral) - fastest!
- And more...

### 🔄 Automatic Fallback
Configure a fallback provider for rate limiting scenarios:
```env
AI_DRIVER=openai
AI_FALLBACK_DRIVER=groq
```

### 📝 Content Actions
Transform existing content:
- **Refine** - Improve grammar and style
- **Expand** - Add more details
- **Shorten** - Condense content
- **Translate** - Translate to any language

### 🎯 Smart Features
- Automatic chunking for long texts
- Incomplete sentence removal
- Support for HTML, Markdown, and EditorJs
- Real-time provider/model selection

## Supported Field Types

- ✅ `TextInput`
- ✅ `Textarea`
- ✅ `RichEditor`
- ✅ `MarkdownEditor`
- ✅ `EditorJs` (broqit/filament-editorjs)

## Advanced Configuration

### Custom Templates

Add your own templates in `config/fields-ai.php`:

```php
'content_templates' => [
    'product_review' => 'Write a detailed product review for [product].',
    'email_campaign' => 'Create an email campaign for [campaign purpose].',
],
```

### Custom Actions

Define custom content transformation actions:

```php
'content_actions' => [
    'summarize' => 'Summarize the following text in 3 sentences: %s',
    'formalize' => 'Rewrite the following text in a formal tone: %s',
],
```

## Rate Limiting & Fallback

When your primary AI provider hits rate limits, the package can automatically switch to a fallback provider:

```env
# Primary provider
AI_DRIVER=openai
OPENAI_API_KEY=sk-...

# Fallback provider (optional)
AI_FALLBACK_DRIVER=groq
GROQ_API_KEY=gsk_...
```

**How it works:**
1. Request fails with `RateLimitException`
2. Package automatically retries with fallback provider
3. User receives notification about which provider was used
4. Content generation continues seamlessly

**Recommended fallback providers:**
- **Groq** - Fastest inference, generous free tier
- **Anthropic** - High quality, good rate limits
- **Gemini** - Google's offering with generous quotas

## Support

- **Issues**: [GitHub Issues](https://github.com/broqit/fields-ai/issues)
- **Email**: ya.qnut@gmail.com
- **Laravel AI Docs**: [laravel.com/docs/ai](https://laravel.com/docs/ai)

## Programmatic Usage

Use the AI service directly in your code:

```php
use Broqit\FieldsAi\FieldsAi;

$ai = app(FieldsAi::class);

// Generate new content
$content = $ai->generateContent(
    prompt: 'Write a blog post about Laravel AI',
    text: null,
    options: [
        'provider' => 'anthropic',
        'model' => 'claude-sonnet-4-6',
        'temperature' => 0.7,
        'max_tokens' => 500,
    ]
);

// Transform existing content
$refined = $ai->generateContent(
    prompt: 'Refine the following text: %s',
    text: $existingContent,
    options: [
        'provider' => 'openai',
        'model' => 'gpt-4o',
    ]
);
```

## Migration from v1.x

If upgrading from v1.x (which used `openai-php/laravel`):

### Breaking Changes

1. **Dependency Change:**
   ```bash
   # Remove old dependency
   composer remove openai-php/laravel
   
   # Add Laravel AI
   composer require laravel/ai
   ```

2. **Configuration:**
   - Old: `config/openai.php`
   - New: `config/ai.php` (Laravel AI config)
   - Update `config/fields-ai.php` with new options

3. **Environment Variables:**
   ```env
   # Old
   OPENAI_API_KEY=sk-...
   
   # New (supports multiple providers)
   AI_DRIVER=openai
   OPENAI_API_KEY=sk-...
   ANTHROPIC_API_KEY=sk-ant-...
   ```

4. **Code Changes:**
   - Provider/model selection now happens in UI (no code changes needed)
   - Custom models can be entered manually in the modal
   - Fallback provider configured via `AI_FALLBACK_DRIVER`

## Credits

- [Roman Borkunov](https://github.com/broqit)
- [Laravel AI Team](https://github.com/laravel/ai)
- All contributors

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
