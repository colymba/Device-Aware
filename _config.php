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
 * @version 0.01
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
 * By default images are not over-sampled
 * Uncomment if you would like to
 * 
 * DeviceAware::$overSampleImages = TRUE;
 */

/**
 * For mobile: devices to pre-generate image for
 * best to use the most common ones for your visitors
 */
DeviceAware::$cachedMobileDevices = array('iPhone','android');

/**
 * Overhide screen resolution for pre-generated images
 * Check you site analytics for the most common ones
 * or checkout: http://en.wikipedia.org/wiki/Display_resolution
 */
DeviceAware::$screenResolutions = array(
    array(1024, 768),
    array(1280, 1024),
    array(1600, 1200),
    array(1440, 900),
    array(1920, 1080)
);

/*
 * Registers images to cache
 * TODO: handle image orientation conditions
 */
DeviceAware::$usedResolutionRatios = array(
    'Project' => array(
        'fields' => array(
            'CoverImageID' => array(
                'classic' => array(
                    'width' => array(0.486, 0.238, 0.155) //as used in template
                ),
                'mobile' => array(
                    'width' => array(0.9, 0.441, 0.288)
                )
            )
        ),
        'objects' => array(
            'ImagePiece' => array(
                'foreignKey' => 'ProjectPageID',
                'fields' => array(
                    'ImageFileID' => array(
                        'classic' => array(
                            'width' => array(0.9, 0.486), //as used in template
                            'height' => array(0.9, 0.37) //as used in template
                        ),
                        'mobile' => array(
                            'width' => array(0.9, 0.486), //as used in template
                            'height' => array(0.9, 0.37) //as used in template
                        )
                    )
                )
            )
        )
    )
);
?>