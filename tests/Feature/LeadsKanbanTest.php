<?php

use App\Models\Lead;
use App\Models\LeadNote;
use App\Models\User;

it('exige autenticação pra acessar o kanban de leads', function () {
    $this->get('/admin/leads')->assertRedirect('/login');
});

it('mostra o kanban de leads pro usuário autenticado', function () {
    $user = User::factory()->create();
    Lead::factory()->create(['nome' => 'Cliente Kanban', 'status' => 'novo']);

    $this->actingAs($user)
        ->get('/admin/leads')
        ->assertOk()
        ->assertSee('Cliente Kanban');
});

it('atualiza o status do lead via PATCH (drag-and-drop)', function () {
    $user = User::factory()->create();
    $lead = Lead::factory()->create(['status' => 'novo']);

    $this->actingAs($user)
        ->patchJson("/admin/leads/{$lead->id}/status", ['status' => 'contatado'])
        ->assertOk()
        ->assertJson(['success' => true, 'status' => 'contatado']);

    expect($lead->fresh()->status)->toBe('contatado');
    expect($lead->fresh()->ultimo_contato_em)->not->toBeNull();
});

it('rejeita status inválido', function () {
    $user = User::factory()->create();
    $lead = Lead::factory()->create();

    $this->actingAs($user)
        ->patchJson("/admin/leads/{$lead->id}/status", ['status' => 'inexistente'])
        ->assertStatus(422)
        ->assertJson(['success' => false]);
});

it('adiciona uma nota ao lead', function () {
    $user = User::factory()->create();
    $lead = Lead::factory()->create();

    $this->actingAs($user)
        ->postJson("/admin/leads/{$lead->id}/notes", ['texto' => 'Ligar amanhã'])
        ->assertCreated()
        ->assertJsonPath('note.texto', 'Ligar amanhã');

    expect($lead->notes()->count())->toBe(1);
});

it('atualiza o valor estimado do lead', function () {
    $user = User::factory()->create();
    $lead = Lead::factory()->create();

    $this->actingAs($user)
        ->patchJson("/admin/leads/{$lead->id}/valor", ['valor_estimado' => 297])
        ->assertOk()
        ->assertJson(['success' => true]);

    expect((float) $lead->fresh()->valor_estimado)->toBe(297.0);
});

it('cria um lead manualmente pelo kanban', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/admin/leads', [
            'nome' => 'Lead Manual',
            'telefone' => '(11) 91234-5678',
            'segmento' => 'Academia',
        ])
        ->assertCreated()
        ->assertJson(['success' => true])
        ->assertJsonPath('lead.nome', 'Lead Manual')
        ->assertJsonPath('lead.status', 'novo')
        ->assertJsonPath('lead.origem', 'manual');

    expect(Lead::where('nome', 'Lead Manual')->exists())->toBeTrue();
});

it('permite informar origem e valor estimado ao criar lead manualmente', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/admin/leads', [
            'nome' => 'Lead Manual 2',
            'telefone' => '(11) 91234-5678',
            'segmento' => 'Academia',
            'origem' => 'indicacao',
            'valor_estimado' => 197,
        ])
        ->assertCreated()
        ->assertJsonPath('lead.origem', 'indicacao');

    expect((float) Lead::where('nome', 'Lead Manual 2')->first()->valor_estimado)->toBe(197.0);
});

it('rejeita criação manual de lead com dados inválidos', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/admin/leads', ['telefone' => 'abc'])
        ->assertStatus(422)
        ->assertJson(['success' => false]);
});

it('exige autenticação pra criar lead manualmente', function () {
    $this->postJson('/admin/leads', [
        'nome' => 'X',
        'telefone' => '(11) 91234-5678',
        'segmento' => 'Y',
    ])->assertStatus(401);

    expect(Lead::where('nome', 'X')->exists())->toBeFalse();
});

it('atualiza nome, telefone e segmento do lead', function () {
    $user = User::factory()->create();
    $lead = Lead::factory()->create(['nome' => 'Jao da Silva']);

    $this->actingAs($user)
        ->patchJson("/admin/leads/{$lead->id}", [
            'nome' => 'João da Silva',
            'telefone' => '(21) 98888-7777',
            'segmento' => 'Barbearia',
        ])
        ->assertOk()
        ->assertJson(['success' => true]);

    $lead->refresh();
    expect($lead->nome)->toBe('João da Silva');
    expect($lead->telefone)->toBe('(21) 98888-7777');
    expect($lead->segmento)->toBe('Barbearia');
});

it('rejeita atualização de dados básicos com telefone inválido', function () {
    $user = User::factory()->create();
    $lead = Lead::factory()->create();

    $this->actingAs($user)
        ->patchJson("/admin/leads/{$lead->id}", [
            'nome' => 'Nome Válido',
            'telefone' => 'abc',
            'segmento' => 'Academia',
        ])
        ->assertStatus(422)
        ->assertJson(['success' => false]);
});

it('exclui um lead e suas notas em cascata', function () {
    $user = User::factory()->create();
    $lead = Lead::factory()->create();
    $lead->notes()->create(['texto' => 'Nota qualquer']);

    $this->actingAs($user)
        ->deleteJson("/admin/leads/{$lead->id}")
        ->assertOk()
        ->assertJson(['success' => true]);

    expect(Lead::find($lead->id))->toBeNull();
    expect(LeadNote::where('lead_id', $lead->id)->count())->toBe(0);
});

it('exige autenticação pra editar ou excluir lead', function () {
    $lead = Lead::factory()->create();

    $this->patchJson("/admin/leads/{$lead->id}", ['nome' => 'X', 'telefone' => '11999999999', 'segmento' => 'Y'])
        ->assertStatus(401);

    $this->deleteJson("/admin/leads/{$lead->id}")->assertStatus(401);

    expect(Lead::find($lead->id))->not->toBeNull();
});

it('cria um lead via api pública sem autenticação', function () {
    $this->postJson('/api/leads', [
        'nome' => 'Lead Público',
        'telefone' => '(11) 91234-5678',
        'segmento' => 'Academia',
        'origem' => 'landing-autoflow',
    ])
        ->assertCreated()
        ->assertJson(['success' => true]);

    expect(Lead::where('nome', 'Lead Público')->exists())->toBeTrue();
});

it('retorna 422 com mensagem clara quando faltam campos obrigatórios', function () {
    $this->postJson('/api/leads', ['telefone' => '11999999999'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['nome', 'segmento']);
});

it('aplica rate limit de 5 requisições por minuto na api pública', function () {
    $payload = [
        'nome' => 'Rate Limit',
        'telefone' => '(11) 91234-5678',
        'segmento' => 'Academia',
    ];

    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/leads', $payload)->assertCreated();
    }

    $this->postJson('/api/leads', $payload)->assertStatus(429);
});
