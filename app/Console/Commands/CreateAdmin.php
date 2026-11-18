<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

#[Signature('admin:create {email} {--name=Administrator}')]
#[Description('Create an administrator with a generated password')]
class CreateAdmin extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = Str::lower(trim($this->argument('email')));
        if (Validator::make(['email' => $email], ['email' => ['required', 'email', 'max:255']])->fails()) {
            $this->error('Alamat email tidak valid.');

            return self::FAILURE;
        }
        if (User::where('email', $email)->exists()) {
            $this->error('Email sudah terdaftar. Akun yang ada tidak diubah.');

            return self::FAILURE;
        }
        $password = Str::random(20);
        $user = new User(['name' => $this->option('name'), 'email' => $email, 'password' => $password]);
        $user->is_admin = true;
        $user->save();
        $this->info('Administrator berhasil dibuat.');
        $this->line('Email: '.$email);
        $this->line('Kata sandi: '.$password);

        return self::SUCCESS;
    }
}
