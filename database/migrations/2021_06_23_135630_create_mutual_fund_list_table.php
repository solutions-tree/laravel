<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMutualFundListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mutual_fund_list', function (Blueprint $table) {
            $table->id();
			$table->string('amc', 100);
			$table->string('code', 20)->unique();
			$table->string('scheme_name', 500);
			$table->string('scheme_type', 500);
			$table->string('scheme_category', 500);
			$table->string('scheme_nav_name', 500);
			$table->string('scheme_min_amt', 10);
			$table->string('launch_date', 20);
			$table->string('closure_date', 20);
			$table->string('isin', 500);
			$table->decimal('nav',12,5)->unsigned()->nullable();
			$table->dateTime('nav_updated_at', $precision = 0)->nullable();
			
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
        Schema::dropIfExists('mutual_fund_list');
    }
}
