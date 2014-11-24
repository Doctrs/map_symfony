<?php

namespace App\MapBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ImageController extends Controller
{
    private $validArrayType = array(
        'image/gif' => array(
            'create' => 'imagecreatefromgif',
            'save' => 'imagepng'
        ),
        'image/jpeg' => array(
            'create' => 'imagecreatefromjpeg',
            'save' => 'imagejpeg'
        ),
        'image/png' => array(
            'create' => 'imagecreatefrompng',
            'save' => 'imagepng'
        ),
    );

    function layoutAction($name, $x, $y, $z, $gridX, $gridY){
        $type = mime_content_type('uploads/maps/' . $name);
        $sizeIm = getimagesize('uploads/maps/' . $name);
        $sizeIm[0] /= pow(2, $z);
        $sizeIm[1] /= pow(2, $z);
        $size = max($sizeIm[0], $sizeIm[1]);

        $functionCreate = $this->validArrayType[$type]['create'];
        $im = $functionCreate('uploads/maps/' . $name);
        $sizeImage = 256;
        $image = imagecreate($sizeImage, $sizeImage);
        imagecolorallocate($image, 0, 0, 0);
        imagecopyresampled($image, $im, 0, 0, $x*$size, $y*$size, $sizeImage, $sizeImage, $size, $size);

        $gridSize = array(
            $sizeIm[0] / $gridX * pow(2, $z),
            $sizeIm[1] / $gridY * pow(2, $z)
        );
        if($size * $x == 0){
            $firstX = $gridSize[0];
        } else {
            $firstX = 0;
            while($firstX < $size * $x){
                $firstX += $gridSize[0];
            }
            $firstX = $firstX % ($size * $x);
        }
        for($i = $firstX ; $i < $size * ($x + 1); $i += $gridSize[0]){
            $varX = $i * ($sizeImage / $size);
            imageline($image, $varX, 0, $varX, 256, imagecolorallocate($image, 0, 0, 0));
        }
        if($size * $y == 0){
            $firstY = $gridSize[1];
        } else {
            $firstY = 0;
            while($firstY < $size * $y){
                $firstY += $gridSize[1];
            }
            $firstY = $firstY % ($size * $y);
        }
        for($i = $firstY ; $i < $size * ($y + 1); $i += $gridSize[1]){
            $varY = $i * ($sizeImage / $size);
            imageline($image, 0, $varY, 256, $varY, imagecolorallocate($image, 0, 0, 0));
        }

        header('Content-Type: image/png');
        imagepng($image);
        die();

        return new \Symfony\Component\HttpFoundation\Response();
    }

    function gridAction($name, $x, $y){
        $size = getimagesize('uploads/maps/' . $name);
        $image = imagecreate($size[0], $size[1]);

        $pixX = $x / $size[0];
        $pixY = $y / $size[1];

        $colors = array(
            'red' => imagecolorallocate($image, 255, 0, 0),
            'white' => imagecolorallocate($image, 255, 255, 255),
            'black' => imagecolorallocate($image, 0, 0, 0),
        );

        $color = ImageColorAllocate($image, 0, 0, 0);
        ImageColorTransparent($image, $color);

        for ($i = 0; $i < $x; $i++) {
            $varX = $pixX * $i;
            //imageline($image, $varX, 0, $varX, $y, $colors['black']);
        }
        for ($i = 0; $i < $y; $i++) {
            $varY = $pixY * $i;
            //imageline($image, 0, $varY, $x, $varY, $colors['black']);
        }


        imagepng($image, 'uploads/maps/' . $name . '_grid');
    }
}