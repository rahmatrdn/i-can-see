<?php

test('the application returns a successful response', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('iCanSee')
        ->assertSee('See Beyond Sight, Empower Independence');
});
