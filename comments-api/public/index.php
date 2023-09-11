<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/../vendor/autoload.php';

$servername = 'localhost';
$username = 'root';
$password = 'rootroot';
$database = 'commentsDB';
$secretKey = '6LfQ8BAoAAAAAMK8prCSdQ6cOX52XW7MHITNviY6';
$err = '';

// Функция для установления соединения с БД и отправки запроса
$connect = function ($sql) use ($servername, $username, $password, $database) {
  try {
    $mysqli = new mysqli($servername, $username, $password, $database);
  } catch (Exception $e) {
    throw new Exception('Ошибка подключения к базе данных.');
  };
  try {
    $mysqli->set_charset('utf8');
    $result = mysqli_query($mysqli, $sql);
    $mysqli->close();
  } catch (Exception $e) {
    throw new Exception('Ошибка отправки запроса к базе данных.');
  };

  return $result;
};

// Обработчик uri, который убирает лишние символы: '/'
$slashHandler = function ($request, $handler) {
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
};

$app = AppFactory::create();

$app->addRoutingMiddleware();

$app->add($slashHandler);

//Кастомный обработчик ошибок
$customErrorHandler = function (
  ServerRequestInterface $request,
  Throwable $exception,
  bool $displayErrorDetails,
  bool $logErrors,
  bool $logErrorDetails,
  ?LoggerInterface $logger = null
) use ($app, $err) {
  if ($logger) {
    $logger->error($exception->getMessage());
  }
  $payload = [];
  if (strlen($err) !== 0) {
    $payload = ['error' => $err];
    $err = '';
  } else {
    $payload = ['error' => $exception->getMessage()];
  }

  $response = $app->getResponseFactory()->createResponse();
  $response->getBody()->write(
    json_encode($payload, JSON_UNESCAPED_UNICODE)
  );
  $newResponse = $response->withStatus(403);

  return $newResponse;
};

// Добавление слоя для обработки ошибок
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setDefaultErrorHandler($customErrorHandler);

// Установление соединения с MySQL, cоздание БД и таблицы в ней (если их не существует)
try {
  $mysqli = new mysqli($servername, $username, $password);
  $sqlBD = "CREATE DATABASE IF NOT EXISTS $database";
  $mysqli->query($sqlBD);
  $mysqli->close();
  $link = new mysqli($servername, $username, $password, $database);
  $sqlTB = "CREATE TABLE IF NOT EXISTS comments(
      id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
      userName VARCHAR(30) NOT NULL,
      comment VARCHAR(400) NOT NULL,
      creationDate VARCHAR(50) NOT NULL
    )";
  $link->query($sqlTB);
  $link->close();
} catch (Exception $e) {
  // throw new Exception("Не удалось создать базу данных");
  $err = 'Не удалось создать базу данных';
};

// Создание Header для ответа
$app->add(function ($request, $handler) {
  $response = $handler->handle($request);
  return $response
    ->withHeader('Access-Control-Allow-Origin', 'http://localhost:3000')
    ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

// Обработка запросов OPTIONS
$app->options('/{routes:.+}', function ($request, $response, $args) {
  return $response;
});

// Обработка запросов на получение всех комментариев
$app->get('/comments', function (Request $request, Response $response) use ($connect) {
  $sql = "SELECT * FROM comments";
  $result = $connect($sql);
  $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
  $payload = json_encode($rows);
  $response->getBody()->write($payload);
  return $response;
});

// Обработка запросов на сохранение нового комментария с ReCAPTCHA
$app->post('/comments', function (Request $request, Response $response) use ($connect, $secretKey) {
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
    throw new Exception('Ты точно не робот?', 403);
  }

  $sql = "INSERT INTO comments (userName, comment, creationDate) VALUES
    ('$user', '$comment', '$date')";
  $result = $connect($sql);
  $payload = json_encode($result);
  $response->getBody()->write($payload);
  return $response;
});

// Обработка запросов на удаление комментария
$app->delete('/comments/{id}', function (Request $request, Response $response, $params) use ($connect) {
  $id = $params['id'];
  $num = (int)$id;
  $sql = "DELETE FROM comments WHERE id=$num";
  $result = $connect($sql);
  $payload = json_encode($result);
  $response->getBody()->write($payload);
  return $response;
});

// Обработка всех остальных запросов
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
  throw new HttpNotFoundException($request);
});

$app->run();
