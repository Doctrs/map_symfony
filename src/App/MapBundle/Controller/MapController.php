<?php

namespace App\MapBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use App\MapBundle\Entity\Map;
use App\MapBundle\Entity\Coordinates as Coordinates;
use App\MapBundle\Form\MapType;

/**
 * Map controller.
 *
 */
class MapController extends Controller
{
    /**
     * Типы изображений и функции для работы с ними
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

    /**
     * Creates a new Map entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new Map();
        $em = $this->getDoctrine()->getManager();

        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            return $this->render('AppMapBundle:Map:new.html.twig', array(
                'entity' => $entity,
                'form'   => $form->createView(),
            ));
        }
        // если картинки нет то переадресация на tpl с картой
        $type = 'map';
        if($entity->getImg()) {
            $type = 'img';
            $imgName = (new \DateTime())->getTimestamp();
            $entity->getImg()->move('uploads/maps', $imgName);
            // Если картинка не соответсвует валидному типу то ошибка
            if (!isset($this->validArrayType[mime_content_type('uploads/maps/' . $imgName)])) {
                $form->get('img')->addError(new \Symfony\Component\Form\FormError('Необходимо изображение'));
                return $this->render('AppMapBundle:Map:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
                ));
            }
            $entity->setImg($imgName);
        }
        $em->persist($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('app_map_show', array('id' => $entity->getId(), 'type' => $type)));
    }

    /**
     * Creates a form to create a Map entity.
     *
     * @param Map $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Map $entity, $id = 0)
    {
        $type = new MapType();
        $form = $this->createForm($type, $entity, array(
            'action' => $this->generateUrl('app_map_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('attr'=>array('class'=>'btn btn-success'),'label' => 'Создать'));

        return $form;
    }

    public function saveMapAction($id){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppMapBundle:Map')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Map entity.');
        }
        // Отдаем отредактированную картинку (_edit)
        $filename = 'uploads/maps/'.$entity->getImg().'_edit';

        $response = new \Symfony\Component\HttpFoundation\Response();
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', mime_content_type($filename));
        $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($entity->getImg()) . '";');
        $response->headers->set('Content-length', filesize($filename));
        $response->sendHeaders();

        $response->setContent(readfile($filename));
        return $response;
    }

    public function saveCoordAction($id){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppMapBundle:Coordinates')->findBy(array('coords'=>$id));

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Map entity.');
        }
        // Генерация строки с координатами для загрузки из браузера
        $text = "Название\tКоординаты\tКвадрат\n__________________________________\n\n";
        foreach($entity as $key=>$r){
            $text .= 'Точка ' . $key . "\t\t" .
                "x:" . $r->getX() . " y:" . $r->getY() . "\t" .
                $r->getName() . "\n";
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

    /**
     * Displays a form to create a new Map entity.
     *
     */
    public function newAction($id)
    {
        $entity = new Map();
        $em = $this->getDoctrine()->getManager();
        $entity->setMaps($em->merge($em->getRepository('AppMapBundle:Task')->find($id)));
        $form   = $this->createCreateForm($entity, $id);

        return $this->render('AppMapBundle:Map:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    private function getImage($array)
    {
        list($x, $y) = getimagesize('uploads/maps/'.$array['img']);

        // определение расстояния между линиями
        $pixX = $x/$array['x'];
        $pixY = $y/$array['y'];

        // определение типа изображения и создание нового
        $type = mime_content_type('uploads/maps/'.$array['img']);
        $createFunctionImg = $this->validArrayType[$type]['create'];
        $saveFunctionImg = $this->validArrayType[$type]['save'];
        $img = $createFunctionImg('uploads/maps/'.$array['img']);

        $color = array(
            'red' => imagecolorallocate($img, 255, 0, 0),
            'white' => imagecolorallocate($img, 255, 255, 255),
            'black' => imagecolorallocate($img, 0, 0, 0),
        );
        // отрисовка линий по X и проставление цифр в квадратах
        for($i = 0; $i < $x ;$i++){
            $varX = $pixX * $i;
            // Обрамление цифры (для темных карт)
            if($color['white']){
                imagefilledellipse($img, $varX + 12, 10, 20, 20, $color['white']);
            }
            // Цифра
            imagestring($img, 2, $varX + 7, 5, $i, $color['black']);
            // линия
            imageline($img, $varX, 0, $varX, $y, $color['black']);
        }
        for($i = 0; $i < $y ;$i++){
            $varY = $pixY * $i;
            if($color['white']) {
                imagefilledellipse($img, 10, $varY + 12, 20, 20, $color['white']);
            }
            imagestring($img, 2, 5, $varY + 7, $i, $color['black']);
            imageline($img, 0, $varY, $x, $varY, $color['black']);
        }
        // Отрисовка точек на карте
        foreach($array['coords'] as $item) {
            imagefilledellipse($img, $item['c_x'], $item['c_y'], 10, 10, $color['red']);
        }

        $saveFunctionImg($img, 'uploads/maps/'.$array['img'].'_edit');
    }

    public function viewAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->createQueryBuilder()
            ->select('rg.id, rg.name, rg.img, rg.x, rg.y, r.name as c_name, r.x as c_x, r.y as c_y')
            ->from('AppMapBundle:Map', 'rg')
            ->leftJoin('rg.coords_m','r')
            ->where('rg.id = :id')
            ->setParameter('id', $id);
        $entities = $entities
            ->getQuery()
            ->getResult();

        if (!$entities) {
            throw $this->createNotFoundException('Unable to find Map entity.');
        }
        $firstEnt = $entities[0];

        $array = array(
            'id' => $firstEnt['id'],
            'name'=> $firstEnt['name'],
            'img' => $firstEnt['img'],
            'x' => $firstEnt['x'],
            'y' => $firstEnt['y'],
            'coords' => []
        );
        foreach($entities as $ent){
            if($ent['c_name']) {
                $array['coords'][] = $ent;
            }
        }
        // рисуем на картинке сетку и координаты
        $this->getImage($array);

        return $this->render('AppMapBundle:Map:view.html.twig', array(
            'entity' => $array,
        ));
    }

    public function saveAction(Request $request, $id)
    {
        $request = $request->request->all();
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppMapBundle:Map')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Map entity.');
        }
        // Если пользователь не загружал свою картинку то скачиваем Я.Карту
        if(isset($request['yandex_image']) && $request['yandex_image']){
            $imgName = (new \DateTime())->getTimestamp();
            $entity->setImg($imgName);
            file_put_contents('uploads/maps/' . $imgName, file_get_contents($request['yandex_image']));
        }

