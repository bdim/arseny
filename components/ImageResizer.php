<?php
namespace app\components;
/**
 * 
 * @author volkov
 * @since  28.07.2009
 */
class ImageResizer
{

	public $quality = 85;

	protected $mime_types = array(
		'image/jpeg',
		'image/png',
		'image/gif'
	);

	protected $file;

	protected $type;

	protected $orig_width;
	protected $orig_height;


	protected $src_x;
	protected $src_y;
	protected $src_width;
	protected $src_height;

	protected $dst_x;
	protected $dst_y;
	protected $dst_width;
	protected $dst_height;

	public function __construct($file)
	{
/*        if (extension_loaded('imagick'))
            $this->_imageResizer = new ImageResizerIM($file);
        else*/
            $this->_imageResizer = new ImageResizerGD($file);
	}

    public function setQiality($quality)
    {
        $this->_imageResizer->quality = $quality;
    }

	public function getFormat()
	{
		return $this->_imageResizer->getFormat();
	}

  public function getOgirW() {
    return $this->_imageResizer->getOgirW();
  }

  public function getOgirH() {
    return $this->_imageResizer->getOgirH();
  }
  
	public function resize($file, $w, $h = false) {
		return $this->_imageResizer->resize($file, $w, $h);
	}

	public function resizeH($file, $h) {
        return $this->_imageResizer->resizeH($file, $h);
	}

	public function resizeW($file, $w) {
        return $this->_imageResizer->resizeW($file, $w);
	}

	public function resizeCrop($file, $w, $h) {
        return $this->_imageResizer->resizeCrop($file, $w, $h);
	}

    public function rotateImage($angle)
    {
        return $this->_imageResizer->rotateImage($angle);
    }
}
?>
