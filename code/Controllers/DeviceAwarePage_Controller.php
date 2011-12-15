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
    
    /* ************************************************************************
     * TEMPLATES HELPER FUNCTIONS
     */
    
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
    
    public function isClassicDevice()
    {          
        return !$this->isMobile();         
    }
    
    public function screenWidth()
    {
        $screenResolution = Session::get('screenResolution');
        if ( !$screenResolution )
        {
            $screenResolution = DeviceAware::$defaultScreenResolution;
        }
        
        return $screenResolution[0];
    }
    
    public function screenWidth1920()
    {
        if ( $this->screenWidth() == 1920 ) return TRUE;
        else return FALSE;
    }
    
    public function screenWidth1680()
    {
        if ( $this->screenWidth() == 1680 ) return TRUE;
        else return FALSE;
    }
    
    public function screenWidth1600()
    {
        if ( $this->screenWidth() == 1600 ) return TRUE;
        else return FALSE;
    }
    
    public function screenWidth1440()
    {
        if ( $this->screenWidth() == 1440 ) return TRUE;
        else return FALSE;
    }
    
    public function screenWidth1280()
    {
        if ( $this->screenWidth() == 1280 ) return TRUE;
        else return FALSE;
    }
    
    public function screenWidth1024()
    {
        if ( $this->screenWidth() == 1024 ) return TRUE;
        else return FALSE;
    }
       
}
?>
