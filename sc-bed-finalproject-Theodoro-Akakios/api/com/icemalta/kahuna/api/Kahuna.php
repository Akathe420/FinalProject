<?php
require 'com/icemalta/kahuna/model/User.php';
require 'com/icemalta/kahuna/model/AccessToken.php';
require 'com/icemalta/kahuna/util/ApiUtil.php';
require 'com/icemalta/kahuna/model/Product.php';
require 'com/icemalta/kahuna/model/Registration.php';
use com\icemalta\kahuna\util\ApiUtil;
use com\icemalta\kahuna\model\{AccessToken, Product, User, Registration};

cors();

$endPoints = [];
$requestData = [];
header("Content-Type: application/json; charset=UTF-8");

/* BASE URI */
$BASE_URI = '/kahuna/api/';

function sendResponse(mixed $data = null, int $code = 200, mixed $error = null): void
{
    if (!is_null($data)) {
        $response['data'] = $data;
    }
    if (!is_null($error)) {
        $response['error'] = $error;
    }
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    http_response_code($code);
}

function checkToken(array $requestData): bool 
{
    if (!isset($requestData['token']) || !isset($requestData['user'])) {
        return false;
    }
    $token = new AccessToken($requestData['user'], $requestData['token']);
    return AccessToken::verify($token);
}

/* Get Request Data */
$requestMethod = $_SERVER['REQUEST_METHOD'];
switch ($requestMethod) {
    case 'GET':
        $requestData = $_GET;
        break;
    case 'POST':
        $requestData = $_POST;
        break;
    case 'PATCH':
        parse_str(file_get_contents('php://input'), $requestData);
        ApiUtil::parse_raw_http_request($requestData);
        $requestData = is_array($requestData) ? $requestData : [];
        break;
    case 'DELETE':
        break;
    default:
        sendResponse(null, 405, 'Method not allowed.');
}

/* Extract EndPoint */
$parsedURI = parse_url($_SERVER["REQUEST_URI"]);
$path = explode('/', str_replace($BASE_URI, "", $parsedURI["path"]));
$endPoint = $path[0];
$requestData['dataId'] = isset($path[1]) ? $path[1] : null;
if (empty($endPoint)) {
    $endPoint = "/";
}

/* Extract Token */
if (isset($_SERVER["HTTP_X_API_KEY"])) {
    $requestData["user"] = $_SERVER["HTTP_X_API_USER"];
}
if (isset($_SERVER["HTTP_X_API_KEY"])) {
    $requestData["token"] = $_SERVER["HTTP_X_API_KEY"];
}

/* EndPoint Handlers */
$endPoints["/"] = function (string $requestMethod, array $requestData): void {
    sendResponse('Welcome to Kahuna API!');
};

$endPoints["404"] = function (string $requestMethod, array $requestData): void {
    sendResponse(null, 404, "Endpoint " . $requestData["endPoint"] . " not found.");
};

// Non-Authenticated Endpoints
// User Management Endpoints
// Creating an account
$endPoints["user"] = function (string $requestMethod, array $requestData): void {
    if ($requestMethod === 'POST') {
        // Register a new user account
        $email = $requestData['email'];
        $password = $requestData['password'];
        $user = new User($email, $password);
        $user = User::save($user);
        sendResponse($user, 201); // CREATED
    } elseif ($requestMethod === 'PATCH') {
        sendResponse(null, 501, 'Updating a user has not been implemented yet.');
    } elseif ($requestMethod === 'DELETE') {
        sendResponse(null, 501, 'Deleting a user has not been implemented yet.');
    } else {
        sendResponse(null, 405, 'Method not allowed.');
    }
};

$endPoints["login"] = function (string $requestMethod, array $requestData): void {
    if ($requestMethod === 'POST') {
        $email = $requestData['email'];
        $password = $requestData['password'];
        $user = new User($email, $password);
        $user = User::authenticate($user);
        if ($user) {
            // Generate an access token for this user's login
            $token = new AccessToken($user->getId());
            $token = AccessToken::save($token);
            sendResponse(['user' => $user->getId(), 'token' => $token->getToken()]);
        } else {
            sendResponse(null, 401, 'Login failed.');
        }
    } else {
        sendResponse(null, 405, 'Method not allowed.');
    }
};

// Authenticated Endpoints
/* This will handle /product */
// This endpoint was tweaked a bit by Chatgpt

