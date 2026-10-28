<?php

namespace App\Helpers;

class PermissionMatrix
{
    public static function roles(): array
    {
        return [
            ['key' => 'admin', 'name' => 'Administrador'],
            ['key' => 'seller', 'name' => 'Vendedor'],
            ['key' => 'manager', 'name' => 'Gestor'],
        ];
    }

    public static function groups(): array
    {
        return [
            [
                'title' => 'Eventos',
                'items' => [
                    ['label' => 'Ver listado de eventos', 'admin' => true, 'seller' => false, 'manager' => false],
                    ['label' => 'Crear eventos', 'admin' => true, 'seller' => false, 'manager' => false],
                    ['label' => 'Editar eventos', 'admin' => true, 'seller' => false, 'manager' => false],
                    ['label' => 'Eliminar eventos', 'admin' => true, 'seller' => false, 'manager' => false],
                    ['label' => 'Gestionar tipos y precios de boleto', 'admin' => true, 'seller' => false, 'manager' => false],
                ],
            ],
            [
                'title' => 'Boletos',
                'items' => [
                    ['label' => 'Ver todos los boletos', 'admin' => true, 'seller' => true, 'manager' => false],
                    ['label' => 'Ver boleto propio', 'admin' => true, 'seller' => true, 'manager' => false],
                    ['label' => 'Registrar venta de boleto', 'admin' => true, 'seller' => true, 'manager' => false],
                    ['label' => 'Editar boleto', 'admin' => true, 'seller' => true, 'manager' => false],
                    ['label' => 'Enviar boleto por correo', 'admin' => true, 'seller' => true, 'manager' => false],
                    ['label' => 'Cancelar boleto', 'admin' => true, 'seller' => false, 'manager' => false],
                    ['label' => 'Ver clientes cancelados', 'admin' => true, 'seller' => false, 'manager' => false],
                    ['label' => 'Eliminar boleto', 'admin' => false, 'seller' => false, 'manager' => false],
                ],
            ],
            [
                'title' => 'Usuarios',
                'items' => [
                    ['label' => 'Ver listado de usuarios', 'admin' => true, 'seller' => false, 'manager' => false],
                    ['label' => 'Crear usuarios', 'admin' => true, 'seller' => false, 'manager' => false],
                    ['label' => 'Editar usuarios', 'admin' => true, 'seller' => false, 'manager' => false],
                    ['label' => 'Eliminar usuarios', 'admin' => false, 'seller' => false, 'manager' => false],
                ],
            ],
            [
                'title' => 'Gafetes',
                'items' => [
                    ['label' => 'Imprimir gafetes', 'admin' => true, 'seller' => true, 'manager' => false],
                ],
            ],
            [
                'title' => 'Check-in',
                'items' => [
                    ['label' => 'Escanear código QR y registrar entrada', 'admin' => true, 'seller' => false, 'manager' => true],
                    ['label' => 'Ver bitácora de escaneos', 'admin' => true, 'seller' => false, 'manager' => true],
                ],
            ],
            [
                'title' => 'Dashboard y perfil',
                'items' => [
                    ['label' => 'Ver dashboard', 'admin' => true, 'seller' => true, 'manager' => true],
                    ['label' => 'Búsqueda global', 'admin' => true, 'seller' => true, 'manager' => true],
                    ['label' => 'Editar perfil propio', 'admin' => true, 'seller' => true, 'manager' => true],
                ],
            ],
        ];
    }
}
