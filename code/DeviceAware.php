<?php
/**
 * DeviceAware: Main class 
 *  -   Containins resolutions information and init methodes
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
 * @requires MobileBrowserDetector by Silverstripe
 */
class DeviceAware
{
    public static $mobileDevicesResolutions = array(
        'iPhone' => array(640, 960),
        'android' => array(
            'short' => array(240, 720),
            'long' => array(320, 1280)
        ),
        'opera' => array(
            'short' => array(240, 480),
            'long' => array(320, 800)
        ),
        'windows' => array(480, 800),
        'blackBerry' => array(
            'short' => array(240, 480),
            'long' => array(320, 800)
        ),
        'palm' => array(240, 320)
    );    
    public static $defaultMobileResolution = array(480, 800);
    
    
    public static $screenResolutions = array(
        array(800, 600),
        array(1024, 768),
        array(1280, 1024),
        array(1600, 1200),
        array(1920, 1080)
    );    
    public static $defaultScreenResolution = array(1600, 1200);
    
    public static $classicDeviesJPGImageQuality = 85;
    public static $mobileDeviesJPGImageQuality = 65;
    
    public static $usedResolutionRatios = array(); //set through config and defines what to pre-generate
    public static $cachedMobileDevices = array(); //set through config and defines which mobile device to pre-generate images for
    public static $overSampleImages = FALSE; //decides if small image will be scaled up for high resolutions    
    
    /**
	 * Store mobile detection results in the Session variable for later use
	 * 
	 * @return void
	 */
    public function initMobileDeviceAware()
    {        
        $mobileDevices = array();
        
        $mobileDevices['android'] = MobileBrowserDetector::is_android();
        $mobileDevices['iPhone'] = MobileBrowserDetector::is_iphone();
        $mobileDevices['opera'] = MobileBrowserDetector::is_opera_mini();
        $mobileDevices['blackBerry'] = MobileBrowserDetector::is_blackberry();
        $mobileDevices['palm'] = MobileBrowserDetector::is_palm();
        $mobileDevices['windows'] = MobileBrowserDetector::is_windows();
        
        $mobileDevices['current'] = FALSE;
        foreach ( $mobileDevices as $model => $active )
        {
            if ( $active )
            {
                $mobileDevices['current'] = $model;
                break;
            }
        }
        
        $mobileDevices['isMobile'] = MobileBrowserDetector::is_mobile();
        
        $mobileDevices['advanced'] = FALSE;
        if ( $mobileDevices['android'] || $mobileDevices['iPhone'] || $mobileDevices['opera'] || $mobileDevices['windows'] )
        {
            $mobileDevices['advanced'] = TRUE;
        }
        
        Session::set('mobileDevices', $mobileDevices);
    }
    
    /**
     * Return the prviously stored mobile detection data from the Session
     * and will init the Session data if not already set
     * 
     * @return array mobile detection data
     */
    public function getSessionMobileDevicesData()
    {        
        $mobileDevices = Session::get('mobileDevices');
        if ( !$mobileDevices )
        {
            self::initMobileDeviceAware();
            $mobileDevices = Session::get('mobileDevices');
        }        
        return $mobileDevices;
    }
    
    /**
	 * Returns a specific mobile device or the current mobile device resolution
	 *
	 * @param string $device the deivce name to return a resolution for
     * @param string $calculation (average, min, max) methode of calculation used if the device has a min/max resolution
	 * @return array resolution of $device or default mobile resolution if fail
	 */
    public function getMobileDeviceResolution ( $device = '', $calculation = 'average' )
    {        
        if ( !$device )
        {
            $mobileDevices = DeviceAware::getSessionMobileDevicesData();
            if ( !$mobileDevices['isMobile'] ) return FALSE;
            
            $device = $mobileDevices['current'];
        }        
        
        if ( $device && $device != 'isMobile' && $device != 'advanced' )
        {
            $resolution = self::$mobileDevicesResolutions[$device];
            if ( !$resolution ) $resolution = self::$defaultMobileResolution;
                        
            if ( isset($resolution['short']) )
            {
                switch ( strtolower($calculation) )
                {                    
                    case 'min':
                            $resolution = array(
                                $resolution['short'][0],
                                $resolution['long'][0],
                            );
                        break;

                    case 'max': 
                            $resolution = array(
                                $resolution['short'][1],
                                $resolution['long'][1],
                            );
                        break;
                        
                    case 'average':
                    default:
                            $resolution = array(
                                ($resolution['short'][0] + $resolution['short'][1]) / 2,
                                ($resolution['long'][0] + $resolution['long'][1]) / 2,
                            );
                        break;
                }
            }
                        
            return $resolution;
            
        }else{
            return self::$defaultMobileResolution;
        }
    }
    
