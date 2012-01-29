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
 * 
 * @requires MobileBrowserDetector by Silverstripe
 */

 /**
  * TODO: test cookie instead of session
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
    public static $defaultScreenResolution = array(1600, 1200);
    
    public static $JPGImageQuality = 85;       
    
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
            /*
            $mobileDevices = self::getSessionMobileDevicesData();
            
            if ( !$mobileDevices )
            {
                GD::set_default_quality(self::$classicDeviesJPGImageQuality);                
            }else{
                if ( $mobileDevices['isMobile'] ) GD::set_default_quality(self::$mobileDeviesJPGImageQuality);
                else GD::set_default_quality(self::$classicDeviesJPGImageQuality);
            }*/
            GD::set_default_quality(self::$JPGImageQuality);            
        }else{
            GD::set_default_quality($quality);
        }
        
        Session::set('deviceAwareGDJpegQualitySet', TRUE);
    }
}
?>
