<?php

function supabaseHeaders() {
    $key = getenv("SUPABASE_KEY");
    return [
        "apikey: $key",
        "Authorization: Bearer $key",
        "Content-Type: application/json",
    ];
}

function tablaUrl() {
    $url = getenv("SUPABASE_URL");
    return "$url/rest/v1/productos";
}

function supabaseRequest($method, $url, $headers, $body = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    $respuesta = curl_exec($ch);

    if ($respuesta === false) {
        $error = curl_error($ch);
        return ["codigo" => 500, "datos" => ["error" => "cURL falló: $error"]];
    }

    $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    return ["codigo" => $codigo, "datos" => json_decode($respuesta, true)];
}

function listarProductos() {
    return supabaseRequest("GET", tablaUrl(), supabaseHeaders());
}

function obtenerProducto($id) {
    $url = tablaUrl() . "?id=eq.$id";
    return supabaseRequest("GET", $url, supabaseHeaders());
}

function crearProducto($datos) {
    $headers = array_merge(supabaseHeaders(), ["Prefer: return=representation"]);
    return supabaseRequest("POST", tablaUrl(), $headers, $datos);
}

function actualizarProducto($id, $datos) {
    $url = tablaUrl() . "?id=eq.$id";
    $headers = array_merge(supabaseHeaders(), ["Prefer: return=representation"]);
    return supabaseRequest("PATCH", $url, $headers, $datos);
}

function eliminarProducto($id) {
    $url = tablaUrl() . "?id=eq.$id";
    return supabaseRequest("DELETE", $url, supabaseHeaders());
}