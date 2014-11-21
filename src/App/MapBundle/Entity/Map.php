<?php

namespace App\MapBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Map
 */
class Map
{

    public $file;
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $img;

    /**
     * @var tinyint
     */
    private $x;

    /**
     * @var tinyint
     */
    private $y;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $coords_m;

    /**
     * @var \App\MapBundle\Entity\Task
     */
    private $maps;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->coords_m = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Map
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set img
     *
     * @param string $img
     * @return Map
     */
    public function setImg($img)
    {
        $this->img = $img;

        return $this;
    }

    /**
     * Get img
     *
     * @return string 
     */
    public function getImg()
    {
        return $this->img;
    }

    /**
     * Set x
     *
     * @param \tinyint $x
     * @return Map
     */
    public function setX($x)
    {
        $this->x = $x;

        return $this;
    }

    /**
     * Get x
     *
     * @return \tinyint 
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * Set y
     *
     * @param \tinyint $y
     * @return Map
     */
    public function setY($y)
    {
        $this->y = $y;

        return $this;
    }

    /**
     * Get y
     *
     * @return \tinyint 
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * Add coords_m
     *
     * @param \App\MapBundle\Entity\Coordinates $coordsM
     * @return Map
     */
    public function addCoordsM(\App\MapBundle\Entity\Coordinates $coordsM)
    {
        $this->coords_m[] = $coordsM;

        return $this;
    }

    /**
     * Remove coords_m
     *
     * @param \App\MapBundle\Entity\Coordinates $coordsM
     */
    public function removeCoordsM(\App\MapBundle\Entity\Coordinates $coordsM)
    {
        $this->coords_m->removeElement($coordsM);
    }

    /**
     * Get coords_m
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCoordsM()
    {
        return $this->coords_m;
    }

    /**
     * Set maps
     *
     * @param \App\MapBundle\Entity\Task $maps
     * @return Map
     */
    public function setMaps(\App\MapBundle\Entity\Task $maps = null)
    {
        $this->maps = $maps;

        return $this;
    }

    /**
     * Get maps
     *
     * @return \App\MapBundle\Entity\Task 
     */
    public function getMaps()
    {
        return $this->maps;
    }


    public function __toString(){
        return $this->name ? $this->name : '';
    }
    /**
     * @var int
     */
    private $coords;


    /**
     * Set coords
     *
     * @param \int $coords
     * @return Map
     */
    public function setCoords($coords)
    {
        $this->coords = $coords;

        return $this;
    }

    /**
     * Get coords
     *
     * @return \int 
     */
    public function getCoords()
    {
        return $this->coords;
    }
}
