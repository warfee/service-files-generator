<?php

namespace Warfee\ServiceFilesGenerator;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class ServiceFilesGenerator
{
	private $databaseDriver;
	private $useSoftDelete;
	private $tableParameterKey;
	private $serviceDirectoryPath;
	private $helperDirectoryPath;

	public function __construct($databaseDriver,$useSoftDelete){

		$this->databaseDriver = $databaseDriver;
		$this->useSoftDelete = $useSoftDelete;

		$this->serviceDirectoryPath = app_path("Services");
		$this->helperDirectoryPath = app_path("Helpers");

		$this->createServiceDiretoryPath($this->serviceDirectoryPath);

	}

	public function fetchDatabaseTables(){

		if($this->databaseDriver == 'mysql'){

			return $this->mySqlDriver();

		}elseif($this->databaseDriver == 'sqlite'){

			return $this->sqlLiteDriver();
		}
	}

    public function mySqlDriver(){

        $this->tableParameterKey = 'Tables_in_'. env('DB_DATABASE');

        $tables = DB::connection($this->databaseDriver)->select('SHOW TABLES');

        return $tables;

    }

    public function sqlLiteDriver(){

    	$this->tableParameterKey = 'name';

    	$tables = DB::connection($this->databaseDriver)->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

    	return $tables;
    }

    public function stubTemplate(){

    	$stubPathFile = "service-generator.stub";

    	if($this->useSoftDelete == "true"){

    		$stubPathFile = "service-generator-soft-delete.stub";
    	}

    	$stubPath = base_path('vendor/warfee/service-files-generator/src/Stubs/'.$stubPathFile);

    	return $this->getStubContent($stubPath);

    }

    public function getStubContent($stubPath){

    	return File::get($stubPath);
    }

    public function getServiceName($tableName){

    	$tableName = preg_replace('/^\d+/', '', $tableName);

    	return Str::studly(Str::replace('_', ' ', $tableName)).'Services';
    }

    public function getServiceDirectory($serviceName){

    	return app_path("Services/{$serviceName}.php");
    }

    public function getTableColumn($tableName){

    	$columns = DB::connection($this->databaseDriver)
    			->getSchemaBuilder()
    			->getColumnListing($tableName);

    	$filteredColumns = array_diff($columns, ['created_at', 'updated_at', 'deleted_at']);

		return $filteredColumns;
    }

    public function generateColumnStubLayout($columns){

    	$columnLayouts = collect($columns)->map(function ($column) {
                            return "                '{$column}' => \$request->" . Str::replace('-', '_', $column);
                        })->implode(",\n");

    	return $columnLayouts;
    }

    public function placingStubParameter($stubContent,$tables){

    	$rawTemplate = $stubContent;

    	foreach (collect($tables) as $table) {

    		$tableName = $table->{$this->tableParameterKey};

    		$serviceClassName = $this->getServiceName($tableName);
    		
		    $serviceDir = $this->getServiceDirectory($serviceClassName);

	        $columns = $this->getTableColumn($tableName);
	        $columnLayouts = $this->generateColumnStubLayout($columns);

	        $stubContent = Str::replace('{{ serviceName }}', $serviceClassName, $stubContent);
	        $stubContent = Str::replace('{{ tableName }}', $tableName, $stubContent);
	        $stubContent = Str::replace('{{ tableColumns }}', $columnLayouts, $stubContent);

	        $this->generateServiceFile($serviceDir,$stubContent);

	        $stubContent = $rawTemplate;

    	}

    	$this->checkHelperDiretoryPath($this->helperDirectoryPath);


    }

    public function generateServiceFile($serviceDir,$stubContent){

	    if (!Storage::exists($serviceDir)) {
            File::put($serviceDir,$stubContent);

        }
    }

    public function checkServiceFiles(){

    	if(!count(File::files($this->serviceDirectoryPath))){

    		return false;
    	}


    	return true;
    }



    public function createServiceDiretoryPath($directory){

    	if (File::exists($directory) != 1) {

    		mkdir($directory, 0777, true);
    	}

    }

    public function checkHelperDiretoryPath($directory){

    	$errorLoggerPath = $directory . '/ErrorLogger.php';
    	$serviceReturnerPath = $directory . '/ServiceReturner.php';

    	if (!File::exists($errorLoggerPath) || !File::exists($serviceReturnerPath)) {

    		Artisan::call('vendor:publish --tag=service-generator-helpers');

    	}

    }


    

}
