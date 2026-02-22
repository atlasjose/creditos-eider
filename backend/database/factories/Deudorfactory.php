<?php

namespace Database\Factories;

use App\Models\Deudor;
use App\Models\RutaCobro;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Deudor>
 */
class DeudorFactory extends Factory
{
    protected $model = Deudor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codigo_deudor' => 'DEU-' . $this->faker->unique()->numerify('######'),
            'cedula' => $this->faker->unique()->numerify('##########'),
            'nombres' => $this->faker->firstName(),
            'apellidos' => $this->faker->lastName(),
            'direccion' => $this->faker->streetAddress() . ', Bogotá',
            'latitud' => $this->coordenadasBogota()['lat'],
            'longitud' => $this->coordenadasBogota()['lng'],
            'telefono_principal' => $this->faker->numerify('3#########'),
            'telefono_secundario' => $this->faker->optional(0.3)->numerify('3#########'),
            'id_ruta' => RutaCobro::inRandomOrder()->first()->id_ruta ?? 1,
            'dia_cobro_preferido' => $this->faker->numberBetween(1, 7),
            'recordatorio_diario' => $this->faker->boolean(80),
            'score_credito' => $this->faker->numberBetween(50, 100),
            'id_creado_por' => Usuario::where('id_rol', 1)->first()->id_usuario ?? 1,
        ];
    }

    /**
     * Generar coordenadas GPS dentro de Bogotá.
     */
    private function coordenadasBogota(): array
    {
        // Coordenadas de Bogotá: aproximadamente
        // Lat: 4.5 a 4.8
        // Lng: -74.2 a -74.0
        return [
            'lat' => $this->faker->randomFloat(6, 4.5, 4.8),
            'lng' => $this->faker->randomFloat(6, -74.2, -74.0),
        ];
    }

    /**
     * Deudor sin coordenadas GPS.
     */
    public function sinGPS(): static
    {
        return $this->state(fn (array $attributes) => [
            'latitud' => null,
            'longitud' => null,
        ]);
    }

    /**
     * Deudor con score bajo.
     */
    public function scoreBajo(): static
    {
        return $this->state(fn (array $attributes) => [
            'score_credito' => $this->faker->numberBetween(30, 60),
        ]);
    }

    /**
     * Deudor con score alto.
     */
    public function scoreAlto(): static
    {
        return $this->state(fn (array $attributes) => [
            'score_credito' => $this->faker->numberBetween(90, 100),
        ]);
    }

    /**
     * Deudor en ruta específica.
     */
    public function enRuta(int $idRuta): static
    {
        return $this->state(fn (array $attributes) => [
            'id_ruta' => $idRuta,
        ]);
    }

    /**
     * Deudor con día de cobro específico.
     */
    public function diaCobro(int $dia): static
    {
        return $this->state(fn (array $attributes) => [
            'dia_cobro_preferido' => $dia,
        ]);
    }
}