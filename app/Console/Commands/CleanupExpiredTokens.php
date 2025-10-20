<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;

class CleanupExpiredTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sanctum:cleanup-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired Sanctum tokens';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredTokens = PersonalAccessToken::where('expires_at', '<', now())->count();
        
        if ($expiredTokens > 0) {
            PersonalAccessToken::where('expires_at', '<', now())->delete();
            $this->info("Cleaned up {$expiredTokens} expired tokens.");
        } else {
            $this->info('No expired tokens found.');
        }

        return Command::SUCCESS;
    }
}
