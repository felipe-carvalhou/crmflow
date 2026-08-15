<?php

namespace Database\Seeders;

use App\Models\Lead;
use Illuminate\Database\Seeder;

class LeadSeeder extends Seeder
{
    /**
     * Cria leads fake pra visualizar o Kanban populado durante o desenvolvimento.
     * Pode ser deletado/reexecutado à vontade: `sail artisan migrate:fresh --seed`.
     */
    public function run(): void
    {
        Lead::factory(10)->create()->each(function (Lead $lead) {
            if ($lead->status !== 'novo' && random_int(0, 1) === 1) {
                $lead->notes()->create([
                    'texto' => fake()->randomElement([
                        'Cliente pediu pra ligar de volta amanhã à tarde.',
                        'Enviei o link da demonstração pelo WhatsApp.',
                        'Perguntou sobre o plano anual com desconto.',
                        'Ficou de conversar com o sócio e retornar.',
                        'Muito interessado, quer fechar ainda essa semana.',
                    ]),
                ]);
            }
        });
    }
}
