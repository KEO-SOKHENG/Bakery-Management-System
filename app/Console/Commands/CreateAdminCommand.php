<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'admin:create')]
class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create
                            {--name= : Full name of the administrator}
                            {--username= : Unique username}
                            {--email= : Unique email address}
                            {--password= : Optional password (prompted securely if omitted)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely create or update an initial administrator account with secure password hashing';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Administrator Name', 'System Admin');
        $username = $this->option('username') ?: $this->ask('Username', 'admin');
        $email = $this->option('email') ?: $this->ask('Email Address', 'admin@bakery.com');

        $validator = Validator::make([
            'name'     => $name,
            'username' => $username,
            'email'    => $email,
        ], [
            'name'     => 'required|string|max:100',
            'username' => 'required|string|max:50',
            'email'    => 'required|email|max:100',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        $password = $this->option('password');
        if (empty($password)) {
            $password = $this->secret('Password (min 8 characters)');
            $confirmation = $this->secret('Confirm Password');

            if ($password !== $confirmation) {
                $this->error('Passwords do not match.');
                return self::FAILURE;
            }
        }

        if (strlen((string) $password) < 6) {
            $this->error('Password must be at least 6 characters.');
            return self::FAILURE;
        }

        $user = User::updateOrCreate(
            ['username' => $username],
            [
                'name'                 => $name,
                'email'                => $email,
                'password'             => Hash::make($password),
                'role'                 => 'admin',
                'status'               => 'active',
                'must_change_password' => false,
            ]
        );

        $this->newLine();
        $this->components->info('Administrator Account Ready');
        $this->components->twoColumnDetail('Username', (string) $user->username);
        $this->components->twoColumnDetail('Email', (string) $user->email);
        $this->components->twoColumnDetail('Role', (string) $user->role);
        $this->components->twoColumnDetail('Status', "<fg=green>{$user->status}</>");
        $this->newLine();

        return self::SUCCESS;
    }
}
