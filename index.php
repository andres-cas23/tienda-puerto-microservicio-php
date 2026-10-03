<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

require_once "supabase.php";

$metodo = $_SERVER["REQUEST_METHOD"];
$ruta = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$partes = array_values(array_filter(explode("/", $ruta)));

// Ruta: /docs  -> muestra la documentación Swagger
if (isset($partes[0]) && $partes[0] === "docs") {
    header("Content-Type: text/html");
    include "docs.php";
    exit;
}

// Ruta: /openapi.json -> sirve el archivo de especificación
if (isset($partes[0]) && $partes[0] === "openapi.json") {
    header("Content-Type: application/json");
    readfile("openapi.json");
    exit;
}

// Ruta: /  -> mensaje de bienvenida
if (count($partes) === 0) {
    echo json_encode(["mensaje" => "Microservicio de inventario - PHP - Tienda Puerto"]);
    exit;
}

// A partir de aquí, todas las rutas son /productos y /productos/{id}
if ($partes[0] !== "productos") {
    http_response_code(404);
    echo json_encode(["detail" => "Ruta no encontrada"]);
    exit;
}

$id = $partes[1] ?? null;

// GET /productos  -> listar todos
if ($metodo === "GET" && $id === null) {
    $resultado = listarProductos();
    http_response_code($resultado["codigo"]);
    echo json_encode($resultado["datos"]);
    exit;
}

// GET /productos/{id}  -> obtener uno
if ($metodo === "GET" && $id !== null) {
    $resultado = obtenerProducto($id);
    if (empty($resultado["datos"])) {
        http_response_code(404);
        echo json_encode(["detail" => "Producto no encontrado"]);
        exit;
    }
    echo json_encode($resultado["datos"][0]);
    exit;
}

// POST /productos  -> crear
if ($metodo === "POST" && $id === null) {
    $body = json_decode(file_get_contents("php://input"), true);
    $resultado = crearProducto($body);
    http_response_code(201);
    echo json_encode($resultado["datos"][0] ?? $resultado["datos"]);
    exit;
}

// PUT /productos/{id}  -> actualizar
if ($metodo === "PUT" && $id !== null) {
    $existe = obtenerProducto($id);
    if (empty($existe["datos"])) {
        http_response_code(404);
        echo json_encode(["detail" => "Producto no encontrado"]);
        exit;
    }
    $body = json_decode(file_get_contents("php://input"), true);
    $resultado = actualizarProducto($id, $body);
    echo json_encode($resultado["datos"][0]);
    exit;
}

// DELETE /productos/{id}  -> eliminar
if ($metodo === "DELETE" && $id !== null) {
    $existe = obtenerProducto($id);
    if (empty($existe["datos"])) {
        http_response_code(404);
        echo json_encode(["detail" => "Producto no encontrado"]);
        exit;
    }
    eliminarProducto($id);
    echo json_encode(["mensaje" => "Producto eliminado correctamente"]);
    exit;
}

http_response_code(405);
echo json_encode(["detail" => "Método no permitido"]);