$endPoints["product"] = function (string $requestMethod, array $requestData): void {
    // Check if the user is logged in by verifying the token
    if (!checkToken($requestData)) {
        sendResponse(null, 401, 'Unauthorized');
        return;
    }

    // Get the user's access level and customer ID from the request data
    $accessLevel = $requestData['accessLevel']; // e.g., 'user' or 'admin'
    $userId = $requestData['userId']; // Assuming this comes from the token or session

    // If the request is a GET method (to view products)
    if ($requestMethod === 'GET') {
        if (isset($requestData['id'])) {
            $productId = $requestData['id'];
            $product = Product::load($productId); // Load product by ID
            if (!$product) {
                sendResponse(null, 404, 'Product not found.');
            } else {
                sendResponse($product);
            }
        } else {
            $products = Product::load(); // Load all products
            sendResponse($products);
        }

    // If the request is a POST method (to add a new product or register a product)
    } elseif ($requestMethod === 'POST') {
        // Admin can add new products
        if ($accessLevel === 'admin') {
            $serial = $requestData['serial'];
            $name = $requestData['name'];
            $warrantyLength = $requestData['warrantyLength'];
            
            $product = new Product($serial, $name, $warrantyLength);
            $savedProduct = Product::save($product); // Save product to database

            sendResponse($savedProduct, 201);

        // User can register a product they have purchased
        } elseif ($accessLevel === 'user') {
            $productId = $requestData['productId']; // Product ID should be provided

            // Step 1: Check if the product exists by ID
            $product = Product::load($productId); // Method to load product by ID
            if (!$product) {
                sendResponse(null, 404, 'Product not found.');
                return;
            }

            // Step 2: Check if the product is already registered by this user
            $existingRegistration = Registration::checkIfRegistered($userId, $productId);
            if ($existingRegistration) {
                sendResponse(null, 400, 'Product already registered.');
                return;
            }

            // Step 3: Register the product (store the userId and productId in the Registration table)
            $registration = Registration::save($userId, $productId);
            if ($registration) {
                sendResponse($registration, 201, 'Product successfully registered.');
            } else {
                sendResponse(null, 500, 'Failed to register product.');
            }

        } else {
            sendResponse(null, 403, 'Forbidden: Only admins can add products, users can register products.');
        }

    } else {
        sendResponse(null, 405, 'Method not allowed.');
    }
};


// $endpoints["product"] = function (string $requestMethod, array $requestData): void {
//     // if (checkToken($requestData)) { // Check if user is logged in 
//     if($requestMethod === 'GET'){
//         $products = Product::load();
//         sendResponse($products);
//     } elseif ($requestMethod === 'POST') {
//         $serial = $requestData['serial'];
//         $name = $requestData['name'];
//         $warrantyLength = $requestData['warrantyLength'];
//         $product = new Product($serial, $name, $warrantyLength);
//         $product = Product::save($product);
//         sendResponse($product, 201);
//         // } Check for admin level
//     } else {
//         sendResponse(null, 405, 'Method not allowed.');
//     }
// };

$endPoints["logout"] = function (string $requestMethod, array $requestData): void {
    if ($requestMethod === 'POST') {
        if (checkToken($requestData)) {
            $userId = $requestData['user'];
            $token = new AccessToken($userId);
            $token = AccessToken::delete($token);
            sendResponse('You have been logged out.');
        } else {
            sendResponse(null, 403, 'Missing, invalid or expired token.');
        }
    } else {
        sendResponse(null, 405, 'Method not allowed.');
    }
};

$endPoints["token"] = function (string $requestMethod, array $requestData): void {
    if ($requestMethod === 'GET') {
        sendResponse(['valid' => checkToken($requestData), 'token' => $requestData['token']]);
    } else {
        sendResponse(null, 405, 'Method not allowed.');
    }
};

function cors()
{
    // Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
        // you want to allow, and if so:
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }

    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            // may also be using PUT, PATCH, HEAD etc
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PATCH, DELETE");

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

        exit(0);
    }
}

try {
    if (isset($endPoints[$endPoint])) {
        $endPoints[$endPoint]($requestMethod, $requestData);
    } else {
        $endPoints["404"]($requestMethod, array("endPoint" => $endPoint));
    }
} catch (Exception $e) {
    sendResponse(null, 500, $e->getMessage());
} catch (Error $e) {
    sendResponse(null, 500, $e->getMessage());
}