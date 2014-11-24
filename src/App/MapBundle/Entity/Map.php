<?php

namespace App\MapBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Map
 */
class Map
{
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
    private $file;

    /**
     * @var string
     */
    private $img;

    /**
     * @var integer
     */
    private $x;

    /**
     * @var integer
     */
    private $y;

    /**
     * @var integer
     */
    private $radius;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $coords_m;

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
     * @param integer $x
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
     * @return integer 
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * Set y
     *
     * @param integer $y
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
     * @return integer 
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * Set radius
     *
     * @param integer $radius
     * @return Map
     */
    public function setRadius($radius)
    {
        $this->radius = $radius;

        return $this;
    }

    /**
     * Get radius
     *
     * @return integer 
     */
    public function getRadius()
    {
        return $this->radius;
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
}
