<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateApiTokenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:create-token {email} {--name=RME-Token}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an API token for a specific user to be used by external systems (e.g. RME).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $tokenName = $this->option('name');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email {$email} not found.");
            return;
        }

        $token = $user->createToken($tokenName);

        $this->info("Token created successfully for {$user->name} ({$email}).");
        $this->warn("Token: " . $token->plainTextToken);
        $this->line("Please copy this token as it will not be shown again.");
    }
}
