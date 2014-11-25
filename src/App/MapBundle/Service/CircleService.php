<?php

namespace App\MapBundle\Service;


class CircleService{

    private $src_img;
    private $src_w;
    private $src_h;
    private $dst_img;
    private $dst_w;
    private $dst_h;

    public function __construct(){}

    public function setParams($img, $src_w, $src_h, $dstWidth, $dstHeight)
    {
        $this->src_img = $img;
        $this->src_w = $src_w;
        $this->src_h = $src_h;
        $this->dst_w = $dstWidth;
        $this->dst_h = $dstHeight;
    }

    public function delete()
    {
        if (is_resource($this->dst_img))
        {
            imagedestroy($this->dst_img);
        }
    }

    public function display($filename = '')
    {
        if(!$filename) {
            header("Content-type: image/png");
            imagepng($this->dst_img);
        } else {
            imagepng($this->dst_img, $filename);
        }
        return $this;
    }

    public function reset()
    {
        if (is_resource(($this->dst_img))) {
            imagedestroy($this->dst_img);
        }
        $this->dst_img = imagecreatetruecolor($this->dst_w, $this->dst_h);
        imagecopy($this->dst_img, $this->src_img, 0, 0, $this->src_w, $this->src_h, $this->dst_w, $this->dst_h);
        return $this;
    }

    public function size($dstWidth, $dstHeight)
    {
        $this->dst_w = $dstWidth;
        $this->dst_h = $dstHeight;
        return $this->reset();
    }

    public function crop()
    {
        $this->reset();
        $mask = imagecreatetruecolor($this->dst_w, $this->dst_h);
        $maskTransparent = imagecolorallocate($mask, 255, 0, 255);
        imagecolortransparent($mask, $maskTransparent);
        imagefilledellipse($mask, $this->dst_w / 2, $this->dst_h / 2, $this->dst_w, $this->dst_h, $maskTransparent);

        imagecopymerge($this->dst_img, $mask, 0, 0, 0, 0, $this->dst_w, $this->dst_h, 100);
        $dstTransparent = imagecolorallocate($this->dst_img, 255, 0, 255);
        imagefill($this->dst_img, 0, 0, $dstTransparent);
        imagefill($this->dst_img, $this->dst_w - 1, 0, $dstTransparent);
        imagefill($this->dst_img, 0, $this->dst_h - 1, $dstTransparent);
        imagefill($this->dst_img, $this->dst_w - 1, $this->dst_h - 1, $dstTransparent);
        imagecolortransparent($this->dst_img, $dstTransparent);
        return $this;
    }
}