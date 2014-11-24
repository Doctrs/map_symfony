<?php

namespace App\MapBundle\Controller;

use App\MapBundle\Entity\Coordinates as Coordinates;
use App\MapBundle\Entity\Map;
use App\MapBundle\Form\MapType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Map controller.
 *
 */
class MapController extends Controller
{
    /**
     * Creates a new Map entity.
     *
     */

    public function indexAction(){
        $breadcrumbs = $this->get('white_october_breadcrumbs');
        $breadcrumbs->addItem('Главная', '');

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppMapBundle:Map')->findAll();

        return $this->render('AppMapBundle:Map:index.html.twig',
            array(
                'entities' => $entities
            ));
    }


    public function createAction(Request $request)
    {
        $entity = new Map();
        $em = $this->getDoctrine()->getManager();
        // Задаем занчения по умолчанию
        $entity->setX(2);
        $entity->setY(2);
        $entity->setRadius(10);

        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            return $this->render('AppMapBundle:Map:new.html.twig', array('entity' => $entity, 'form' => $form->createView(),));
        }
        $imgName = (new \DateTime())->getTimestamp();
        $entity->getImg()->move('uploads/maps', $imgName);
        // Если картинка не соответсвует валидному типу то ошибка
        $validTypes = $this->get('image_controller')->getValidTypes();
        if (!isset($validTypes[mime_content_type('uploads/maps/' . $imgName)])) {
            $form->get('img')->addError(new \Symfony\Component\Form\FormError('Необходимо изображение'));
            return $this->render('AppMapBundle:Map:new.html.twig', array('entity' => $entity, 'form' => $form->createView(),));
        }
        $entity->setImg($imgName);
        $em->persist($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('app_map_show',
            array(
                'id' => $entity->getId()
            )
        ));
    }

    /**
     * Creates a form to create a Map entity.
     *
     * @param Map $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Map $entity)
    {
        $type = new MapType();
        $form = $this->createForm($type, $entity,
            array('action' => $this->generateUrl('app_map_create'), 'method' => 'POST',)
        );

        $form->add('submit', 'submit', array('attr' => array('class' => 'btn btn-success'), 'label' => 'Создать'));

        return $form;
    }
    /**
     * Displays a form to create a new Map entity.
     *
     */
    public function newAction()
    {
        $breadcrumbs = $this->get('white_october_breadcrumbs');
        $breadcrumbs->addItem('Главная', $this->generateUrl('app_map_index'));
        $breadcrumbs->addItem('Новое задание', '');
        $entity = new Map();
        $form = $this->createCreateForm($entity);

        return $this->render('AppMapBundle:Map:new.html.twig', array('entity' => $entity, 'form' => $form->createView(),));
    }

    public function viewAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->createQueryBuilder()
            ->select('rg.id, rg.radius, rg.name, rg.img, rg.x, rg.y, r.name as c_name, r.x as c_x, r.y as c_y')
            ->from('AppMapBundle:Map', 'rg')
            ->leftJoin('rg.coords_m', 'r')
            ->where('rg.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();

        if (!$entities) {
            throw $this->createNotFoundException('Unable to find Map entity.');
        }
        $firstEnt = $entities[0];

        $breadcrumbs = $this->get('white_october_breadcrumbs');
        $breadcrumbs->addItem('Главная', $this->generateUrl('app_map_index'));
        $breadcrumbs->addItem($firstEnt['name'], '');

        $array = array(
            'id' => $firstEnt['id'],
            'name' => $firstEnt['name'],
            'img' => $firstEnt['img'],
            'x' => $firstEnt['x'],
            'y' => $firstEnt['y'],
            'radius' => $firstEnt['radius'],
            'coords' => []
        );
        foreach ($entities as $ent) {
            if ($ent['c_name']) {
                $array['coords'][] = $ent;
            }
        }
        // рисуем на картинке сетку и координаты
        $this->get('image_controller')->getImage($array);

        return $this->render('AppMapBundle:Map:view.html.twig', array('entity' => $array,));
    }

    public function saveAction(Request $request, $id)
    {
        $request = $request->request->all();
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppMapBundle:Map')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Map entity.');
        }
        $coords = $em->getRepository('AppMapBundle:Coordinates')->findby(array('coords' => $entity));
        foreach($coords as $coord) {
            $em->remove($coord);
        }
        // Если пользователь не загружал свою картинку то скачиваем Я.Карту
        if (isset($request['yandex_image']) && $request['yandex_image']) {
            $imgName = (new \DateTime())->getTimestamp();
            $entity->setImg($imgName);
            file_put_contents('uploads/maps/' . $imgName, file_get_contents($request['yandex_image']));
        }

        // Если в процессе были изменены размеры сетки то обновялем
        // помимо этого проверяем на валидность (проверка в ангуляре есть, поэтому не стал генерировать ошибку)
        if($request['x'] < 2){$request['x'] = 2;}
        if($request['x'] > 20){$request['x'] = 20;}
        if($request['y'] < 2){$request['y'] = 2;}
        if($request['y'] > 20){$request['y'] = 20;}
        if($request['rad'] < 10){$request['rad'] = 10;}
        if($request['rad'] > 100){$request['rad'] = 100;}

        $entity->setX($request['x']);
        $entity->setY($request['y']);
        $entity->setRadius($request['rad']);
        if (!$entity->getImg()) {
            $entity = $em->getRepository('AppMapBundle:Map')->find($id);
            return $this->render('AppMapBundle:Map:showMap.html.twig', array('entity' => $entity, 'error' => 'Необходимо получить изображение'));
        }
        $em->persist($entity);

        // Проходимся по полученному списку координат
        if (isset($request['coordx'])) {
            foreach ($request['coordx'] as $key => $item) {
                $coord = new Coordinates;
                $coord->setX($item);
                $coord->setY($request['coordy'][$key]);
                $coord->setName($request['coordname'][$key]);
                $coord->setCoords($entity);
                $em->persist($coord);
            }
        }
        $em->flush();

        return $this->redirect($this->generateUrl('app_map_view', array('id' => $entity->getId())));
    }

    /**
     * Finds and displays a Map entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->createQueryBuilder()
            ->select('rg.id, rg.radius, rg.name, rg.img, rg.x, rg.y, r.name as c_name, r.x as c_x, r.y as c_y')
            ->from('AppMapBundle:Map', 'rg')
            ->leftJoin('rg.coords_m', 'r')
            ->where('rg.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();

        if (!$entities) {
            throw $this->createNotFoundException('Unable to find Map entity.');
        }

        $firstEnt = $entities[0];
        list($sizeX, $sizeY) = getimagesize('uploads/maps/' . $firstEnt['img']);

        $breadcrumbs = $this->get('white_october_breadcrumbs');
        $breadcrumbs->addItem('Главная', $this->generateUrl('app_map_index'));
        if(stristr($this->get('request')->server->get('HTTP_REFERER'), 'new')){
            $breadcrumbs->addItem('Новое задание', $this->generateUrl('app_map_new'));
        } else {
            $breadcrumbs->addItem($firstEnt['name'], $this->generateUrl('app_map_view', array('id' => $firstEnt['id'])));
        }
        $breadcrumbs->addItem('Редактирование', '');

        $array = array(
            'id' => $firstEnt['id'],
            'radius' => $firstEnt['radius'],
            'name' => $firstEnt['name'],
            'img' => $firstEnt['img'],
            'x' => $firstEnt['x'],
            'y' => $firstEnt['y'],
            'size' => array(
                'x' => $sizeX,
                'y' => $sizeY,
            ),
            'coords' => []
        );
        foreach ($entities as $ent) {
            if ($ent['c_name']) {
                $array['coords'][] = $ent;
            }
        }

        return $this->render('AppMapBundle:Map:showMap.html.twig', array('entity' => $array, 'error' => false));
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

        // находим все файлы кэша по маске и удаляем
        $cacheFiles = glob('uploads/maps/cache/' . $entity->getImg() . '*');
        foreach($cacheFiles as $cache){
            unlink($cache);
        }

        // Удаляем карту и отредактированную карту для обьекта
        if ($entity->getImg()) {
            if (file_exists('uploads/maps/' . $entity->getImg())) {
                unlink('uploads/maps/' . $entity->getImg());
            }
            if (file_exists('uploads/maps/' . $entity->getImg() . '_edit')) {
                unlink('uploads/maps/' . $entity->getImg() . '_edit');
            }
        }

        $em->remove($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('app_map_index'));
    }
}
