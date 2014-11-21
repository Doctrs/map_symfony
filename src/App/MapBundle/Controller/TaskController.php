<?php

namespace App\MapBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use App\MapBundle\Entity\Task;
use App\MapBundle\Form\TaskType;

/**
 * Task controller.
 *
 */
class TaskController extends Controller
{

    /**
     * Lists all Task entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->createQueryBuilder()
            ->select('r.id, r.name, rg.name as task_name, rg.id as task_id')
            ->from('AppMapBundle:Task', 'rg')
            ->leftJoin('rg.tasks','r')
            ->getQuery()
            ->getResult();

        $array = [];
        foreach($entities as $entity){
            if(!isset($array[$entity['task_id']])) {
                $array[$entity['task_id']] = array(
                    'name' => $entity['task_name'],
                    'id' => $entity['task_id'],
                    'entities' => []
                );
            }
            if($entity['id']){
                $array[$entity['task_id']]['entities'][] = $entity;
            }
        }

        return $this->render('AppMapBundle:Task:index.html.twig', array(
            'entities' => $array,
        ));
    }
    /**
     * Creates a new Task entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new Task();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('app_task', array('id' => $entity->getId())));
        }

        return $this->render('AppMapBundle:Task:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Task entity.
     *
     * @param Task $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Task $entity)
    {
        $form = $this->createForm(new TaskType(), $entity, array(
            'action' => $this->generateUrl('app_task_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('attr'=>array('class'=>'btn btn-success'),'label' => 'Создать'));

        return $form;
    }

    /**
     * Displays a form to create a new Task entity.
     *
     */
    public function newAction()
    {
        $entity = new Task();
        $form   = $this->createCreateForm($entity);

        return $this->render('AppMapBundle:Task:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Deletes a Task entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppMapBundle:Task')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Task entity.');
        }

        // Удаляем все картинки обьектов котоыре были связанны
        $entityMap = $em->getRepository('AppMapBundle:Map')->findby(array('maps' => $id));
        foreach($entityMap as $item){
            unlink('uploads/maps/' . $item->getImg());
            unlink('uploads/maps/' . $item->getImg() . '_edit');
        }

        $em->remove($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('app_task'));
    }
}
