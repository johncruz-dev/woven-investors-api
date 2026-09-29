<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateApiTokenCommand extends Command
{
    protected $signature = 'security:create-api-token
                            {email : The user email address}
                            {name=api : A descriptive name for the token}
                            {--abilities=* : Token abilities (default: import,read)}';

    protected $description = 'Create a Sanctum API token for programmatic access';

    public function handle(): int
    {
        $user = User::query()->where('email', $this->argument('email'))->first();

        if ($user === null) {
            $this->error('No user found with that email address.');

            return self::FAILURE;
        }

        $abilities = $this->option('abilities');

        if ($abilities === []) {
            $abilities = ['import', 'read'];
        }

        $token = $user->createToken($this->argument('name'), $abilities);

        $this->info('API token created successfully.');
        $this->line('');
        $this->warn('Store this token securely — it will not be shown again:');
        $this->line($token->plainTextToken);

        return self::SUCCESS;
    }
}
