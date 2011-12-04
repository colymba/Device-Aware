<?php
/**
 * DeviceAwarePage_Controller: Extension for Page Controller
 *  *   Inits DeviceAware on init
 *  *   Templates functions 
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
class DeviceAwarePage_Controller extends Extension
{     
    public static $allowed_actions = array (
        'saveScreenResolution'
	);
    
    public function onBeforeInit()
    {/*
        $mobileDevices = Session::get('mobileDevices');
        if ( !$mobileDevices )
        {*/
            DeviceAware::initMobileDeviceAware();
            $mobileDevices = Session::get('mobileDevices');
        /*}*/
        
        if ( isset($_GET['isDev']) ) DeviceAware::initMobileDeviceAware();        
    }
    
    public function isMobile()
    {       
        
        $mobileDevices = Session::get('mobileDevices');
        if ( !$mobileDevices )
        {
            DeviceAware::initMobileDeviceAware();
            $mobileDevices = Session::get('mobileDevices');
        }
        return $mobileDevices['isMobile'];         
    }
    
    public function saveScreenResolution( $data )
    {                       
        $resolution = FALSE;
        
        if ( isset($data['width']) && isset($data['height']) )
            $resolution = array(intval($data['width']), intval($data['height']));
                
        if ( $resolution )
        {           
            Session::set('screenResolution', $resolution);
            echo 'saved';
        }else{
            echo 'fail';
        }
    }    
}
?>
