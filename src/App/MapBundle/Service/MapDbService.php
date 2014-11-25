<?php

namespace App\MapBundle\Service;


class MapDbService{

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
    }

    public function getCoordMaps($id)
    {
        $entities = $this->em->createQueryBuilder()
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

        // по хэшу проверяется есть ли кэш
        // также по нему и выводим изображение
        $array['hash'] = md5(json_encode($array));

        return $array;
    }
}