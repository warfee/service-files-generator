<?php

namespace Warfee\ServiceFilesGenerator;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Warfee\ServiceFilesGeneratorFacade\Skeleton\SkeletonClass
 */
class ServiceFilesGeneratorFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'services-files-generator';
    }
}
