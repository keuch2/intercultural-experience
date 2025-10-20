<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_bank_info_is_encrypted_when_stored()
    {
        $user = User::factory()->create();
        
        $bankInfo = [
            'bank_name' => 'Test Bank',
            'account_number' => '1234567890',
            'routing_number' => '123456789'
        ];
        
        $user->bank_info = $bankInfo;
        $user->save();
        
        // Check that the raw database value is encrypted (not readable)
        $rawValue = \DB::table('users')->where('id', $user->id)->value('bank_info');
        $this->assertNotEquals(json_encode($bankInfo), $rawValue);
        
        // Check that the model accessor returns decrypted data
        $user->refresh();
        $this->assertEquals($bankInfo, $user->bank_info);
    }

    public function test_bank_info_handles_null_values()
    {
        $user = User::factory()->create();
        
        $user->bank_info = null;
        $user->save();
        
        $user->refresh();
        $this->assertNull($user->bank_info);
    }

    public function test_bank_info_handles_decryption_errors_gracefully()
    {
        $user = User::factory()->create();
        
        // Manually set an invalid encrypted value
        \DB::table('users')->where('id', $user->id)->update(['bank_info' => 'invalid_encrypted_data']);
        
        $user->refresh();
        
        // Should return null when decryption fails
        $this->assertNull($user->bank_info);
    }

    public function test_user_password_is_properly_hashed()
    {
        $user = User::factory()->create([
            'password' => bcrypt('test_password')
        ]);
        
        $this->assertTrue(\Hash::check('test_password', $user->password));
        $this->assertFalse(\Hash::check('wrong_password', $user->password));
    }

    public function test_user_relationships_are_defined()
    {
        $user = User::factory()->create();
        
        // Test that relationships are properly defined
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->applications());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->points());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->redemptions());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->supportTickets());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->notifications());
    }
}
