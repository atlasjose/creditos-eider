<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * Este seeder ejecuta todos los seeders en el orden correcto.
     * 
     * Uso:
     * php artisan db:seed
     * php artisan db:seed --class=DatabaseSeeder
     * php artisan migrate:fresh --seed
     */
    public function run(): void
    {
        // Ejecutar seeders en orden de dependencias
        $this->call([
            RoleSeeder::class,           // 1. Roles primero (JEFE, COBRADOR, VENDEDOR)
            UsuarioSeeder::class,        // 2. Usuarios (depende de roles)
            PuebloSeeder::class,         // 3. Pueblos
            RutaSeeder::class,           // 4. Rutas (depende de pueblos y usuarios)
            DeudorSeeder::class,         // 5. Deudores con GPS (depende de rutas y usuarios)
            ProductoSeeder::class,       // 6. Productos
            // VentaSeeder::class,       // 7. Ventas (opcional, descomentar si lo necesitas)
        ]);

        $this->command->info('✅ Base de datos poblada exitosamente!');
        $this->command->info('📊 Resumen:');
        $this->command->info('   - 3 Roles creados');
        $this->command->info('   - 5 Usuarios creados (1 JEFE, 2 COBRADORES, 2 VENDEDORES)');
        $this->command->info('   - 5 Pueblos creados');
        $this->command->info('   - 3 Rutas de cobro creadas');
        $this->command->info('   - 10 Deudores creados (todos con GPS)');
        $this->command->info('   - 10 Tarjetas de crédito creadas');
        $this->command->info('   - 5 Productos creados');
        $this->command->info('');
        $this->command->info('🔐 Usuarios de prueba:');
        $this->command->info('   JEFE:      admin@creditos.com / admin123');
        $this->command->info('   COBRADOR:  cobrador1@creditos.com / cobrador123');
        $this->command->info('   VENDEDOR:  vendedor1@creditos.com / vendedor123');
    }
}