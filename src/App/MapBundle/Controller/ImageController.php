<?php

namespace App\MapBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ImageController extends Controller
{
    /**
     * валидные типы картинок с функциями для создания и сохранения
     */
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

    public function getValidTypes(){
        return $this->validArrayType;
    }

    /**
     * @param $array
     * Массив из координат
     *
     * Рисует на картинке сетку и метки
     */
    public function getImage($array)
    {
        // функции отрисовки текста слишком тяжелые поэтому кэшируем все это дело
        if(file_exists('uploads/maps/' . $array['img'] . '_edit_' . $array['hash'])){
            return;
        }

        list($x, $y) = getimagesize('uploads/maps/' . $array['img']);

        // определение расстояния между линиями
        $pixX = $x / $array['x'];
        $pixY = $y / $array['y'];

        // размер шрифта (для карт разного размера) и шрифт
        $fontSize = ceil(max($x, $y) / 40);
        $font = 'static/font/arial.ttf';
        $lineSize = ceil(max($x, $y) / 200);

        $array['radius'] *= $x / 600;

        // определение типа изображения и создание нового
        $type = mime_content_type('uploads/maps/' . $array['img']);
        $createFunctionImg = $this->validArrayType[$type]['create'];
        $saveFunctionImg = $this->validArrayType[$type]['save'];
        $img = $createFunctionImg('uploads/maps/' . $array['img']);

        $color = array(
            'red' => imagecolorallocate($img, 255, 0, 0),
            'white' => imagecolorallocate($img, 255, 255, 255),
            'black' => imagecolorallocate($img, 0, 0, 0),
        );
        // отрисовка линий по X и проставление цифр в квадратах
        for ($i = 0; $i < $x; $i++) {
            // место проведения линии
            $varX = $pixX * $i;
            // место простановки цифры
            $varText = $varX + $pixX / 2;
            // Обрамление цифры (для темных карт)
            // Белая тень (на случай темных карт)
            imagettftext($img, $fontSize, 0,
                $varText + 15, $fontSize + 15,
                $color['white'], $font, chr($i + 65));
            // Цифра
            imagettftext($img, $fontSize, 0,
                $varText + 10, $fontSize + 10,
                $color['black'], $font, chr($i + 65));
            // линия
            imageline($img, $varX, 0, $varX, $y, $color['black']);
        }
        for ($i = 0; $i < $y; $i++) {
            $varY = $pixY * $i;
            $varText = $varY + $pixY / 2;
            imagettftext($img, $fontSize, 0,
                15, $varText + 15,
                $color['white'], $font, $i);
            imagettftext($img, $fontSize, 0,
                10, $varText + 10,
                $color['black'], $font, $i);
            imageline($img, 0, $varY, $x, $varY, $color['black']);
        }
        // Отрисовка точек на карте
        imagesetthickness($img, min(9, $lineSize));
        $array['radius'] *= 1000 / max($x, $y);
        foreach ($array['coords'] as $item) {
            imagearc(
                $img,
                $item['c_x'], $item['c_y'],
                $array['radius'], $array['radius'],
                0, 359.9,
                $color['red']);
        }

        $saveFunctionImg($img, 'uploads/maps/' . $array['img'] . '_edit_' . $array['hash']);
    }

    /**
     * @param $name
     * Название картинки
     * @param $x
     * Квадрат по x
     * @param $y
     * Квадрат по y
     * @param $z
     * зум
     * @param $gridX
     * количетсво квадратов сетки по x
     * @param $gridY
     * количество квадратов сетки по y
     *
     * Расчитываем квадраты по 256 пикселей для Я.Карт и рисуем на них сетку
     */

