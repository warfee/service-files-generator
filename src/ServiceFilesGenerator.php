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
	private $columnParameterKey;


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

        $this->columnParameterKey = 'Tables_in_'. env('DB_DATABASE');

        return DB::connection('mysql')->select('SHOW TABLES');

    }

    public function sqlLiteDriver(){

    	$tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

    }

    public function stubTemplate(){

    	$stubPathFile = $this->useSoftDelete == false ? 'service-generator.stub' : 'service-generator-soft-delete.stub';

    	$stubPath = base_path('packages/warfee/mysql-services-generator/src/Stubs/'.$stubPathFile);

    	return $this->getStubContent($stubPath);

    }

    public function getStubContent($stubPath){

    	return File::get($stubPath);
    }

    public function getServiceName($tableName){

    	return Str::studly(Str::replace('_', ' ', $table->$parameterKey)).'Services';
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

    		$serviceName = $this->getServiceName($table->$parameterKey);
		    $serviceDir = $this->getServiceDirectory($serviceName);

	        $columns = $this->getTableColumn();
	        $columnLayouts = $this->generateColumnStubLayout($columns);

	        $stubContent = Str::replace('{{ serviceName }}', $serviceName, $stubContent);
	        $stubContent = Str::replace('{{ tableName }}', $table->$parameterKey, $stubContent);
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
