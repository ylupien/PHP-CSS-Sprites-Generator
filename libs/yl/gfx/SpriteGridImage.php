<?php

namespace yl\gfx;

class SpriteGridImage
{
    public $id = null;

    public $file;
    public $pixelWidth;
    public $pixelHeight;

    // Grid
    public $x;
    public $y;
    public $width;
    public $height;

    /**
     * @param $splFile SplFileInfo
     * @return SpriteGridImage
     */
    public static function create($splFile)
    {
        $img = new SpriteGridImage();
        $img->file = $splFile;
        
        list($img->pixelWidth, $img->pixelHeight, $bits) = getimagesize($splFile->getPathname());

        return $img;
    }

    /**
     * @param integer $pixelCnt
     * @return bool
     */
    public function notExceedSurface($pixelCnt)
    {
        return ($this->pixelWidth * $this->pixelHeight) < $pixelCnt;
    }
}
