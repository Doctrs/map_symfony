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

    public function getCoordinatesAction($id)
    {
        $entity = $this->get('db_service')->getCoordMaps($id);

        $breadcrumbs = $this->get('white_october_breadcrumbs');
        $breadcrumbs->addItem('Главная', $this->generateUrl('app_map_index'));
        $breadcrumbs->addItem($entity['name'], $this->generateUrl('app_map_view', array('id' => $entity['id'])));
        $breadcrumbs->addItem('Точки на карте', '');

        $validTypes = $this->get('image_controller')->getValidTypes();
        $type = mime_content_type('uploads/maps/' . $entity['img'] . '_edit_' . $entity['hash']);
        $createFunctionImg = $validTypes[$type]['create'];

        $image = $createFunctionImg('uploads/maps/' . $entity['img'] . '_edit_' . $entity['hash']);
        // высчитываем окружность и добавялем немного чтобы красный круг вошел
        $entity['radius'] += 50;

        $circleCrop = $this->get('circle_service');
        foreach($entity['coords'] as &$coord){

            // проверяем по хэшу картику в кэшэ
            $coord['hash'] = md5(json_encode($coord));
            if(file_exists('uploads/maps/coords/' . $entity['img'] . '_' . $coord['hash'])){
                continue;
            }
            // вырезаем круг
            $circleCrop->setParams($image,
                $coord['c_x'] - $entity['radius']/2,
                $coord['c_y'] - $entity['radius']/2,
                $entity['radius'], $entity['radius']);
            $circleCrop->crop()->display('uploads/maps/coords/' . $entity['img'] . '_' . $coord['hash']);
            $circleCrop->delete();
        }unset($coord);

        return $this->render('AppMapBundle:Map:showCoord.html.twig',
            array('entity' => $entity)
        );
    }

    public function createAction(Request $request)
    {
        $breadcrumbs = $this->get('white_october_breadcrumbs');
        $breadcrumbs->addItem('Главная', $this->generateUrl('app_map_index'));
        $breadcrumbs->addItem('Новое задание', '');

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
        // модель для рабоыт с БД
        $entity = $this->get('db_service')->getCoordMaps($id);

        // хлебные крошки
        $breadcrumbs = $this->get('white_october_breadcrumbs');
        $breadcrumbs->addItem('Главная', $this->generateUrl('app_map_index'));
        $breadcrumbs->addItem($entity['name'], '');

        // рисуем на картинке сетку и координаты
        $this->get('image_controller')->getImage($entity);

        return $this->render('AppMapBundle:Map:view.html.twig', array('entity' => $entity));
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
            return $this->render('AppMapBundle:Map:show.html.twig',
                array(
                    'entity' => $entity,
                    'error' => 'Необходимо получить изображение'
                )
            );
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
        $entity = $this->get('db_service')->getCoordMaps($id);
        list($sizeX, $sizeY) = getimagesize('uploads/maps/' . $entity['img']);
        $entity['size'] = array(
            'x' => $sizeX,
            'y' => $sizeY
        );

        $breadcrumbs = $this->get('white_october_breadcrumbs');
        $breadcrumbs->addItem('Главная', $this->generateUrl('app_map_index'));
        if(stristr($this->get('request')->server->get('HTTP_REFERER'), 'new')){
            $breadcrumbs->addItem('Новое задание', $this->generateUrl('app_map_new'));
        } else {
            $breadcrumbs->addItem($entity['name'], $this->generateUrl('app_map_view', array('id' => $entity['id'])));
        }
        $breadcrumbs->addItem('Редактирование', '');

        return $this->render('AppMapBundle:Map:show.html.twig', array('entity' => $entity, 'error' => false));
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
            // удаляем все картинки в том числе и закэшированные
            $cacheFiles = glob('uploads/maps/' . $entity->getImg() . '*');
            foreach($cacheFiles as $cache){
                unlink($cache);
            }
        }
        // Удаляем закжшированные точки координат
        $coordinateCacheFiles = glob('uploads/maps/coords/' . $entity->getImg() . '*');
        foreach($coordinateCacheFiles as $cache){
            unlink($cache);
        }

        $em->remove($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('app_map_index'));
    }
}
