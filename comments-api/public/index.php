<?php

// use App\Models\DB;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

use AppHandler\customErrorHandler as customErrorHandler;

require_once __DIR__ . '/../vendor/autoload.php';

$servername = "localhost";
$username = "root";
$password = "rootroot";
$database = "commentsDB";
$secretKey = "6LfQ8BAoAAAAAMK8prCSdQ6cOX52XW7MHITNviY6";

////////
function connect($sql)
{
  global $servername;
  global $username;
  global $password;
  global $database;
  try {
    $mysqli = new mysqli($servername, $username, $password, $database);
    $mysqli->set_charset("utf8");
    $link = mysqli_connect($servername, $username, $password, $database);
    $result = mysqli_query($link, $sql);
    $mysqli->close();
  } catch (PDOException $e) {
    $result = (array(
      "message" => $e->getMessage()
    ));
  };

  return $result;
}
////////////

$app = AppFactory::create();

$app->addRoutingMiddleware();

$app->add(function ($request, $handler) {
  $uri = $request->getUri();
  $path = $uri->getPath();
  if ($path != '/' && substr($path, -1) == '/') {
    $uri = $uri->withPath(substr($path, 0, -1));
    if ($request->getMethod() == 'GET') {
      $response = $handler->handle($request);

      return $response
        ->withHeader('Location', (string)$uri)
        ->withStatus(301);
    } else {

      return $request->withUri($uri);
    }
  }
  $response = $handler->handle($request);

  return $response;
});

// Define Custom Error Handler
$customErrorHandler = function (
  ServerRequestInterface $request,
  Throwable $exception,
  bool $displayErrorDetails,
  bool $logErrors,
  bool $logErrorDetails,
  ?LoggerInterface $logger = null
) use ($app) {
  global $error;
  if ($logger) {
    $logger->error($exception->getMessage());
  }

  $payload = ['error' => $exception->getMessage()];

  $response = $app->getResponseFactory()->createResponse();
  $response->getBody()->write(
    json_encode($payload, JSON_UNESCAPED_UNICODE)
  );
  $newResponse = $response->withStatus(403);

  return $newResponse;
};

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setDefaultErrorHandler($customErrorHandler);

// Create connection
try {
  $conn = new mysqli($servername, $username, $password);

  // Create database
  $sqlBD = "CREATE DATABASE IF NOT EXISTS $database";
  if ($conn->query($sqlBD) === TRUE) {
    $link = mysqli_connect($servername, $username, $password, $database);
    $sqlTB = "CREATE TABLE IF NOT EXISTS comments(
      id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
      userName VARCHAR(30) NOT NULL,
      comment VARCHAR(400) NOT NULL,
      creationDate VARCHAR(50) NOT NULL
    )";
  } else {
    throw new Exception("Не удалось создать базу данных: " . $conn->error);
  }
  $conn->close();
} catch (Exception $e) {
  $err = $e->getMessage();
};


$app->add(function ($request, $handler) {
  $response = $handler->handle($request);
  return $response
    ->withHeader('Access-Control-Allow-Origin', 'http://localhost:3000')
    ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});


$app->options('/{routes:.+}', function ($request, $response, $args) {
  return $response;
});

$app->get('/', function (Request $request, Response $response) {
  $response->getBody()->write('Hello World!');
  return $response;
});

$app->get('/comments', function (Request $request, Response $response) {
  $sql = "SELECT * FROM comments";
  $result = connect($sql);
  $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
  $payload = json_encode($rows);
  $response->getBody()->write($payload);
  return $response;
});

$app->post('/comments', function (Request $request, Response $response) {
  global $secretKey;

  $body = $request->getBody();
  $data = json_decode($body, true);
  $user = $data['userName'];
  $comment = $data['comment'];
  $date = $data['creationDate'];
  $captcha = $data['captcha'];

  $secret = $secretKey;
  $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secret}&response={$captcha}");
  $captcha_success = json_decode($verify);
  if ($captcha_success->success == false) {
    throw new Exception('Are you a robot?', 403);
  }

  $sql = "INSERT INTO comments (userName, comment, creationDate) VALUES
    ('$user', '$comment', '$date')";
  $result = connect($sql);
  $payload = json_encode($result);
  $response->getBody()->write($payload);
  return $response;
});

$app->delete('/comments/{id}', function (Request $request, Response $response, $params) {
  $id = $params['id'];
  $num = (int)$id;
  $sql = "DELETE FROM comments WHERE id=$num";
  $result = connect($sql);
  $payload = json_encode($result);
  $response->getBody()->write($payload);
  return $response;
});

$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
  throw new HttpNotFoundException($request);
});

$app->run();
