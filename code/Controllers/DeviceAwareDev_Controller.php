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
 * @version 0.01
 * 
 * @requires DeviceAware
 */
class DeviceAwareDev_Controller extends Controller
{
    //put your code here
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
    
    public function build()
    {
        echo 'Device Aware cache building...'.'<br/><br/>';
        
        //print_r(Director::URLParam('Class') . ': ' . Director::URLParam('ID'));
        $className = Director::URLParam('Class');
        $classID = Director::URLParam('ID');
        
        //if URLParams use this        
        //DeviceAware::generateDeviceSpecificCachedImages($className, $classID, TRUE);
        DeviceAwareCache::$verbose = TRUE;
        DeviceAwareCache::generateCache($className, $classID);
        DeviceAwareCache::$verbose = FALSE;
        
        //get config
        //loop though classes
        //get all objectsfor each classes
        //send obj 1 by 1 to:
        //DeviceAware::generateDeviceSpecificCachedImage($targetClass, $targetClassID);
    }
}

?>
