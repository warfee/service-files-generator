<?php

namespace Warfee\ServiceFilesGenerator;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ServiceFilesGenerator
{
	private $databaseDriver;
	private $useSoftDelete;
	private $tableParameterKey;


	public function __construct($databaseDriver,$useSoftDelete){

		$this->databaseDriver = $databaseDriver;
		$this->useSoftDelete = $useSoftDelete;

		$this->checkServiceDiretoryPath();

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


    }

    public function generateServiceFile($serviceDir,$stubContent){

	    if (!Storage::exists($serviceDir)) {
            File::put($serviceDir,$stubContent);

        }
    }

    public function checkServiceDiretoryPath(){

    	$directory = app_path("Services");

    	if (File::exists($directory) != 1) {

    		mkdir($directory, 0777, true);

    		return true;

    	}
    }

}
