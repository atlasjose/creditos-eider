<?php

namespace Database\Factories;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Producto>
 */
class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categorias = [
            'Electrodomésticos' => [
                'Lavadora', 'Nevera', 'Estufa', 'Microondas', 'Licuadora',
                'Plancha', 'Aspiradora', 'Ventilador', 'Horno Eléctrico'
            ],
            'Electrónica' => [
                'Celular', 'Tablet', 'Laptop', 'TV', 'Parlante Bluetooth',
                'Audífonos', 'Cámara Digital', 'Smartwatch', 'Consola de Videojuegos'
            ],
            'Muebles' => [
                'Sofá', 'Comedor', 'Cama', 'Closet', 'Mesa de Centro',
                'Escritorio', 'Silla Ergonómica', 'Librero'
            ],
        ];

        $categoria = $this->faker->randomElement(array_keys($categorias));
        $nombreBase = $this->faker->randomElement($categorias[$categoria]);
        $marca = $this->faker->randomElement(['Samsung', 'LG', 'Haceb', 'Lenovo', 'Xiaomi', 'Sony', 'Philips', 'Oster']);

        return [
            'codigo_producto' => 'PROD-' . $this->faker->unique()->numerify('####'),
            'nombre_producto' => $nombreBase . ' ' . $marca,
            'descripcion' => $this->faker->sentence(8),
            'precio_venta' => $this->faker->randomElement([
                450000, 650000, 850000, 1200000, 1500000, 
                1800000, 2200000, 2500000, 3000000
            ]),
            'activo' => true,
        ];
    }

    /**
     * Producto inactivo.
     */
    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }

    /**
     * Producto de bajo precio.
     */
    public function economico(): static
    {
        return $this->state(fn (array $attributes) => [
            'precio_venta' => $this->faker->numberBetween(200000, 600000),
        ]);
    }

    /**
     * Producto de alto precio.
     */
    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'precio_venta' => $this->faker->numberBetween(3000000, 6000000),
        ]);
    }
}