<?php

namespace Warfee\ServiceFilesGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Warfee\ServiceFilesGenerator\ServiceFilesGenerator;
use Illuminate\Support\Facades\Log;

class GenerateServiceFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service-generator:create';

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
        $driver = $this->choice(
            'Please choose database driver',
            ['mysql', 'sqlite'],
            $defaultIndex = 0,
            $maxAttempts = null,
            $allowMultipleSelections = false
        );

        $softDelete = $this->choice(
            'Do you implement soft delete for your service table? All your table need to have deleted_at column field.',
            ['true', 'false'],
            $defaultIndex = 0,
            $maxAttempts = null,
            $allowMultipleSelections = false
        );
        

        $generatorSetup = new ServiceFilesGenerator($driver,$softDelete);
        $stubContent = $generatorSetup->stubTemplate();
        $tables = $generatorSetup->fetchDatabaseTables();

        if(empty($tables)){

            $this->error('Error! Unable to connect with database driver. Please check your database driver connection');
            
            return 1;
        }

        $generate = $generatorSetup->placingStubParameter($stubContent,$tables);
    }
        
}

