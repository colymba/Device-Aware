<?php
/**
 * Device Aware Config file
 *  *   Registers extensions
 *  *   Register images to chache
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

///////////////////////////////////////////////////////////////////////////////
/////////////////////// Extensions + rules

/**
 * Register extensions
 */
Object::add_extension('Page_Controller', 'DeviceAwarePage_Controller');
Object::add_extension('Page', 'DeviceAwarePage'); 
Object::add_extension('Image', 'DeviceAwareImage');

/**
 * Add director URL rule to store current device resolution from AJAX call
 */
Director::addRules(100, array( 
    'device_aware/$Action/$ID' => "Page_Controller" 
));

Director::addRules(100, array( 
    'device_aware/dev/$Action/$Class/$ID' => "DeviceAwareDev_Controller" 
));

///////////////////////////////////////////////////////////////////////////////
/////////////////////// Image cache generations

/**
 * Default JPEG quality is 85 for cached images
 * Uncomment to overide
 */
//DeviceAware::$JPGImageQuality = 100;


/**
 * By default images are not over-sampled
 * Uncomment if you would like to
 */ 
//DeviceAwareImage::$overSampleImages = TRUE;


/**
 * Registers images to cache
 * 
 * Check you site analytics for the most common resolutions
 * or checkout: http://en.wikipedia.org/wiki/Display_resolution
 */
DeviceAwareCache::$imageCacheSettings = array(
    'Project' => array(
        'fields' => array(
            'CoverID' => array(
                // imageOrientation, ratio/pixelSize, screenResolution, imageDirectionToResize
                
                // Classic devices
                array('P', 0.102, 1920, 'W'),
                array('S', 0.102, 1920, 'W'),
                array('L', 0.102, 1920, 'H'),
                
                array('P', 0.102, 1680, 'W'),
                array('S', 0.102, 1680, 'W'),
                array('L', 0.102, 1680, 'H'), 
                               
                array('P', 0.1498, 1440, 'W'),
                array('S', 0.1498, 1440, 'W'),
                array('L', 0.1498, 1440, 'H'),
                
                array('P', 0.1498, 1280, 'W'),
                array('S', 0.1498, 1280, 'W'),
                array('L', 0.1498, 1280, 'H'),
                
                array('P', 0.147, 1024, 'W'),
                array('S', 0.147, 1024, 'W'),
                array('L', 0.147, 1024, 'H'),
                
                // Mobile
                array('P', 0.266, 640, 'W'),
                array('S', 0.266, 640, 'W'),
                array('L', 0.266, 640, 'H'),
                
                array('P', 0.266, 960, 'W'),
                array('S', 0.266, 960, 'W'),
                array('L', 0.266, 960, 'H')             
            )
        ),
        'objects' => array(
            'Snapshot' => array(
                'fields' => array(
                    'ImageID' => array(
                        // imageOrientation, ratio/pixelSize, screenResolution, imageDirectionToResize
                        
                        // Classic devices
                        array('L', 0.6, 1920, 'W'),
                        array('L', 0.6, 1680, 'W'),
                        array('L', 0.7, 1440, 'W'),
                        array('L', 0.7, 1280, 'W'),
                        array('L', 0.7, 1024, 'W'),
                        
                        array('P', '700', 0, 'H'),
                        array('P', '550', 0, 'H'),
                        array('S', '700', 0, 'H'),
                        array('S', '550', 0, 'H'),
                        
                        // Mobile
                        array('P', '550', 0, 'H'),
                        array('S', '550', 0, 'H'),
                        array('L', 0.95, 640, 'W'),              
                        array('L', 0.95, 960, 'W'),
                    )
                )
            )
        )
    )
); 
?>