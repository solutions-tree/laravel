<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Http;

class SyncFundNav extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nav:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is basically designed to sync the nav values';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
		
		
		//lets write our logic here...
		$mutual_fund = DB::table('investment')->select('scheme_code')->groupBy('scheme_code')->get();
		//$arr = [];
		//lets traverse..
		if ($mutual_fund) {
			foreach ($mutual_fund as $fund) {
				$this->updateNavToScheme($fund->scheme_code);
				
				//$arr[] = $fund->scheme_code;
				
			}
			//. implode(",",$arr)
			 $this->info('All Mutual fund Nav updated successfully');
			 
		} else {
			
			 $this->info('Nothing to update..');
		}
		
		
		
    }
	
	// nav scheme...
	function updateNavToScheme($scheme_code) {
		
		if (!$scheme_code) return;
		
		//lets find out if the nav already in db or not.. 
		$mutual_fund = DB::table('mutual_fund_list')->Where('code', '=',  $scheme_code)->first();
		
		// lets see, if we do not get any fund value...
		if ($mutual_fund) {
				 
			//lets fetch from the remote nav server and update it..
				$response = Http::get('https://api.mfapi.in/mf/' . $scheme_code);
	
				// lets get the latest value ...
				$data = $response->json();
				
				if (isset($data['data'][0])) {
					
					//lets update the memory with nav and its date...
					$res = DB::table('mutual_fund_list')->where('id', $mutual_fund->id)->update([
						'nav' => (float) $data['data'][0]['nav'],
						'nav_updated_at' => date('Y-m-d' , strtotime($data['data'][0]['date']))
					]);
					
					
				}
			
		}
	}
	
}
