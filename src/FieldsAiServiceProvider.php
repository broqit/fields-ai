<?php

namespace Broqit\FieldsAi;

use Broqit\FilamentEditorJs\Forms\Components\EditorJs;
use Camya\Filament\Forms\Components\TitleWithSlugInput;
use Filament\Forms\Components\MarkdownEditor;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Broqit\FieldsAi\Commands\FieldsAiCommand;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Broqit\FieldsAi\Forms\Actions\GenerateContentAction;
use Broqit\FieldsAi\Services\OpenAIService;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Illuminate\Support\Facades\Artisan;

class FieldsAiServiceProvider extends PackageServiceProvider
{
    public static string $name = 'fields-ai';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasTranslations()
            ->hasCommand(FieldsAiCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(FieldsAi::class, function ($app) {
            return new FieldsAi(
                $app->make(OpenAIService::class),
            );
        });
    }
    public function packageBooted(): void
    {
        $this->registerWithAIMacro(TextInput::class);
        $this->registerWithAIMacro(Textarea::class);
        $this->registerWithAIMacro(RichEditor::class);
        $this->registerWithAIMacro(EditorJs::class);
        $this->registerWithAIMacro(MarkdownEditor::class);
    }

    protected function registerWithAIMacro(string $componentClass)
    {
        $componentClass::macro('withAI', function (array $options = []) {
            return $this->hintAction(
                app(GenerateContentAction::class)->execute($this, null, [], $options)
            );
        });
    }

    protected function isPackageInstalled(string $package): bool
    {
        $installedPackages = json_decode(file_get_contents(base_path('composer.json')), true)['require'] ?? [];
        return isset($installedPackages[$package]);
    }

    protected function needsUpgrade(string $package, string $requiredVersion): bool
    {
        $installedVersion = $this->getInstalledVersion($package);
        return version_compare($installedVersion, trim($requiredVersion, '^~'), '<');
    }

    protected function getInstalledVersion(string $package): string
    {
        $composerLock = json_decode(file_get_contents(base_path('composer.lock')), true);
        foreach ($composerLock['packages'] as $installedPackage) {
            if ($installedPackage['name'] === $package) {
                return $installedPackage['version'];
            }
        }
        return '0.0.0';
    }
}
