<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvestmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        //
		Schema::create('investment', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('scheme_id');
			$table->string('scheme_code' , 15);
            $table->integer('client_id')->unsigned();
			$table->decimal('investment_amt',12,2)->unsigned();
			$table->decimal('investment_unit',12,2)->unsigned();
			$table->date('investment_date');
			$table->integer('created_by_id')->unsigned();
            $table->timestamps();
		});
	}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
