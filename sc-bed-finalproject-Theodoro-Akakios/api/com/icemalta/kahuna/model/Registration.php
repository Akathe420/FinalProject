<?php
namespace com\icemalta\kahuna\model;

require_once 'com/icemalta/kahuna/model/DBConnect.php';

use \PDO;
use \JsonSerializable;
use com\icemalta\kahuna\model\DBConnect;

class Registration implements JsonSerializable
{
    private static $db;
    private int $id = 0;
    private int $userId;
    private int $productId;
    private string $registrationDate;

    public function __construct(int $userId, int $productId, string $registrationDate = '', int $id = 0)
    {
        $this->userId = $userId;
        $this->productId = $productId;
        $this->registrationDate = $registrationDate ?: date('Y-m-d H:i:s');
        $this->id = $id;
        self::$db = DBConnect::getInstance()->getConnection();
    }

    // Getters and Setters
    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function setProductId(int $productId): self
    {
        $this->productId = $productId;
        return $this;
    }

    public function getRegistrationDate(): string
    {
        return $this->registrationDate;
    }

    public function setRegistrationDate(string $registrationDate): self
    {
        $this->registrationDate = $registrationDate;
        return $this;
    }

    // Method to save the registration
    public static function save(int $userId, int $productId): ?Registration
{
    self::$db = DBConnect::getInstance()->getConnection(); // Ensure database connection is established

    // Create a new registration instance
    $registration = new self($userId, $productId);

    // Prepare SQL statement for insertion
    $sql = 'INSERT INTO Registration(userId, productId) VALUES (:userId, :productId)';
    $sth = self::$db->prepare($sql);

    // Bind the values
    $sth->bindValue(':userId', $userId);
    $sth->bindValue(':productId', $productId);

    // Execute the query
    if ($sth->execute()) {
        // Set the ID for the new registration record
        $registration->setId(self::$db->lastInsertId());
        return $registration; // Return the new registration object
    }

    return null; // Return null if the registration fails
}

    // Check if a product is already registered by the user
    public static function checkIfRegistered(int $userId, int $productId): bool
    {
        self::$db = DBConnect::getInstance()->getConnection();
        $sql = 'SELECT * FROM Registration WHERE userId = :userId AND productId = :productId';
        $sth = self::$db->prepare($sql);
        $sth->bindParam(':userId', $userId, PDO::PARAM_INT);
        $sth->bindParam(':productId', $productId, PDO::PARAM_INT);
        $sth->execute();
        return $sth->fetch() ? true : false;
    }

    // Method for json serialization
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}