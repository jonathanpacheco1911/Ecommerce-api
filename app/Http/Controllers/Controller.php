<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="API de E-commerce Segura",
 *     version="1.0.0",
 *     description="API RESTful para la gestión de un e-commerce básico: clientes, catálogo de productos, órdenes de compra y procesamiento de pagos mediante Stripe.",
 *     @OA\Contact(
 *         email="soporte@ecommerce-api.test"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Servidor API"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Token",
 *     description="Autenticación mediante Bearer Token generado por Laravel Sanctum. Se obtiene al iniciar sesión o registrarse."
 * )
 *
 * @OA\Tag(name="Autenticación", description="Registro, login y logout de clientes")
 * @OA\Tag(name="Productos", description="Catálogo de productos (CRUD)")
 * @OA\Tag(name="Órdenes", description="Creación y consulta de órdenes de compra")
 * @OA\Tag(name="Pagos", description="Procesamiento de pagos con Stripe")
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Ha ocurrido un error"),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         nullable=true,
 *         example={"field": {"El campo es obligatorio."}}
 *     )
 * )
 */
abstract class Controller
{
    //
}
