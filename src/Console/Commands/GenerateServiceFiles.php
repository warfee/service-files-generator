<?php

namespace Warfee\ServiceFilesGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Warfee\ServiceFilesGenerator\ServiceFilesGenerator;

class GenerateServiceFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service-generator:create {driver=mysql} {softDelete=false}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create service files and basic method based on MySQL database tables';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $driver = $this->argument('driver');
        $softDelete = $this->argument('softDelete');
        

        $generatorSetup = new ServiceFilesGenerator($driver,$softDelete);
        $stubContent = $generatorSetup->stubTemplate();
        $tables = $generatorSetup->fetchDatabaseTables();
        $generate = $generatorSetup->placingStubParameter($stubContent,$tables);
    }
        
}