    /**
     * Return current device's resolution
     * or fallback to default on fail
     * 
     * @return array resolution array(width, height)
     */
    public function getCurrentScreenResolution ()
    {
        $resolution = FALSE;    
        
        $userScreenResolution = Session::get('screenResolution');
        
        if ( $userScreenResolution )
        {
            $resolution = $userScreenResolution;
        }else{
            $mobileDevices = DeviceAware::getSessionMobileDevicesData();
            if ( $mobileDevices['isMobile'] )
            {
                $resolution = self::getMobileDeviceResolution( $mobileDevices['current'] );
            }
        }
            
        if (!$resolution) return self::getDefaultResolution();
        else return $resolution;        
    }   
    
    /**
     * Return current device's default resolution
     * Either mobile or classic screen
     * 
     * @return array Default resolution array(width, height)
     */
    public function getDefaultResolution()
    {
        $mobileDevices = DeviceAware::getSessionMobileDevicesData();
        
        if ( $mobileDevices['isMobile'] ) return self::$defaultMobileResolution;
        else return self::$defaultScreenResolution;
    } 
    
    /**
     * Sets GD Jpeg image quality to a specific value 
     * or to different defaults for mobile and classic devices
     * 
     * @param string|int $quality GD Jpeg image quality
     * @return void
     */
    public function setImageJpegQuality($quality = 'auto')
    {        
        if ( $quality == 'auto' )
        {
            $mobileDevices = self::getSessionMobileDevicesData();
            
            if ( !$mobileDevices )
            {
                GD::set_default_quality(self::$classicDeviesJPGImageQuality);                
            }else{
                if ( $mobileDevices['isMobile'] ) GD::set_default_quality(self::$mobileDeviesJPGImageQuality);
                else GD::set_default_quality(self::$classicDeviesJPGImageQuality);
            }
        }else{
            GD::set_default_quality($quality);
        }
        
        Session::set('deviceAwareGDJpegQualitySet', TRUE);
    }
    
    
    /**
     * Generates resized images in relation to the configured cache setting
     * Cache settings set through _config.php via DeviceAware::$usedResolutionRatios
     * 
     * @param string $targetPageClassName Object's class name to process the images from
     * @param string|int $targetPageID Object's ID from which the images will be processed
     * @param boolean $verbose If TRUE will ouput debug/stat infos
     * @return void
     */
    public function generateDeviceSpecificCachedImages($targetPageClassName, $targetPageID, $verbose = FALSE)
    {    
        increase_time_limit_to(); //will probably take for ever depending on the amount of pictures
        /*
        $targetPageClassName = $this->owner->ClassName;
        $targetPageID = $this->owner->ID;
        */
        
        if ($verbose) echo 'Working on: '.$targetPageClassName.' #'.$targetPageID.'<br/><br/>';
        
        $imagesToProcess = array();        
        $targetPageObject = DataObject::get_by_id($targetPageClassName, $targetPageID);
                
        //do nothing if can't find the page we are suppose to work on
        if ( $targetPageObject )
        {
            if ($verbose) echo 'Building image list...'.'<br/>';
            
            //generate image table to process
            if ( isset(self::$usedResolutionRatios[$targetPageClassName]) )
            {

                //fields
                if ( isset(self::$usedResolutionRatios[$targetPageClassName]['fields']) )
                {
                    foreach ( self::$usedResolutionRatios[$targetPageClassName]['fields'] as $imageField => $deviceTypes )
                    {      
                        if ($verbose) echo 'Added: '.$imageField.' (Field)'.'<br/>';
                        
                        if ( $targetPageObject->$imageField )
                        {                            
                            array_push($imagesToProcess, array(
                                'imageID' => $targetPageObject->$imageField,
                                'devices' => $deviceTypes
                            ));
                        }
                    }
                }

                //linked objects
                if ( isset(self::$usedResolutionRatios[$targetPageClassName]['objects']) )
                {
                    foreach ( self::$usedResolutionRatios[$targetPageClassName]['objects'] as $objectClass => $objectInfos )
                    {
                        $objectList = DataObject::get($objectClass, $objectInfos['foreignKey'] . '=' . $targetPageID );
                        if ( $objectList )
                        {
                            if ( isset(self::$usedResolutionRatios[$targetPageClassName]['objects'][$objectClass]['fields']) )
                            {
                                foreach ( self::$usedResolutionRatios[$targetPageClassName]['objects'][$objectClass]['fields'] as $imageField => $deviceTypes )
                                {
                                    if ($verbose) echo 'Added: '.$objectClass.' (Object): '.$imageField.' (Field)'.'<br/>';
                                    
                                    foreach ( $objectList as $object )
                                    {
                                        if ( $object->$imageField )
                                        {
                                            array_push($imagesToProcess, array(
                                                'imageID' => $object->$imageField,
                                                'devices' => $deviceTypes
                                            ));
                                        }
                                    }                                
                                }
                            }
                        }
                    }
                }

            }
            if ($verbose) echo 'Completed image list'.'<br/><br/>';
            if ($verbose) flush();
            if ($verbose) echo 'Generating images...'.'<br/>';
            
            //process image table
            foreach ( $imagesToProcess as $data )
            {
                $imageObj = DataObject::get_by_id('Image', $data['imageID']);
                if ( $imageObj )
                {
                    if ($verbose) echo '<br/>'.'Processing: '.$imageObj->Filename.'<br/>';
                    
                    foreach( $data['devices'] as $device => $ratioSet )
                    {                        
                        if ( $device == 'classic' )
                        {
                            //$mobileDevices['isMobile'] = FALSE;
                            //Session::set('mobileDevices', $mobileDevices);
                            GD::set_default_quality(self::$classicDeviesJPGImageQuality);
                            
                            if ($verbose) echo '<br/>'.'Classic devices: '.'<br/>';
                            
                            foreach( $ratioSet as $orientation => $ratios )
                            {
                                foreach ( self::$screenResolutions as $resolution )
                                {
                                    foreach ( $ratios as $ratio )
                                    {
                                        increase_time_limit_to(30);//try to avoid time out by resetting limit on each image processing
                                        if ( $orientation == 'width' )
                                        {
                                            $resolutionRatio = $resolution[0] * $ratio;
                                            $imageObj->getFormattedDeviceAwareImage('SetWidth', $resolutionRatio);
                                            if ($verbose) echo 'SetWidth: '.$resolution[0].' @ '.$ratio.' = '.$resolutionRatio.'<br/>';
                                        }else if ( $orientation == 'height' ){
                                            $resolutionRatio = $resolution[1] * $ratio;
                                            $imageObj->getFormattedDeviceAwareImage('SetHeight', $resolutionRatio);
                                            if ($verbose) echo 'SetHeight: '.$resolution[1].' @ '.$ratio.' = '.$resolutionRatio.'<br/>';
                                        }
                                    }
                                }
                            }
                        }// classic devices ratios
                        if ($verbose) flush();
                        
                        if ( $device == 'mobile' )
                        {
                            //$mobileDevices['isMobile'] = TRUE;
                            //Session::set('mobileDevices', $mobileDevices);
                            GD::set_default_quality(self::$mobileDeviesJPGImageQuality);
                            
                            if ($verbose) echo '<br/>'.'Mobile devices: '.'<br/>';
                            
                            foreach( $ratioSet as $orientation => $ratios )
                            {
                                foreach ( self::$mobileDevicesResolutions as $device => $resolution )
                                {
                                    //check if device is to be cached
                                    if ( in_array($device, self::$cachedMobileDevices) )
                                    {

                                        $resolutions = array();
                                        if ( isset($resolution['short']) )
                                        {
                                            //has min/max resolutions                            
                                            $index = 0;
                                            foreach ( $resolution['short'] as $short )
                                            {
                                                array_push($resolutions, array($short, $resolution['long'][$index])); //portrait
                                                array_push($resolutions, array($resolution['long'][$index], $short)); //landscape
                                                $index++;
                                            }

                                            //average
                                            array_push($resolutions, array(
                                                ($resolution['short'][0] + $resolution['short'][1]) / 2,
                                                ($resolution['long'][0] + $resolution['long'][1]) / 2
                                            )); //portrait
                                            array_push($resolutions, array(
                                                ($resolution['long'][0] + $resolution['long'][1]) / 2,
                                                ($resolution['short'][0] + $resolution['short'][1]) / 2
                                            )); //landscape
                                        }else{
                                            //1 resolution
                                            array_push($resolutions, array($resolution[0], $resolution[1])); //portrait
                                            array_push($resolutions, array($resolution[0], $resolution[1])); //landscape
                                        }

                                        foreach ( $resolutions as $res )
                                        {
                                            foreach ( $ratios as $ratio )
                                            {
                                                increase_time_limit_to(30);//try to avoid time out by resetting limit on each image processing
                                                if ( $orientation == 'width' )
                                                {
                                                    $resolutionRatio = $res[0] * $ratio;
                                                    $imageObj->getFormattedDeviceAwareImage('SetWidth', $resolutionRatio);
                                                    if ($verbose) echo 'SetWidth: '.$device.': '.$res[0].' @ '.$ratio.' = '.$resolutionRatio.'<br/>';
                                                }else if ( $orientation == 'height' ){
                                                    $resolutionRatio = $res[1] * $ratio;
                                                    $imageObj->getFormattedDeviceAwareImage('SetHeight', $resolutionRatio);
                                                    if ($verbose) echo 'SetHeight: '.$device.': '.$res[1].' @ '.$ratio.' = '.$resolutionRatio.'<br/>';
                                                }
                                            }
                                        }

                                    }//if device to be cahched
                                }//loop mobile resolutions
                            }
                        }// mobile ratios
                        
                    }// loop through devices
                    
                }// if image object
                if ($verbose) flush();
            }// loop through images to cache
            
            if ($verbose) echo '<br/><br/>'.'Re-init Mobile Device Aware'.'<br/><br/>';
            //re-init becuase we manual modified it earlier
            self::initMobileDeviceAware(); 
            
        }//if page Object
        if ($verbose) echo '<br/><br/>'.'Done'.'<br/><br/>';
    }
}
?>
