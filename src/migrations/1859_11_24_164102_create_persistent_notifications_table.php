<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePersistentNotificationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// Creates the users table
        Schema::create('persistent_notifications', function($table)
        {
            $table->engine = 'InnoDB';

            $table->increments('id')->unsigned();
			$table->integer('user_id')->unsigned()->index();
			$table->string('group', 128);
			$table->enum('type', array('error', 'success', 'warning', 'info'));
	        $table->string('message');
	        $table->string('url');
	        $table->boolean('seen')->default(false);
	        $table->timestamps();

	        $table->foreign('user_id')		 // assumes a users table
      			  ->references('id')->on('users')
      			  ->onDelete('cascade');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('persistent_notifications', function(Blueprint $table) 
		{
            $table->dropForeign('persistent_notifications_user_id_foreign');
        });

		Schema::drop('persistent_notifications');
	}

}
