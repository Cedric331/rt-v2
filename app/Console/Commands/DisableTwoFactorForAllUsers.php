<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DisableTwoFactorForAllUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '2fa:disable-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disable two-factor authentication for all users';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = User::whereNotNull('two_factor_confirmed_at')
            ->orWhereNotNull('two_factor_secret')
            ->update([
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
            ]);

        $this->info("Two-factor authentication disabled for {$count} user(s).");

        return Command::SUCCESS;
    }
}

