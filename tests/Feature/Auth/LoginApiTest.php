<?php

use App\Models\User;

test('user can login with valid email and password via api', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('12345678'),
    ]);
    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => '12345678',
    ]);

    $response->assertStatus(200);
});
