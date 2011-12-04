<?php
/**
 * DeviceAwarePage: Extension for Page Model
 *  *   Hook to pre-generate/cache optimized images for used resolutions
 *  *   See config file for usage
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
class DeviceAwarePage extends SiteTreeDecorator
{     
    public function onAfterPublish( &$original)
    {
        /**
         * Images are cached when the page is published. This will take some time
         * so better be sure it is the images we want published
         */
        DeviceAware::generateDeviceSpecificCachedImages($this->owner->ClassName, $this->owner->ID);
    } 
}
?>
