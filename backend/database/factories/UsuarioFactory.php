<?php

namespace Database\Factories;

use App\Models\Usuario;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Usuario>
 */
class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cedula' => $this->faker->unique()->numerify('##########'),
            'nombres' => $this->faker->firstName(),
            'apellidos' => $this->faker->lastName(),
            'telefono' => $this->faker->numerify('3#########'),
            'email' => $this->faker->unique()->safeEmail(),
            'clave_hash' => Hash::make('password123'), // Password por defecto
            'id_rol' => Role::inRandomOrder()->first()->id_rol ?? 2, // Rol aleatorio o COBRADOR
            'id_jefe_asociado' => null, // Se puede asignar después
            'porcentaje_comision' => $this->faker->randomFloat(2, 5, 15),
            'activo' => true,
        ];
    }

    /**
     * Indica que el usuario debe ser JEFE.
     */
    public function jefe(): static
    {
        return $this->state(fn (array $attributes) => [
            'id_rol' => Role::where('nombre_rol', 'JEFE')->first()->id_rol,
            'porcentaje_comision' => 0.00,
            'id_jefe_asociado' => null,
        ]);
    }

    /**
     * Indica que el usuario debe ser COBRADOR.
     */
    public function cobrador(): static
    {
        return $this->state(fn (array $attributes) => [
            'id_rol' => Role::where('nombre_rol', 'COBRADOR')->first()->id_rol,
            'porcentaje_comision' => $this->faker->randomFloat(2, 8, 12),
        ]);
    }

    /**
     * Indica que el usuario debe ser VENDEDOR.
     */
    public function vendedor(): static
    {
        return $this->state(fn (array $attributes) => [
            'id_rol' => Role::where('nombre_rol', 'VENDEDOR')->first()->id_rol,
            'porcentaje_comision' => 0.00,
        ]);
    }

    /**
     * Indica que el usuario debe estar inactivo.
     */
    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }

    /**
     * Con jefe asociado específico.
     */
    public function conJefe(int $idJefe): static
    {
        return $this->state(fn (array $attributes) => [
            'id_jefe_asociado' => $idJefe,
        ]);
    }
}