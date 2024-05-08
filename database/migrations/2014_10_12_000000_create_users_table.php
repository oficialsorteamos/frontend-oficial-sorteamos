<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->unsignedBigInteger('gender_id')->comment('Gênero do usuário. (Masculino, Feminino, etc.)');
            $table->string('cpf')->nullable()->comment('CPF do usuário');
            $table->timestamp('birthday')->nullable()->comment('Data de aniversário do usuário.');
            $table->string('phone')->comment('Telefone do usuário');
            $table->string('avatar')->nullable()->comment('Url da imagem do usuário');
            $table->string('status')->default('A')->comment('Status do Usuário. (Ativo, Inativo, etc.)');
            $table->timestamp('date_deleted')->nullable()->comment('Data que o usuário foi deletado.');
            $table->unsignedBigInteger('situation_user_id')->default(1)->comment('Situação do Usuário. (Online, Offline, etc)');
            $table->boolean('audio_notification_chat')->default(1)->comment('Habilita ou desabilita o áudio durante a chegada de um novo contato no chat');
            $table->unsignedBigInteger('user_id')->comment('Id do usuário que inseriu o referido usuário');
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('situation_user_id')
                    ->references('id')
                    ->on('man_type_situations_users')
                    ->onDelete('cascade');
            
            $table->foreign('gender_id')
                    ->references('id')
                    ->on('sys_genders')
                    ->onDelete('cascade');
        });

        // Insert default values
        DB::table('users')->insert(
            array(
                [
                    'id' => 1,
                    'name' => 'Bot',
                    'username' => 'bot',
                    'email' => 'bot@bot.com',
                    'password' => Hash::make('admin'),
                    'gender_id' => 3,
                    'phone' => '+5527999999999',
                    'status' => 'A',
                    'situation_user_id' => 1,
                ],
                [
                    'id' => 2,
                    'name' => 'Admin',
                    'username' => 'admin',
                    'email' => 'admin@admin.com',
                    'password' => Hash::make('admin'),
                    'gender_id' => 3,
                    'phone' => '+5527999999999',
                    'status' => 'A',
                    'situation_user_id' => 1,
                ],
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
