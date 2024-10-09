<?php
namespace com\icemalta\kahuna\model;

require_once 'com/icemalta/kahuna/model/DBConnect.php';

use \PDO;
use \JsonSerializable;
use com\icemalta\kahuna\model\DBConnect;

class Product implements  JsonSerializable
{
    private static $db;
    private int|string $id = 0;
    private string $serial;
    private string $name;
    private int $warrantyLength = 0;

    public function __construct(string $serial, string $name, int $warrantyLength, int|string $id = 0)
    {
        $this->serial = $serial;
        $this->name = $name;
        $this->warrantyLength = $warrantyLength;
        $this->id = $id;
        self::$db = DBConnect::getInstance()->getConnection();
    }

    public function getId(): int
    {
        return $this->id;
    }
    
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getSerial(): string
    {
        return $this->serial;
    }

    public function setSerial(string $serial): self
    {
        $this->serial = $serial;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }
    
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getWarrantyLength(): int
    {
        return $this->warrantyLength;
    }
    
    public function setWarrantyLength(int $warrantyLength): self
    {
        $this->warrantyLength = $warrantyLength;
        return $this;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    public static function save(Product $product): Product
    {
        if ($product->getId() === 0) {
            // New Product (insert)
            $sql = 'INSERT INTO Product(serial, name, warrantyLength) VALUES (:serial, :name, :warrantyLength)';
            $sth = self::$db->prepare($sql);
        } else {
            // Update product (update)
            $sql = 'UPDATE Product SET serial = :serial, name = :name, warrantyLength = :warrantyLength WHERE id = :id';
            $sth = self::$db->prepare($sql);
            $sth->bindValue('id', $product->getId());
        }
        $sth->bindValue('serial', $product->getSerial());
        $sth->bindValue('name', $product->getName());
        $sth->bindValue('warrantyLength', $product->getWarrantyLength());
        $sth->execute();

        if ($sth->rowCount() > 0 && $product->getId() === 0){
            $product->setId(self::$db->lastInsertId());
        }

        return $product;
    }

    public static function load(?int $productId = null): array
{
        self::$db = DBConnect::getInstance()->getConnection();

        // Check if a specific product ID is provided
        if ($productId !== null) {
            // SQL to fetch a specific product by ID
            $sql = 'SELECT serial, name, warrantyLength, id FROM Product WHERE id = :id';
            $sth = self::$db->prepare($sql);
            $sth->bindParam(':id', $productId, PDO::PARAM_INT);
            $sth->execute();
            $product = $sth->fetch(PDO::FETCH_FUNC, fn(...$fields) => new Product(...$fields));
            
            // Return the product as an array or an empty array if not found
            return $product ? [$product] : [];
        } else {
            // SQL to fetch all products
            $sql = 'SELECT serial, name, warrantyLength, id FROM Product';
            $sth = self::$db->prepare($sql);
            $sth->execute();
            $products = $sth->fetchAll(PDO::FETCH_FUNC, fn(...$fields) => new Product(...$fields));
            return $products;
    }
}


    // public static function load(): array
    // {
    //     self::$db = DBConnect::getInstance()->getConnection();
    //     $sql = 'SELECT serial, name, warrantyLength, id FROM Product';
    //     $sth = self::$db->prepare($sql);
    //     $sth->execute();
    //     $products = $sth->fetchAll(PDO::FETCH_FUNC, fn(...$fields)=> new Product(...$fields));
    //     return $products;
    // }
}


