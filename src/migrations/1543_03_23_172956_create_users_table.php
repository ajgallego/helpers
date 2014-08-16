<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Creates the users table
        Schema::create('users', function ($table) 
        {
            $table->engine = 'InnoDB';

            $table->increments('id')->unsigned();
            $table->string('email')->unique()->index();
            $table->string('password');
            $table->string('confirmation_code');
            $table->string('remember_token');
            $table->boolean('confirmed')->default(false);
            $table->timestamps();
        });

        // Creates password reminders table
        Schema::create('password_reminders', function ($table) 
        {
            $table->engine = 'InnoDB';
            
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('password_reminders');
        Schema::drop('users');
    }
}
