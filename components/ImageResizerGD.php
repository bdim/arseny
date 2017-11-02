<?php
namespace app\components;

class ImageResizerGD extends ImageResizer
{

    private $tmp;
    private $src;

	public function __construct($file)
	{
		if(!$info = getimagesize($file))
			return false;

		if(!in_array($info['mime'], $this->mime_types))
			return false;

		$this->file = $file;

		$this->type = $info['mime'];

		$this->orig_width = $info[0];
		$this->orig_height = $info[1];
	}

	public function getFormat()
	{
		switch($this->type)
		{
			case 'image/jpeg':

				return 'jpg';
			break;

			case 'image/gif':

					return 'gif';
			break;

			case 'image/png':

				return 'png';
				break;
		}

		return 'jpg';
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

		if(!$this->process($file))
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

		$this->src_width = $this->orig_width;
		$this->src_height = $this->orig_height;

		$this->dst_x = 0;
		$this->dst_y = 0;

		$this->src_x = 0;
		$this->src_y = 0;
	}

	private function setResizeW($w)
	{
		$this->dst_width = $this->orig_width < $w ? $this->orig_width : $w;
		$this->dst_height = $this->orig_width ? ($this->orig_height / $this->orig_width) * $this->dst_width : 0;

		$this->src_width = $this->orig_width;
		$this->src_height = $this->orig_height;

		$this->dst_x = 0;
		$this->dst_y = 0;

		$this->src_x = 0;
		$this->src_y = 0;
	}

	private function setResizeH($h) {

		$this->dst_height = $this->orig_height < $h ? $this->orig_height : $h;
		$this->dst_width = $this->orig_height ? ($this->orig_width / $this->orig_height) * $this->dst_height : 0;

		$this->src_width = $this->orig_width;
		$this->src_height = $this->orig_height;

		$this->dst_x = 0;
		$this->dst_y = 0;

		$this->src_x = 0;
		$this->src_y = 0;
	}

	private function setResizeCrop($w, $h) {

		$this->dst_width = $w;
		$this->dst_height = $h;

		$this->dst_x = 0;
		$this->dst_y = 0;

		if($this->orig_height * $this->dst_width / $this->orig_width >= $this->dst_height) {

			$this->src_width = $this->orig_width;
			$this->src_height = $this->src_width * $this->dst_height / $this->dst_width;

			$this->src_x = 0;
			$this->src_y = ($this->orig_height - $this->src_height) / 2;
		}

		else {

			$this->src_height = $this->orig_height;
			$this->src_width = $this->src_height * $this->dst_width / $this->dst_height;

			$this->src_x = ($this->orig_width - $this->src_width) / 2;
			$this->src_y = 0;
		}
	}

	private function process($path) {

		if($this->orig_width > 18000 || $this->orig_height > 18000)
			return false;
		
		if(!$this->createImage())
			return false;

		if(!$this->tmp = @imagecreatetruecolor($this->dst_width, $this->dst_height))
			return false;

			
		$this->setAlpha();


		if(!@imagecopyresampled($this->tmp, $this->src,
								$this->dst_x, $this->dst_y,
								$this->src_x, $this->src_y,
								$this->dst_width, $this->dst_height,
								$this->src_width, $this->src_height))
			return false;

		if(file_exists($path))
			@unlink($path);

			
		$this->saveImage($path);

		
		//chmod($path, NVS_FILES_RIGHTS);

		@imagedestroy($this->src);
		@imagedestroy($this->tmp);

		return true;
		
	}

	private function createImage()
	{
		switch($this->type)
		{
			case 'image/jpeg':

				if(!$this->src = @imagecreatefromjpeg($this->file))
					return false;

				break;

			case 'image/gif':

				if(!$this->src = @imagecreatefromgif($this->file))
					return false;

				break;

			case 'image/png':

				if(!$this->src = @imagecreatefrompng($this->file))
					return false;

				break;
		}

		return true;
	}

	private function saveImage($path) {

		switch($this->type) {

			case 'image/jpeg':

				if(!@imagejpeg($this->tmp, $path, $this->quality))
					return false;

				break;

			case 'image/gif':

				if(!@imagegif($this->tmp, $path))
					return false;

				break;

			case 'image/png':

				if(!@imagepng($this->tmp, $path))
					return false;

				break;
		}

		return true;
	}

	private function setAlpha() {

		if($this->type == 'image/jpeg') return;
		if($this->type == 'image/gif') return;
		
		$index = imagecolortransparent($this->src);

		if(($index = imagecolortransparent($this->src)) >= 0) {

			$color = imagecolorsforindex($this->src, $index);

			$index = imagecolorallocate($this->tmp, $color['red'], $color['green'], $color['blue']);

			imagefill($this->tmp, 0, 0, $index);

			imagecolortransparent($this->tmp, $index);
		}

		elseif($this->type == 'image/png') {

			imagealphablending($this->tmp, false);

			$color = imagecolorallocatealpha($this->tmp, 0, 0, 0, 127);

			imagefill($this->tmp, 0, 0, $color);

			imagesavealpha($this->tmp, true);
		}
	}

    public function rotateImage($angle)
    {
        //под GD не реализуем - устарело
        return false;
    }

}
?>
