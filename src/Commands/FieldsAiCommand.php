<?php

namespace Broqit\FieldsAi\Commands;

use Illuminate\Console\Command;

class FieldsAiCommand extends Command
{
    public $signature = 'fields-ai:install';

    public $description = 'Install and set up FieldsAi package';

    public function handle(): int
    {
        $this->info('Setting up FieldsAi package...');

        // Publish configuration
        $this->call('vendor:publish', [
            '--provider' => 'Broqit\FieldsAi\FieldsAiServiceProvider',
            '--tag' => 'fields-ai-config'
        ]);

        $this->call('vendor:publish', [
            '--provider' => 'Broqit\FieldsAi\FieldsAiServiceProvider',
            '--tag' => 'fields-ai-langs'
        ]);

        //Create openai-php/laravel Config File
        $this->call('openai:install');

        // Create storage link
        $this->call('storage:link');

        $this->info('FieldsAi package has been set up successfully!');
        $this->info('Please review the configuration file at config/fields-ai.php');

        return self::SUCCESS;
    }
}