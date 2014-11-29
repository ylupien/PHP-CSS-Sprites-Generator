<?php

namespace yl\gfx;

require_once('SpriteGridImage.php');

class SpriteGrid
{
    private $_spriteGd;
    private $_nextId = 1;

    private $_placedPictures = array();
    private $_css = array();
    private $_grids = array();

    private $_width;
    private $_height;

    private $_tileWidth = 8;
    private $_tileHeight = 8;

    private $_acceptedFormat = array('png' => true);
    private $_maxSurface = 40000; //200 * 200;

    private $_gd = null;

    public function __construct()
    {
        $this->_width = 800 / $this->_tileWidth;
        $this->_height = 10;

        for ($y = 0; $y < 256; $y++) {
            $this->_grids[$y] = array_fill(0, $this->_width+1, 0);
        }
    }

    /**
     * @param \DirectoryIterator $it
     * @return void
     */
    public function importImagesFromFolder(\DirectoryIterator $it)
    {
        foreach ($it as $file)
        {
            if ($file->isFile() && array_key_exists($file->getExtension(), $this->_acceptedFormat))
            {
                $img = SpriteGridImage::create($file->getFileInfo());

                if ($img->notExceedSurface($this->_maxSurface))
                    $this->add($img);
            }

            unset($file);
        }
    }

    /**
     * @param SpriteGridImage $img
     * @return void
     */
    public function add(SpriteGridImage $img)
    {
        $img->id = $this->_nextId;
        $this->_nextId++;

        $img->width = ceil($img->pixelWidth / $this->_tileWidth);
        $img->height = ceil($img->pixelHeight / $this->_tileHeight);

        $placed = false;
        for ($y = 0; $y < $this->_height && $placed === false; $y++)
        {
            for ($x = 0; $x < $this->_width && $placed === false; $x++)
            {
                $img->x = $x;
                $img->y = $y;

                $placed = $this->canFit($img);

                if ($placed)
                {
                    $this->_placedPictures[$img->id] = $img;
                    $this->assignToGrd($img);
                }
            }
        }

    }

    /**
     * @param SpriteGridImage $img
     * @return bool
     */
    private function canFit(SpriteGridImage $img)
    {
        $fit = true;

        $y1 = $img->y + $img->height;
        $x1 = $img->x + $img->width;

        if ($x1 >= $this->_width)
            $fit = false;

        for ($y = $img->y; $y < $y1 && $fit === true; $y++) {
            for ($x = $img->x; $x < $x1 && $x1 <= $this->_width && $fit === true; $x++) {
                $fit = $this->_grids[$y][$x] === 0;
            }
        }

        return $fit;
    }

    /**
     * @param SpriteGridImage $img
     * @return void
     */
    private function assignToGrd(SpriteGridImage $img)
    {
        $y1 = $img->y + $img->height;
        $x1 = $img->x + $img->width;

        if ($y1 > $this->_height)
            $this->_height = $y1;

        for ($y = $img->y; $y <= $y1; $y++) {
            for ($x = $img->x; $x <= $x1; $x++) {
                $this->_grids[$y][$x] = $img->file->getFilename();
            }
        }
    }

    /**
     * Return an True Color Image with Alpha channel.
     *
     * @return resource Gd Image
     */
    public function asGdImage()
    {
        /* @var $img SpriteGridImage */

        if ($this->_gd)
            return $this->_gd;

        $imgWidth = $this->_width * $this->_tileWidth;
        $imgHeight = $this->_height * $this->_tileHeight;

        $this->_gd = imagecreatetruecolor($imgWidth, $imgHeight);
        imagealphablending($this->_gd, false);
        $col = imagecolorallocatealpha($this->_gd, 255, 255, 255, 127);
        imagefilledrectangle($this->_gd, 0, 0, $imgWidth, $imgHeight, $col);

        $clrBlack = imagecolorallocate($this->_gd, 0, 0, 0);

        foreach ($this->_placedPictures as $img)
        {
            if ($img->file->getExtension() == 'png')
                $im = imagecreatefrompng($img->file->getPathName());

            if ($img->file->getExtension() == 'gif')
                $im = imagecreatefromgif($img->file->getPathName());

            $x = $img->x * $this->_tileWidth;
            $y = $img->y * $this->_tileHeight;

            imagealphablending($this->_gd, true);
            imagecopy($this->_gd, $im, $x, $y, 0, 0, $img->pixelWidth, $img->pixelHeight);
            //imagerectangle($this->_gd, $x, $y, $img->x * 8 + $img->pixelWidth, $img->y * 8 + $img->pixelHeight, $clrBlack);

            imagedestroy($im);
        }

        imagealphablending($this->_gd, false);

        return $this->_gd;
    }

    /**
     * Return CSS
     *
     * @return string
     */
    public function asCss()
    {
        foreach ($this->_placedPictures as $img)
        {
            $x = $img->x * $this->_tileWidth;
            $y = $img->y * $this->_tileHeight;

            $filePathname = str_replace(__DIR__ . '\\', '', $img->file->getPathname());
            $filePathname = str_replace('\\', '/', $filePathname);

            $fileNameNoExt = substr($img->file->getFileName(), 0, strrpos($img->file->getFileName(), '.'));

            $css = ".{$fileNameNoExt} { ";
            $css .= "background-image: url('sprite.png'); ";
            $css .= "background-position: -{$x}px -{$y}px; ";
            $css .= "width: {$img->pixelWidth}px; height: {$img->pixelHeight}px; ";
            $css .= "}";

            $this->_css[] = $css;
        }

        return implode("\n", $this->_css);
    }

    /**
     * @param $pathname
     * @return void
     */
    public function writeCss($pathname)
    {
        file_put_contents($pathname, $this->asCss());
    }

    public function writePng($pathname)
    {
        $gd = $this->asGdImage();

        imagesavealpha($gd, true);
        imagepng($gd, $pathname);
    }
}