<?php
namespace com\icemalta\kahuna\model;

require_once 'com/icemalta/kahuna/model/DBConnect.php';

use \JsonSerializable;
use \PDO;
use com\icemalta\kahuna\model\DBConnect;

class User implements JsonSerializable
{
    private static $db;
    private int $id;
    private string $email;
    private string $password;
    private string $firstName;
    private string $lastName;
    private string $accessLevel;

    public function __construct(string $email, string $password, string $firstName, string $lastName, string $accessLevel = 'user', int $id = 0) 
    {
        $this->email = $email;
        $this->password = $password;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->accessLevel = in_array($accessLevel, ['user', 'admin']) ? $accessLevel : 'user';
        $this->id = $id;
        self::$db = DBConnect::getInstance()->getConnection();
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'accessLevel' => $this->accessLevel
        ];
    }

    public static function save(User $user): User
    {
        $hashed = password_hash($user->password, PASSWORD_DEFAULT);
        if ($user->getId() === 0) {
            // Insert
            $sql = 'INSERT INTO User(email, password, firstName, lastName, accessLevel) VALUES (:email, :password, :firstName, :lastName, :accessLevel)';
            $sth = self::$db->prepare($sql);
        } else {
            // Update
            $sql = 'UPDATE User SET email = :email, password = :password, firstName = :firstName, lastName = :lastName, accessLevel = :accessLevel WHERE id = :id';
            $sth = self::$db->prepare($sql);
            $sth->bindValue('id', $user->getId());
        }
        $sth->bindValue('email', $user->getEmail());
        $sth->bindValue('password', $hashed);
        $sth->bindValue('firstName', $user->firstName);
        $sth->bindValue('lastName', $user->lastName);
        $sth->bindValue('accessLevel', $user->accessLevel);
        $sth->execute();

        if ($sth->rowCount() > 0 && $user->getId() === 0) {
            $user->setId(self::$db->lastInsertId());
        }
        return $user;
    }

    public static function authenticate(User $user): ?User
{
    // print_r("Starting authentication for email: " . $user->getEmail() . "\n"); // Debugging output

    $sql = 'SELECT * FROM User WHERE email = :email';
    $sth = self::$db->prepare($sql);
    $sth->bindValue('email', $user->getEmail());
    $sth->execute();

    $result = $sth->fetch(PDO::FETCH_OBJ);
    if (!$result) {
        // print_r("No user found with that email.\n"); // Debugging output
        return null; // User not found
    }

    // print_r("User found: " . $result->email . "\n"); // Debugging output
    if (password_verify($user->getPassword(), $result->password)) {
        return new User(
            $result->email,
            $result->password, 
            $result->firstName,
            $result->lastName,
            $result->accessLevel,
            $result->id
        );
    } else {
        // print_r("Password mismatch for email: " . $user->getEmail() . "\n"); // Debugging output
        return null; // Password does not match
    }
}

    public static function delete(User $user): bool
    {
        $sql = "DELETE FROM User WHERE id = :id";
        $sth = self::$db->prepare($sql);
        $sth->bindValue("id", $user->getId());
        $sth->execute();
        return $sth->rowCount() > 0;
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

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getAccessLevel(): string
    {
        return $this->accessLevel;
    }

    public function setAccessLevel(string $accessLevel): self
    {
        if (in_array($accessLevel, ['user', 'admin'])) {
            $this->accessLevel = $accessLevel;
        }
        return $this;
    }
}
