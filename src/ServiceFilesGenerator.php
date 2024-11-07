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

	private function fetchDatabaseTables(){

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

    private function mySqlDriver(){

        $this->columnParameterKey = 'Tables_in_'. env('DB_DATABASE');

        return DB::connection('mysql')->select('SHOW TABLES');

    }

    private function sqlLiteDriver(){

    	$tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

    }

    private function stubTemplate(){

    	$stubPathFile = $this->useSoftDelete == false ? 'service-generator.stub' : 'service-generator-soft-delete.stub';

    	$stubPath = base_path('packages/warfee/mysql-services-generator/src/Stubs/'.$stubPathFile);

    	return $this->getStubContent($stubPath);

    }

    private function getStubContent($stubPath){

    	return File::get($stubPath);
    }

    private function getServiceName($tableName){

    	return Str::studly(Str::replace('_', ' ', $table->$parameterKey)).'Services';
    }

    private function getServiceDirectory($tableName){

    	return app_path("Services/{$serviceName}.php");
    }

    private function getTableColumn(){

    	return DB::connection($this->databaseDriver)
    			->getSchemaBuilder()
    			->getColumnListing($table->$parameterKey);
    }

    private function generateColumnStubLayout($columns){

    	$columnLayouts = collect($columns)->map(function ($column) {
                            return "                '{$column}' => \$request->" . $column;
                        })->implode(",\n");

    	return $columnLayouts;
    }

    private function placingStubParameter($stubContent,$tables){

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

    private function generateServiceFile($serviceDir,$stubContent){

	    if (!Storage::exists($serviceDir)) {
            File::put($serviceDir,$stubContent);

        }
    }

}
