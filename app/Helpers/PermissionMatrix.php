<?php

namespace App\Helpers;

class PermissionMatrix
{
    public static function roles(): array
    {
        return [
            ['key' => 'admin', 'name' => 'Administrador'],
            ['key' => 'manager', 'name' => 'Gestor'],
        ];
    }

    public static function groups(): array
    {
        return [
            [
                'title' => 'Eventos',
                'items' => [
                    ['label' => 'Ver listado de eventos', 'admin' => true, 'manager' => false],
                    ['label' => 'Crear eventos', 'admin' => true, 'manager' => false],
                    ['label' => 'Editar eventos', 'admin' => true, 'manager' => false],
                    ['label' => 'Eliminar eventos', 'admin' => true, 'manager' => false],
                    ['label' => 'Gestionar tipos y precios de boleto', 'admin' => true, 'manager' => false],
                ],
            ],
            [
                'title' => 'Boletos',
                'items' => [
                    ['label' => 'Ver todos los boletos', 'admin' => true, 'manager' => false],
                    ['label' => 'Ver boleto propio', 'admin' => true, 'manager' => false],
                    ['label' => 'Registrar venta de boleto', 'admin' => true, 'manager' => false],
                    ['label' => 'Editar boleto', 'admin' => true, 'manager' => false],
                    ['label' => 'Enviar boleto por correo', 'admin' => true, 'manager' => false],
                    ['label' => 'Cancelar boleto', 'admin' => true, 'manager' => false],
                    ['label' => 'Ver clientes cancelados', 'admin' => true, 'manager' => false],
                    ['label' => 'Eliminar boletos y clientes con boleto asociado', 'admin' => true, 'manager' => true],
                ],
            ],
            [
                'title' => 'Usuarios',
                'items' => [
                    ['label' => 'Ver listado de usuarios', 'admin' => true, 'manager' => false],
                    ['label' => 'Crear usuarios', 'admin' => true, 'manager' => false],
                    ['label' => 'Editar usuarios', 'admin' => true, 'manager' => false],
                    ['label' => 'Eliminar usuarios sin ventas ni check-in', 'admin' => true, 'manager' => true],
                ],
            ],
            [
                'title' => 'Gafetes',
                'items' => [
                    ['label' => 'Imprimir gafetes', 'admin' => true, 'manager' => false],
                ],
            ],
            [
                'title' => 'Check-in',
                'items' => [
                    ['label' => 'Escanear código QR y registrar entrada', 'admin' => true, 'manager' => true],
                    ['label' => 'Ver bitácora de escaneos', 'admin' => true, 'manager' => true],
                ],
            ],
            [
                'title' => 'Dashboard y perfil',
                'items' => [
                    ['label' => 'Ver dashboard', 'admin' => true, 'manager' => true],
                    ['label' => 'Búsqueda global', 'admin' => true, 'manager' => true],
                    ['label' => 'Editar perfil propio', 'admin' => true, 'manager' => true],
                ],
            ],
        ];
    }
}
