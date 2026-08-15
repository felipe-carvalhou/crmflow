<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ddd = $this->faker->numberBetween(11, 99);
        $telefone = sprintf(
            '(%d) 9%s-%s',
            $ddd,
            $this->faker->numerify('####'),
            $this->faker->numerify('####')
        );

        $criadoEm = $this->faker->dateTimeBetween('-30 days', 'now');

        return [
            'nome' => $this->faker->name(),
            'telefone' => $telefone,
            'segmento' => $this->faker->randomElement([
                'Salão de beleza', 'Clínica odontológica', 'Academia',
                'Loja de roupas', 'Restaurante', 'Escritório de advocacia',
                'Petshop', 'Consultório médico',
            ]),
            'origem' => $this->faker->randomElement([
                'landing-autoflow', 'instagram', 'indicacao', 'google-ads', null,
            ]),
            'status' => $this->faker->randomElement(Lead::STATUSES),
            'valor_estimado' => $this->faker->optional(0.6)->randomElement([97, 197, 297, 497, 997]),
            'observacoes' => $this->faker->optional(0.3)->sentence(),
            'ultimo_contato_em' => $this->faker->optional(0.5)->dateTimeBetween($criadoEm, 'now'),
            'created_at' => $criadoEm,
            'updated_at' => $criadoEm,
        ];
    }
}
