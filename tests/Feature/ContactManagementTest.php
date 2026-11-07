<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ContactManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_contacts_are_saved_edited_and_deleted_in_the_database(): void
    {
        foreach (['customers', 'suppliers'] as $type) {
            Schema::create($type, function (Blueprint $table) use ($type) {
                $table->id();
                $table->string('name');
                $table->string('email', 254)->nullable();
                $table->string('phone')->nullable();
                $table->string('address')->nullable();
                $table->string('contact_person')->nullable();
                $table->timestamp('created_at')->nullable();
                if ($type === 'customers') {
                    $table->string('class');
                }
            });
            $this->post(route($type.'.store'), [])->assertSessionHasErrors('name');
            $values = ['name' => 'Database Contact', 'email' => 'contact@gmail.com', 'phone' => '01712345678', 'class' => 'Hospital'];
            $this->post(route($type.'.store'), $values)->assertRedirect(route($type.'.index'));
            $id = DB::table($type)->value('id');
            $this->assertDatabaseHas($type, ['id' => $id, 'name' => 'Database Contact', 'email' => 'contact@gmail.com']);
            $this->get(route($type.'.index'))->assertOk()->assertSee('Database Contact');
            $this->get(route($type.'.edit', $id))->assertOk()->assertSee('contact@gmail.com')->assertSee('Update');
            $this->patch(route($type.'.update', $id), array_replace($values, ['email' => 'invalid']))->assertSessionHasErrors('email');
            $this->patch(route($type.'.update', $id), array_replace($values, ['name' => 'Updated Contact']))->assertRedirect(route($type.'.index'));
            $this->assertDatabaseHas($type, ['id' => $id, 'name' => 'Updated Contact']);
            $this->delete(route($type.'.destroy', $id))->assertRedirect(route($type.'.index'));
            $this->assertDatabaseMissing($type, ['id' => $id]);
        }
    }
}
