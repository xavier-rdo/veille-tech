<?php

class ProductDto
{
    private $id;
    private $name;
    private $description;
    private $price;

    /**
     * __construct
     *
     * @param int    $id
     * @param string $name
     * @param string $description
     * @param float  $price
     */
    public function __construct($id, $name, $description, $price)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
    }

    public function getId()  { return $this->id; }
    public function getName() { return $this->name; }
    public function getDescription() { return $this->description; }
    public function getPrice() { return $this->price; }

    public function __toString()
    {
        return sprintf(
            "%u - %s (%s) - %1.2f €",
            $this->id,
            $this->name,
            $this->description,
            $this->price
        );
    }
}

