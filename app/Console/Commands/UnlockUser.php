<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Console\Command;

class UnlockUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:unlock {username=admin} {--all : Unlock all blocked users}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unlock a blocked user account';

    protected UserRepository $userRepository;

    /**
     * Create a new command instance.
     */
    public function __construct(UserRepository $userRepository)
    {
        parent::__construct();
        $this->userRepository = $userRepository;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('all')) {
            $blockedUsers = User::where('status', 'temp_blocked')->get();

            if ($blockedUsers->isEmpty()) {
                $this->info('No blocked users found.');
                return 0;
            }

            foreach ($blockedUsers as $user) {
                $this->userRepository->resetFailedAttempts($user);
                $this->info("User '{$user->username}' has been unlocked.");
            }

            $this->info('All blocked users have been unlocked.');
            return 0;
        }

        $username = $this->argument('username');
        $user = $this->userRepository->findByUsername($username);

        if (!$user) {
            $this->error("User '{$username}' not found.");
            return 1;
        }

        if ($user->status !== 'temp_blocked') {
            $this->info("User '{$username}' is not blocked.");
            return 0;
        }

        $this->userRepository->resetFailedAttempts($user);
        $this->info("User '{$username}' has been successfully unlocked.");
        $this->info("You can now login with the password: Admin12345*");

        return 0;
    }
}
