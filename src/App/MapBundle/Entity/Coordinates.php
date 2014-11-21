<?php

namespace App\MapBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Coordinates
 */
class Coordinates
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
     * @var \App\MapBundle\Entity\Map
     */
    private $coords;


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
     * @return Coordinates
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
     * Set coords
     *
     * @param \App\MapBundle\Entity\Map $coords
     * @return Coordinates
     */
    public function setCoords(\App\MapBundle\Entity\Map $coords = null)
    {
        $this->coords = $coords;

        return $this;
    }

    /**
     * Get coords
     *
     * @return \App\MapBundle\Entity\Map 
     */
    public function getCoords()
    {
        return $this->coords;
    }
    /**
     * @var integer
     */
    private $x;

    /**
     * @var integer
     */
    private $y;


    /**
     * Set x
     *
     * @param integer $x
     * @return Coordinates
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
     * @return Coordinates
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

    public function __toString(){
        return $this->name ? $this->name : '';
    }
}
