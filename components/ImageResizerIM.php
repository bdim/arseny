<?php
namespace app\components;
/**
 * 
 * @author tovSuhov
 * @since  22.09.2016
 */
class ImageResizerIM extends ImageResizer
{
	/** @var Imagick $_im */
	private $_im = null;

	private $crop_width = 0;
	private $crop_height = 0;


	public function __construct($file)
	{
		try {

			$this->_im = new Imagick($file);
		}
		catch (Exception $e)
		{
			return false;
		}


		if(!in_array($this->_im->getImageMimeType(), $this->mime_types))
		{
			unset($this->_im);
			return false;
		}

		$this->file = $file;

		$this->type =$this->_im->getImageMimeType();

		$this->orig_width = $this->_im->getImageWidth();
		$this->orig_height = $this->_im->getImageHeight();
	}


	public function getFormat()
	{
		if (!$this->_im) return '';

		return strtolower($this->_im->getImageFormat());
	}

  	public function getOgirW() {
    	return $this->orig_width;
  	}

  	public function getOgirH() {
    	return $this->orig_height;
  	}
  
	public function resize($file, $w, $h = false) {

		$this->setResize($w, $h);

		if(!$this->process($file))
			return false;

		return true;
	}

	public function resizeH($file, $h) {

		$this->setResizeH($h);

		if(!$this->process($file))
			return false;

		return true;
	}

	public function resizeW($file, $w) {

		$this->setResizeW($w);

		if(!$this->process($file))
			return false;

		return true;
	}

	public function resizeCrop($file, $w, $h) {

		$this->setResizeCrop($w, $h);

		if(!$this->processCrop($file))
			return false;

		return true;
	}

	private function setResize($w, $h = false) {

		$h = $h === false ? $w : $h;

		if($this->orig_width > $this->orig_height)
		{

			$this->dst_width = $this->orig_width < $w ? $this->orig_width : $w;
			$this->dst_height = $this->orig_width ? ($this->orig_height / $this->orig_width) * $this->dst_width : 0;

		}
		else
		{

			$this->dst_height = $this->orig_height < $h ? $this->orig_height : $h;
			$this->dst_width = $this->orig_height ? ($this->orig_width / $this->orig_height) * $this->dst_height : 0;
		}
	}

	private function setResizeW($w)
	{
		$this->dst_width = $w;
		$this->dst_height = 0;
	}

	private function setResizeH($h) {

		$this->dst_height = $h;
		$this->dst_width = 0;
	}

	private function setResizeCrop($w, $h) {

		$this->crop_width = $w;
		$this->crop_height = $h;

		if ($this->orig_width > $this->orig_height) {
			$this->dst_width = $this->orig_width * $h / $this->orig_height;
			$this->dst_height = $h;
		}
		else {
			$this->dst_width =  $w;
			$this->dst_height = $this->orig_height * $w / $this->orig_width;
		}

		$this->dst_x = ($this->dst_width - $this->crop_width) / 2;
		$this->dst_y = ($this->dst_height - $this->crop_height) / 2;
	}

	private function process($path) {

		if($this->orig_width > 18000 || $this->orig_height > 18000)
			return false;

		if (!$this->_im) return false;

		if ($this->_im->getImageFormat() == 'GIF') return $this->processGIF($path);

		$this->_im->setImageCompressionQuality($this->quality);

		$bestfit = $this->dst_width  && $this->dst_height;

		$this->_im->resizeImage ( $this->dst_width, $this->dst_height,  Imagick::FILTER_BESSEL, 0.9, $bestfit);

		if(file_exists($path))
			@unlink($path);

		$this->_im->writeImage($path);

		return true;
	}

	private function processGIF($path)
	{
		$this->_im->setImageCompressionQuality($this->quality);

		if(file_exists($path))
			@unlink($path);

        $squareMode = false;

        //если размеры фреймов вдруг отличаются, вписываем все в квадрат
        foreach ($this->_im as $idx => $frame) {
            if ($frame->getImageHeight() != $this->orig_height ||
                $frame->getImageWidth() != $this->orig_width)
                $squareMode = true;
        }

        if ($squareMode && $this->dst_width)
            $this->dst_height = $this->dst_width;
        else
            if ($squareMode && $this->dst_height)
                $this->dst_width = $this->dst_height;

        /* Изменение размера всех фреймов */
		foreach ($this->_im as $idx => $frame) {

            if ($squareMode)
                $frame->thumbnailImage($this->dst_width, $this->dst_height, true, true);
            else
			    $frame->thumbnailImage($this->dst_width, $this->dst_height, false);

			/* Устанавливаем виртуальный холст для коррекции размера */
			$frame->setImagePage($this->dst_width, $this->dst_height, 0, 0);
		}

		/* Обратите внимание, writeImages вместо writeImage */
		$this->_im->writeImages($path, true);
		return true;
	}


	private function processCrop($path) {

		if($this->orig_width > 18000 || $this->orig_height > 18000)
			return false;

		if (!$this->_im) return false;

		$this->_im->setImageCompressionQuality($this->quality);

		$this->_im->resizeImage ( $this->dst_width, $this->dst_height,  Imagick::FILTER_BESSEL, 0.9, false);
		$this->_im->cropImage($this->crop_width, $this->crop_height, $this->dst_x, $this->dst_y);

		if(file_exists($path))
			@unlink($path);

		$this->_im->writeImage($path);

		return true;
	}

    public function rotateImage($angle)
    {
        $res = $this->_im->rotateImage(new ImagickPixel(), $angle);
        if ($res)
            $this->_im->writeImage($this->file);
        return $res;
    }
}
?>
