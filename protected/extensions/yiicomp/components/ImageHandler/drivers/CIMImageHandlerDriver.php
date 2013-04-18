<?php
/**
 * Image handler
 * @author Yaroslav Pelesh aka Tokolist http://tokolist.com
 * @author Kosenka https://github.com/kosenka
 * @link https://github.com/tokolist/yii-components
 * @version 2.0
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

require '..\CImageHandlerDriver.php';

class CIMImageHandlerDriver extends CImageHandlerDriver
{
	public $convertPath = '/usr/bin/convert';
	public $compositePath = '/usr/bin/composite';
	protected $commandString;
	protected $fileName = '';


	public function __construct($imageHandler) {
		parent::__construct();
		
		if(empty($this->convertPath))
		{
			throw new Exception('ImageMagick::convert not set');
		}
		
		if(empty($this->compositePath))
		{
			throw new Exception('ImageMagick::composite not set');
		}
	}
	
	private function colorToHex($color)
	{
		return str_pad(dechex($color), 2, '0', STR_PAD_LEFT);
	}
	
	private function rgbToHex($r, $g, $b)
	{
		$result = '';

		foreach(array($r, $g, $b) as $color)
		{
			$result .= colorToHex($color);
		}		
		
		return "#" . $result;
	}
	
	public function loadImage($file, $format)
	{
		if(!file_exists($file))
		{
			throw new Exception("File {$file} not found");
		}
		
		return $file;
	}
	
	public function initImage($image)
	{
		parent::initImage($image);
		
		$this->fileName = $image['image'];
	}
	
	public function freeImage()
	{
		$this->fileName = '';
	}
	
	public function checkLoaded()
	{
		return !empty($this->fileName);
	}
	
	public function resize($toWidth, $toHeight)
	{
		$this->commandString = $this->convertPath . " -quiet -strip -resize " . $newWidth . "x" . $newHeight . " " . $this->fileName . " %dest%";
	}
	
	public function watermark($wImg, $posX, $posY, $watermarkWidth, $watermarkHeight, $corner)
	{
		if($corner == self::CORNER_TILE)
		{
			$this->commandString = $this->compositePath." -quiet -dissolve 25 -tile ".$watermarkFile." ".$this->fileName." %dest%";
		}
		else
		{
			$this->commandString = $this->convertPath." -quiet ".$this->fileName." ".$watermarkFile." -geometry +".$posX."+".$posY." -composite %dest%";
		}
	}
	
	public function flip($mode)
	{
		switch($mode)
		{
			case self::FLIP_HORIZONTAL:
				$this->commandString = $this->convertPath." -flop ".$this->fileName." %dest%";
				break;
			case self::FLIP_VERTICAL:
				$this->commandString = $this->convertPath." -flip ".$this->fileName." %dest%";
				break;
			case self::FLIP_BOTH:
				$this->commandString = $this->convertPath." -flop -flip ".$this->fileName." %dest%";
				break;
			default:
				throw new Exception('Invalid $mode value');
		}
	}
	
	public function rotate($degrees)
	{
		$this->commandString = $this->convertPath." -rotate ".$degrees." ".$this->fileName." %dest%";
	}
	
	public function crop($width, $height, $startX, $startY)
	{
		$this->commandString = $this->convertPath." -quiet -strip -crop ".$width."x".$height."+".$startX."+".$startY." ".$this->fileName." %dest%";
	}
	
	public function text($text, $fontFile, $size, $color, $angle, $alpha)
	{
		$hex = $this->RGBToHex($color[0],$color[1],$color[2]);
		$this->commandString = $this->convertPath." -quiet -font ".$fontFile." -pointsize ".$size." -draw \"gravity south fill '".$hex."' text ".$posX.",".$posY." '".$text."' \" ".$this->fileName." %dest%";
	}
}