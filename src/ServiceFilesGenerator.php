<?php

namespace Warfee\ServiceFilesGenerator;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServiceFilesGenerator
{
	private $databaseDriver;
	private $useSoftDelete;
	private $tableParameterKey;


	public function __construct($databaseDriver,$useSoftDelete){

		$this->databaseDriver = $databaseDriver;
		$this->useSoftDelete = $useSoftDelete;

	}

	public function fetchDatabaseTables(){

		try{

			if($this->databaseDriver == 'mysql'){

				return $this->mySqlDriver();

			}elseif($this->databaseDriver == 'sqlite'){

				return $this->sqlLiteDriver();

			}else{

				return false;
			}

		}catch(\Exception $e){

			return false;
		}
	}

    public function mySqlDriver(){

        $this->tableParameterKey = 'Tables_in_'. env('DB_DATABASE');

        return DB::connection('mysql')->select('SHOW TABLES');

    }

    public function sqlLiteDriver(){

    	$tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

    }

    public function stubTemplate(){

    	$stubPathFile = $this->useSoftDelete == false ? 'service-generator.stub' : 'service-generator-soft-delete.stub';

    	$stubPath = base_path('vendor/warfee/service-files-generator/src/Stubs/'.$stubPathFile);

    	return $this->getStubContent($stubPath);

    }

    public function getStubContent($stubPath){

    	return File::get($stubPath);
    }

    public function getServiceName($tableName){

    	return Str::studly(Str::replace('_', ' ', $tableName)).'Services';
    }

    public function getServiceDirectory($tableName){

    	return app_path("Services/{$serviceName}.php");
    }

    public function getTableColumn(){

    	return DB::connection($this->databaseDriver)
    			->getSchemaBuilder()
    			->getColumnListing($table->$parameterKey);
    }

    public function generateColumnStubLayout($columns){

    	$columnLayouts = collect($columns)->map(function ($column) {
                            return "                '{$column}' => \$request->" . $column;
                        })->implode(",\n");

    	return $columnLayouts;
    }

    public function placingStubParameter($stubContent,$tables){

    	foreach (collect($tables) as $table) {

    		$tableName = $table->{$this->tableParameterKey};

    		$serviceName = $this->getServiceName($tableName);
		    $serviceDir = $this->getServiceDirectory($serviceName);

	        $columns = $this->getTableColumn($);
	        $columnLayouts = $this->generateColumnStubLayout($columns);

	        $stubContent = Str::replace('{{ serviceName }}', $serviceName, $stubContent);
	        $stubContent = Str::replace('{{ tableName }}', $tableName, $stubContent);
	        $stubContent = Str::replace('{{ tableColumns }}', $columnLayouts, $stubContent);

	        $this->generateServiceFile($serviceDir,$stubContent);

    	}


    }

    public function generateServiceFile($serviceDir,$stubContent){

	    if (!Storage::exists($serviceDir)) {
            File::put($serviceDir,$stubContent);

        }
    }

}
