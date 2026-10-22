<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateAdmin extends Command
{
    protected $signature = 'katniss:create-admin {email : Email admin} {--name=Admin : Nama admin}';

    protected $description = 'Membuat akun admin (atau menjadikan user yang sudah ada sebagai admin) dengan aman.';

    public function handle(): int
    {
        $email = (string) $this->argument('email');

        if (Validator::make(['email' => $email], ['email' => 'required|email'])->fails()) {
            $this->error('Format email tidak valid.');

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            if (! $this->confirm("User {$email} sudah ada. Jadikan admin?", false)) {
                return self::SUCCESS;
            }
            $user->forceFill(['role' => Role::Admin])->save();
            $this->info("User {$email} sekarang admin.");

            return self::SUCCESS;
        }

        $password = (string) $this->secret('Password (minimal 8 karakter, ada huruf dan angka)');
        $confirmation = (string) $this->secret('Ulangi password');

        $validator = Validator::make(
            ['password' => $password, 'password_confirmation' => $confirmation],
            ['password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()]],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $user = new User(['name' => (string) $this->option('name'), 'email' => $email, 'password' => $password]);
        $user->role = Role::Admin;
        $user->email_verified_at = now();
        $user->save();

        $this->info("Admin {$email} berhasil dibuat.");

        return self::SUCCESS;
    }
}
