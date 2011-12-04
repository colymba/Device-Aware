<?php
/**
 * DeviceAwareImage: Extension for Image Model
 *  *   Generates device optimized images
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
class DeviceAwareImage extends DataObjectDecorator
{
    /*
    function extraStatics()
    {
        return array(
            'db' => array(
                'usedResolutionWidthRatios' => 'Text',
                'usedResolutionHeightRatios' => 'Text',
            )
        );
    }
    */
    /*
    public function onAfterWrite ()
    {
        parent::onAfterWrite();
    }
    */
    
    
    public function deviceOptimizedImageWidthByRatio($ratio = 1)
    {        
        return $this->deviceOptimizedImage( NULL, NULL, $ratio, NULL );
    }
    
    public function deviceOptimizedImageHeightByRatio($ratio = 1)
    {
        return $this->deviceOptimizedImage( 'height', NULL, $ratio, NULL );
    }
    
    public function deviceOptimizedImage ( $direction = 'width', $calculation = 'average', $ratio = 1, $quality = 'auto' )
    {     
        $targetResolution = DeviceAware::getCurrentScreenResolution();
        $mobileDevices = Session::get('mobileDevices');                
        if ( !$mobileDevices )
        {
            DeviceAware::initMobileDeviceAware();                
            $mobileDevices = Session::get('mobileDevices');
        }

        if ( $quality == 'auto' )
        {
            if ( !$mobileDevices )
            {
                GD::set_default_quality(80);                
            }else{
                if ( $mobileDevices['isMobile'] ) GD::set_default_quality(65);
                else GD::set_default_quality(80);
            }
        }else{
            GD::set_default_quality($quality);
        }
        
        if ( !$targetResolution )
        {
            if ( $mobileDevices['isMobile'] )
            {
                $targetResolution = DeviceAware::getMobileDeviceResolution( $mobileDevices['current'], $calculation );
            }else{
                $targetResolution = DeviceAware::$defaultScreenResolution;
            }
        }
                
        if ( $targetResolution )
        {
            if ( $ratio != 1 )
            {
                $targetResolution[0] = $targetResolution[0] * $ratio;
                $targetResolution[1] = $targetResolution[1] * $ratio;
            }
            
            switch ( strtolower($direction) )
            {
                case 'height':
                    $newImage = $this->getFormattedDeviceAwareImage('SetHeight', $targetResolution[1]);
                    break;

                default:
                    $newImage = $this->getFormattedDeviceAwareImage('SetWidth', $targetResolution[0]);
                    break;
            }

            return $newImage;

        }else{
            return FALSE;
        }
    }
    
    /**
	 * Return an image object representing the image in the given format.
	 * This image will be generated using generateFormattedDeviceAwareImage().
	 * The generated image is cached, to flush the cache append ?flush=1 to your URL.
	 * @param string $format The name of the format.
	 * @param string $arg1 An argument to pass to the generate function.
	 * @param string $arg2 A second argument to pass to the generate function.
	 * @return Image_Cached
	 */
	function getFormattedDeviceAwareImage($format, $arg1 = null, $arg2 = null) {
		if($this->owner->ID && $this->owner->Filename && Director::fileExists($this->owner->Filename)) {
            
            $returnOriginal = FALSE;
            
            if ( !$arg1 || $arg1 == 0 )
            {
              $defaultResolution = DeviceAware::getDefaultResolution();
              if ( !$defaultResolution )
              {
                $returnOriginal = TRUE;
              }else{
                if ($format == 'SetWidth') $arg1 = $defaultResolution[0];
                else $arg1 = $defaultResolution[1];
              }
            }
                                               
            if ( !DeviceAware::$overSampleImages )
            {
                $gd = new GD(Director::baseFolder()."/" . $this->owner->Filename);
                if ( $format == 'SetWidth' && $gd->getWidth() <= $arg1 ) $returnOriginal = TRUE;
                if ( $format == 'SetHeight' && $gd->getHeight() <= $arg1 ) $returnOriginal = TRUE;
            }            
            
            if ( $returnOriginal )
            {
                return $this->owner;
            }else{
                $cacheFile = $this->deviceAwareCacheFilename($format, $arg1, $arg2);

                if(!file_exists(Director::baseFolder()."/".$cacheFile) || isset($_GET['flush'])) {
                    $this->generateFormattedDeviceAwareImage($format, $arg1, $arg2);
                }

                $cached = new Image_Cached($cacheFile);
                // Pass through the title so the templates can use it
                $cached->Title = $this->owner->Title;
                return $cached;
            }
		}
	}
    
    /**
     * Taking in account Device  TODO: and Quality
	 * Return the filename for the cached image, given it's format name and arguments.
	 * @param string $format The format name.
	 * @param string $arg1 The first argument passed to the generate function.
	 * @param string $arg2 The second argument passed to the generate function.
	 * @return string
	 */
    
	function deviceAwareCacheFilename($format, $arg1 = null, $arg2 = null) {		        
        $folder = $this->owner->ParentID ? $this->owner->Parent()->Filename : ASSETS_DIR . "/";
		
		$format = $format.$arg1.$arg2;        
        $mobileDevices = Session::get('mobileDevices');
        if ( !$mobileDevices )
        {
            DeviceAware::initMobileDeviceAware();
            $mobileDevices = Session::get('mobileDevices');
        }
        if ( $mobileDevices['isMobile'] ) $format .= '-mobile';
        //if ( $mobileDevices['advanced'] ) $format .= '-advanced';
		        
		return $folder . "_resampled/$format-" . $this->owner->Name;
	}
    
    /**
	 * Generate an image on the specified format. It will save the image
	 * at the location specified by deviceAwareCacheFilename(). The image will be generated
	 * using the specific 'generate' method for the specified format.
	 * @param string $format Name of the format to generate.
	 * @param string $arg1 Argument to pass to the generate method.
	 * @param string $arg2 A second argument to pass to the generate method.
	 */
	function generateFormattedDeviceAwareImage($format, $arg1 = null, $arg2 = null) {
		$cacheFile = $this->deviceAwareCacheFilename($format, $arg1, $arg2);
	
		$gd = new GD(Director::baseFolder()."/" . $this->owner->Filename);
		
		
		if($gd->hasGD()){
			$generateFunc = "generate$format";		
			if($this->owner->hasMethod($generateFunc)){
				$gd = $this->owner->$generateFunc($gd, $arg1, $arg2);
				if($gd){
					$gd->writeTo(Director::baseFolder()."/" . $cacheFile);
				}
	
			} else {
				USER_ERROR("DeviceAwareImage::generateFormattedDeviceAwareImage - Image $format function not found.",E_USER_WARNING);
			}
		}
	}
    
}
?>
