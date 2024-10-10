CREATE DATABASE IF NOT EXISTS kahuna;

USE kahuna;

CREATE TABLE IF NOT EXISTS Product(
    id                  INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    serial              VARCHAR(255) NOT NULL,
    name                VARCHAR(255) NOT NULL,
    warrantyLength      INT(11) NOT NULL
);

-- DELETE FROM Product WHERE id = 1;

CREATE TABLE IF NOT EXISTS User(
    id                 INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    firstName          VARCHAR(55)  NOT NULL,
    lastName           VARCHAR(55)  NOT NULL,
    email              VARCHAR(100) NOT NULL UNIQUE, 
    password           VARCHAR(255) NOT NULL,
    accessLevel        CHAR(10)     NOT NULL DEFAULT 'user',
    phone              VARCHAR(20)  NULL    ,
    address            VARCHAR(100) NULL    
);

CREATE TABLE IF NOT EXISTS AccessToken(
    id              INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    userId          INT NOT NULL,
    token           VARCHAR(255) NOT NULL,
    birth           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT c_accesstoken_user
        FOREIGN KEY(userId) REFERENCES User(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Registration(
    id               INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    userId           INT      NOT NULL,
    productId        INT      NOT NULL,
    registrationDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT c_registration_user FOREIGN KEY (userId) REFERENCES User(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT c_registration_product FOREIGN KEY (productId) REFERENCES Product(id) ON UPDATE CASCADE ON DELETE CASCADE
);

-- INSERT INTO User (firstName, lastName, email, password)
-- VALUES ('Theo', 'Akakios', 'theo@test.com', 'abc123def456');

-- DELETE FROM User WHERE id = 5;
-- DELETE FROM User WHERE id = 6;
-- DELETE FROM User WHERE id = 7;
-- DELETE FROM Product WHERE id = 12;