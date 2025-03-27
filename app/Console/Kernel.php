<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\uploadDumpLogs::class,
        Commands\monitoring::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule){
/*
		try{
			$schedule->command('uploadDumpLogs:cron')->everyMinute()->withoutOverlapping();
		}catch(\Throwable $ex){
			\Log::channel('single')->info( "uploadDumpLogs"."\n".$ex->getMessage() );
		}
*/
		try{
			$schedule->command("monitoring:cron")->everyMinute()->withoutOverlapping()->when(function(){
				if(file_exists(env('monitoring_location')."/monitoring.ini")){
					$ini = parse_ini_file(env('monitoring_location')."/monitoring.ini");
					if(($ini['lastrun']+($ini['frequency']*60))<=time()) return true;
				}else{ \Log::channel('monitoring')->info("monitoring.ini not found"); }
				return false;
			});
		}catch(\Throwable $ex){
			\Log::channel('monitoring')->info( $ex->getMessage() );
		}
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
