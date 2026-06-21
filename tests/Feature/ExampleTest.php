<?php

test('returns a successful response', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
});

test('home page uses dompet patungan product copy', function () {
    $contents = file_get_contents(resource_path('js/pages/Welcome.vue'));

    expect($contents)->toContain('Dompet Patungan');
    expect($contents)->not->toContain("Let's get started");
    expect($contents)->not->toContain('Laravel has an incredibly rich ecosystem');
});
