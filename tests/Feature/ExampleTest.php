<?php

it('redirects the homepage for guests', function () {
    $response = $this->get('/');

    $response->assertStatus(302);
});