    public function layoutAction($name, $x, $y, $z, $gridX, $gridY){

        // кэшируем картинки яндекса чтобы не перерисовывать их каждый раз
        $arguments = func_get_args();
        $cache = 'uploads/maps/cache/' . join('_', $arguments);
        // если кэша нет то создаем
        if(!file_exists($cache)) {

            $type = mime_content_type('uploads/maps/' . $name);
            $sizeIm = getimagesize('uploads/maps/' . $name);
            // приводим размеры изображения к масштабу
            $sizeIm[0] /= pow(2, $z);
            $sizeIm[1] /= pow(2, $z);
            // находим максимальный размер по которому и будем брать грань квадрата
            $size = max($sizeIm[0], $sizeIm[1]);

            // создаем квадрат для отдачи (256х256)
            $functionCreate = $this->validArrayType[$type]['create'];
            $im = $functionCreate('uploads/maps/' . $name);
            $image = imagecreate(256, 256);
            imagecolorallocate($image, 0, 0, 0);
            imagecopyresampled($image, $im, 0, 0, $x * $size, $y * $size, 256, 256, $size, $size);

            // размеры сетки с учетом масштаба
            $gridSize = array($gridX ? $sizeIm[0] / $gridX * pow(2, $z) : 0, $gridY ? $sizeIm[1] / $gridY * pow(2, $z) : 0);
            // если указана сетка
            // действия для оси X
            if ($gridSize[0]) {
                // координата сетки
                $numberX = 0;
                // первое вхождение сетки в квадрат
                $firstX = 0;
                // если первый квадрат на этой оси, то первое вхождение - 0
                // если нет то находим количество линий до этого квадрата
                // и высчитываем линию в данном квадрате
                if ($x != 0) {
                    $numberX = ceil($size * $x / $gridSize[0]);
                    $firstX = $gridSize[0] * $numberX - $size * $x;
                }
                // проходимя по квадрату и рисуем линии с первой позиции каждые $gridSize (размер клеток)
                for ($i = $firstX; $i < $size * ($x + 1) - 10; $i += $gridSize[0]) {
                    // так как исходный квадрат и будущий квадрат различаются по величине
                    // высчитываем разницу
                    $varX = $i * (256 / $size);
                    // рисуем букву (15 и +7 чтобы они не попадали друг на друга)
                    imagestring($image, 0, $varX + 7, 15, chr(($numberX++) + 65), imagecolorallocate($image, 0, 0, 0));
                    imageline($image, $varX, 0, $varX, 256, imagecolorallocate($image, 0, 0, 0));
                }
            }
            // по аналогии с осью X
            if ($gridSize[1]) {
                $numberY = 0;
                $firstY = 0;
                if ($y != 0) {
                    $numberY = ceil($size * $y / $gridSize[1]);
                    $firstY = $gridSize[1] * $numberY - $size * $y;
                }
                for ($i = $firstY; $i < $size * ($y + 1); $i += $gridSize[1]) {
                    $varY = $i * (256 / $size);
                    imagestring($image, 0, 15, $varY + 7, $numberY++, imagecolorallocate($image, 0, 0, 0));
                    imageline($image, 0, $varY, 256, $varY, imagecolorallocate($image, 0, 0, 0));
                }
            }
            imagepng($image, $cache);
        }

        // кэширование файлов через браузер
        $response = new \Symfony\Component\HttpFoundation\Response();
        $response->headers->set('Cache-Control', 'public');
        $response->headers->set('Cache-Control', 'max-age=180000');
        $response->headers->set('Content-Type', 'image/png');
        $response->headers->set('Content-length', filesize($cache));
        $response->sendHeaders();

        $response->setContent(readfile($cache));
        return $response;
    }


    public function saveMapAction($id, $source)
    {
        $entity = $this->get('db_service')->getCoordMaps($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Map entity.');
        }
        // Отдаем отредактированную картинку (_edit)
        $filename = 'uploads/maps/' . $entity['img'] . (!$source ? '_edit_' . $entity['hash'] : '');

        $response = new \Symfony\Component\HttpFoundation\Response();
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', mime_content_type($filename));
        $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($entity['img']) . '";');
        $response->headers->set('Content-length', filesize($filename));
        $response->sendHeaders();

        $response->setContent(readfile($filename));
        return $response;
    }

    public function saveCoordAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppMapBundle:Coordinates')->findBy(array('coords' => $id));

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Map entity.');
        }
        // Генерация строки с координатами для загрузки из браузера
        $text = "Название\tКоординаты\tКвадрат\n__________________________________\n\n";
        foreach ($entity as $key => $r) {
            $text .= 'Точка ' . $key . "\t\t" . "x:" . $r->getX() . " y:" . $r->getY() . "\t" . $r->getName() . "\n";
        }


        $filename = $text;

        // заголовки загрузки файла
        $response = new \Symfony\Component\HttpFoundation\Response();
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', 'text');
        $response->headers->set('Content-Disposition', 'attachment; filename="Координаты.txt";');
        $response->headers->set('Content-length', strlen($filename));
        $response->sendHeaders();

        $response->setContent($filename);
        return $response;
    }

}