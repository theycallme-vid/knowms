<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_faq_log_access_batch_sync_deduplicates_and_returns_timestamp(): void
    {
        \Illuminate\Support\Facades\Schema::create('tsession', function ($t) {
            $t->id();
            $t->string('kode_fakultas')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        \Illuminate\Support\Facades\Schema::create('tlogmahasiswa', function ($t) {
            $t->id();
            $t->string('jenis_interaksi', 20)->default('view');
            $t->string('keyword')->nullable();
            $t->integer('hasil')->default(1);
            $t->timestamp('waktu_pencarian')->nullable();
            $t->unsignedBigInteger('tSession_id')->nullable();
            $t->string('tFaqs_id', 20)->nullable();
        });

        $res = $this->postJson(route('faq.log-access'), [
            'items' => [
                ['faq_id' => 'FAQ-0001', 'jenis_interaksi' => 'view', 'keyword' => 'krs', 'hasil' => 1],
                ['faq_id' => 'FAQ-0001', 'jenis_interaksi' => 'view', 'keyword' => 'krs', 'hasil' => 1],
                ['faq_id' => 'FAQ-0001', 'jenis_interaksi' => 'like', 'hasil' => 1],
                ['faq_id' => 'FAQ-0001', 'jenis_interaksi' => 'dislike', 'hasil' => 0],
            ],
        ]);

        $res->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('counts_map.FAQ-0001.views', 1)
            ->assertJsonPath('counts_map.FAQ-0001.likes', 0)
            ->assertJsonPath('counts_map.FAQ-0001.dislikes', 1);
        $this->assertMatchesRegularExpression('/^\d{2}:\d{2}:\d{2}$/', $res->json('synced_at'));
    }

    public function test_superadmin_and_validator_daftar_faq_are_strictly_separated_by_role(): void
    {
        $superAdmin = new \App\Models\User([
            'id' => 1,
            'nama' => 'Admin KMS',
            'email' => 'admin@campus.ac.id',
            'tRoles_id' => 1,
            'is_active' => true,
        ]);
        $superAdmin->setRelation('role', new \App\Models\Role(['id' => 1, 'nama' => 'Super Admin']));

        $validator = new \App\Models\User([
            'id' => 2,
            'nama' => 'Validator KMS',
            'email' => 'validator@campus.ac.id',
            'tRoles_id' => 3,
            'is_active' => true,
        ]);
        $validator->setRelation('role', new \App\Models\Role(['id' => 3, 'nama' => 'Validator']));

        $this->actingAs($superAdmin)
            ->get(route('superadmin.daftarFAQ'))
            ->assertOk()
            ->assertSee('Ruang Super Admin')
            ->assertDontSee('Buat FAQ');

        $this->actingAs($superAdmin)
            ->get(route('validator.daftarFAQ'))
            ->assertForbidden();

        $this->actingAs($validator)
            ->get(route('validator.daftarFAQ'))
            ->assertOk()
            ->assertSee('Ruang Validator');

        $this->actingAs($validator)
            ->get(route('superadmin.daftarFAQ'))
            ->assertForbidden();
    }
}
