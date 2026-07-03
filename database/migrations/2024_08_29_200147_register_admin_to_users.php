<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use App\User;

class RegisterAdminToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Crear un usuario administrador
        $name = 'Admin';
        $email = 'admin@gmail.com';
        $password = Str::random(10);

        // Verifica si el usuario ya existe antes de crearlo
        if (!User::where('email', $email)->exists()) {
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);

            $content = "Usuario administrador: \n";
            $content .= "Nombre: $name\n";
            $content .= "Email: $email\n";
            $content .= "Contraseña: $password\n";

            $filePath = base_path('usuario_admin.txt');
            File::put($filePath, $content);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {}
}
