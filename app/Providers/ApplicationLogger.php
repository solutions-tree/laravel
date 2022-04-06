<?php

namespace App\Providers;

use DB;
use Illuminate\Support\Facades\Log; 

use Illuminate\Support\ServiceProvider;

class ApplicationLogger extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
	 
	public function boot() {
        DB::listen(function($query) {
			//Log::useFiles(storage_path().'/logs/database_query.log');
            Log::info(
                $query->sql,
                $query->bindings,
                $query->time
            );
        });
    }
}