        // Если в процессе были изменены размеры сетки то обновялем
        $entity->setX($request['x']);
        $entity->setY($request['y']);
        if(!$entity->getImg()){
            $entity = $em->getRepository('AppMapBundle:Map')->find($id);
            return $this->render('AppMapBundle:Map:showMap.html.twig', array(
                'entity' => $entity,
                'error' => 'Необходимо получить изображение'
            ));
        }
        $em->persist($entity);

        // Проходимся по полученному списку координат
        foreach($request['coordx'] as $key => $item){
            $coord = new Coordinates;
            $coord->setX($item);
            $coord->setY($request['coordy'][$key]);
            $coord->setName($request['coordname'][$key]);
            $coord->setCoords($entity);
            $em->persist($coord);
        }
        $em->flush();

        return $this->redirect($this->generateUrl('app_map_view', array('id' => $entity->getId())));
    }

    /**
     * Finds and displays a Map entity.
     *
     */
    public function showAction($id, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppMapBundle:Map')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Map entity.');
        }

        $templateName = ($type == 'map' ? 'showMap.html.twig' : 'show.html.twig');

        return $this->render('AppMapBundle:Map:' . $templateName, array(
            'entity' => $entity,
            'error' => false
        ));
    }

    /**
     * Deletes a Map entity.
     *
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppMapBundle:Map')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Map entity.');
        }

        // Удаляем карту и отредактированную карту для обьекта
        if($entity->getImg()) {
            if (file_exists('uploads/maps/' . $entity->getImg())) {
                unlink('uploads/maps/' . $entity->getImg());
            }
            if (file_exists('uploads/maps/' . $entity->getImg() . '_edit')) {
                unlink('uploads/maps/' . $entity->getImg() . '_edit');
            }
        }

        $em->remove($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('app_task'));
    }
}
