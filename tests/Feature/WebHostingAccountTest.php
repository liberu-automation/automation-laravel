<?php

namespace Tests\Feature;

use App\Models\WebHostingAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WebHostingAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_is_encrypted_at_rest(): void
    {
        $account = WebHostingAccount::factory()->create(['password' => 'plain-secret']);

        $raw = DB::table('web_hosting_accounts')->where('id', $account->id)->value('password');

        $this->assertNotSame('plain-secret', $raw);
        $this->assertNotEmpty($raw);
    }

    public function test_password_decrypts_back_to_plaintext(): void
    {
        $account = WebHostingAccount::factory()->create(['password' => 'plain-secret']);

        $this->assertSame('plain-secret', $account->fresh()->password);
    }

    public function test_password_is_hidden_from_array(): void
    {
        $account = WebHostingAccount::factory()->create(['password' => 'plain-secret']);

        $this->assertArrayNotHasKey('password', $account->toArray());
    }
}
