<?php
/**
 * DeviceAwareDev_Controller: Used for Dev and re-build routines
 *  -   Rebuild the images cache
 * 
 * Copyright (c) 2011, Thierry Francois / COLYMBA
 * Licensed under the MIT license
 * http://colymba.com
 * 
 * @package DeviceAware
 * 
 * @copyright Thierry Francois / COLYMBA 
 * @author thierry@colymba.com
 * 
 * @requires DeviceAware
 */
class DeviceAwareDev_Controller extends Controller
{
    /**
     * Check permission before executing anything
     */    
    function init()
    {
		parent::init();
        
        // access restriction
		$canAccess = (
            Director::isDev() 
			|| Director::is_cli() 
			// Its important that we don't run this check if dev/build was requested
			|| Permission::check("ADMIN")
		);
		if(!$canAccess) return Security::permissionFailure($this);
    }
    
    /**
     * Perform a (re)build or image cache for a specific Page object or a whole class
     * @param mixed set through URL parameters: 'device_aware/dev/build/$Class/$ID'
     * $Class string classname to be processes
     * $ID int|string page ID to be process or 'all' to process all published pages of $Class
     */
    public function build()
    {
        echo 'Device Aware cache building...'.'<br/>';
        echo 'Can take a very long time, refresh the page if you get a timeout error.'.'<br/><br/>';
        
        //print_r(Director::URLParam('Class') . ': ' . Director::URLParam('ID'));
        $className = Director::URLParam('Class');
        $classID = Director::URLParam('ID');
        
        if (!$className)
        {
            echo 'No Class name specified. Please use URL parameters.';
        }else{
            if (!$classID)
            {
                echo 'No Class ID specified. Please use URL parameters.';
            }else{
                if ( $classID == 'all' )
                {
                    //process all published pages of classname $className
                    $pages = DataObject::get($className, "`SiteTree`.Status = 'Published'");
                    if ($pages)
                    {
                        DeviceAwareCache::$verbose = TRUE;
                        foreach ($pages as $page)
                        {
                            DeviceAwareCache::generateCache($className, $page->ID);
                        }
                        DeviceAwareCache::$verbose = FALSE;
                    }else{
                        echo 'Nothing published for '.$className.' yet.';
                    }
                }else{
                    //process one page only specified by $className + $classID
                    DeviceAwareCache::$verbose = TRUE;
                    DeviceAwareCache::generateCache($className, $classID);
                    DeviceAwareCache::$verbose = FALSE;
                }
            }   
        }
    }
}

?>
