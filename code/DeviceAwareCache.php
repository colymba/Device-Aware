<?php
/**
 * DeviceAwareCache: Handles image cache generation
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
 * @requires MobileBrowserDetector by Silverstripe
 */

class DeviceAwareCache
{
        
    public static $verbose = FALSE;
        
    public static $imageCacheSettings = array(); // set through _config.php
    
    /**
     * Generates resized images in relation to the configured cache setting
     * Cache settings set through _config.php via DeviceAwareCache::$imageCacheSettings
     * 
     * @param string $targetPageClassName Object's class name to process the images from
     * @param string|int $targetPageID Object's ID from which the images will be processed
     * @return void
     */
    public function generateCache($targetPageClassName, $targetPageID)
    {
        if (self::$verbose) echo 'Working on: '.$targetPageClassName.' #'.$targetPageID.'<br/><br/>';
                       
        $targetPageObject = DataObject::get_by_id($targetPageClassName, $targetPageID);
        
        if (!$targetPageObject)
        {
            if (self::$verbose) echo 'No Page Object found, stopping now.';
            return FALSE;
        }
        
        $cacheList = self::buildCacheImageList($targetPageObject);
        
        if (self::$verbose) echo 'Resizing images...'.'<br/>';
        
        foreach ( $cacheList as $imgObjSettings )
        {
            $imageObj = DataObject::get_by_id('Image', $imgObjSettings['imageID']);
            if (!$imageObj)
            {
                if (self::$verbose) echo 'Skipped Image #'.$imgObjSettings['imageID'].': no object found'.'<br/>';
                continue;
            }
            
            if (self::$verbose) echo '<br/>'.'Processing: '.$imageObj->Filename.'<br/>';
            
            $imageObjOrientation = '';
            if ( $imageObj->getOrientation() == Image::ORIENTATION_SQUARE ) $imageObjOrientation = 'S';
            if ( $imageObj->getOrientation() == Image::ORIENTATION_PORTRAIT ) $imageObjOrientation = 'P';
            if ( $imageObj->getOrientation() == Image::ORIENTATION_LANDSCAPE ) $imageObjOrientation = 'L';
                        
            foreach ($imgObjSettings['settings'] as $resolutionData)
            {
                if ( $resolutionData[0] == $imageObjOrientation || $resolutionData[0] == 'A' )
                {
                    DeviceAware::setImageJpegQuality();
                    increase_time_limit_to(30);//try to avoid time out by resetting limit on each image processing
                    
                    if ( is_string($resolutionData[1]) ) $targetSize = floatval($resolutionData[1]);
                    else $targetSize = $resolutionData[1] * $resolutionData[2];     
                    
                    if ( $resolutionData[3] == 'W' ) $imageObj->getFormattedDeviceAwareImage('SetWidth', $targetSize);
                    else $imageObj->getFormattedDeviceAwareImage('SetHeight', $targetSize);
                    
                    if (self::$verbose) echo 'Resized '. $resolutionData[3] .': '.$resolutionData[1] . ' x ' . $resolutionData[2] . ' = ' . $targetSize .'<br/>';
                }
            }
            
            if (self::$verbose) flush();
        } 

        if (self::$verbose) echo '<br/><br/>'.'Done :)'.'<br/><br/>';             
    }
    
    /**
     * Generates the list of image objects to process for a given Page Object
     * 
     * @param DataObject $targetPageObject Page object we are working on
     * @return array the list of images to cache array(imageObjectID => resizingSettings)
     */
    public function buildCacheImageList($targetPageObject)
    {
        $cacheImageList = array(); 
        
        if (self::$verbose) echo 'Building image list...'.'<br/>';
            
        //generate image table to process
        if ( isset(self::$imageCacheSettings[$targetPageObject->ClassName]) )
        {

            //fields
            if ( isset(self::$imageCacheSettings[$targetPageObject->ClassName]['fields']) )
            {
                foreach ( self::$imageCacheSettings[$targetPageObject->ClassName]['fields'] as $imageField => $settings )
                {      
                    if (self::$verbose) echo 'Added: '.$imageField.' (Field)'.'<br/>';
                    
                    if ( $targetPageObject->$imageField )
                    {                            
                        array_push($cacheImageList, array(
                            'imageID' => $targetPageObject->$imageField,
                            'settings' => $settings
                        ));
                    }
                }
            }

            //linked objects
            if ( isset(self::$imageCacheSettings[$targetPageObject->ClassName]['objects']) )
            {
                foreach ( self::$imageCacheSettings[$targetPageObject->ClassName]['objects'] as $objectClass => $objectInfos )
                {
                    $objectList = DataObject::get($objectClass, $targetPageObject->ClassName.'ID' . '=' . $targetPageObject->ID );
                    // TODO: if no list, try JOIN table for has_many_many relations
                    if ( $objectList )
                    {   
                        if ( isset(self::$imageCacheSettings[$targetPageObject->ClassName]['objects'][$objectClass]['fields']) ) // might be better to test this before the get query
                        {
                            foreach ( self::$imageCacheSettings[$targetPageObject->ClassName]['objects'][$objectClass]['fields'] as $imageField => $settings )
                            {
                                if (self::$verbose) echo 'Added: '.$objectClass.' (Object x '.$objectList->Count().'): '.$imageField.' (Field)'.'<br/>';
                                
                                foreach ( $objectList as $object )
                                {
                                    if ( $object->$imageField )
                                    {
                                        array_push($cacheImageList, array(
                                            'imageID' => $object->$imageField,
                                            'settings' => $settings
                                        ));
                                    }
                                }                                
                            }
                        }
                    }
                }
            }

        }
        if (self::$verbose) echo 'Completed image list'.'<br/><br/>';
        if (self::$verbose) flush();
        
        return $cacheImageList;
    }
    
}
?>
