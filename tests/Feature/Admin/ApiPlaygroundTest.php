<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;

function adminUser(): User
{
    $user = new User([
        'id' => 1,
        'name' => '관리자',
        'email' => 'admin@example.com',
        'login_id' => 'admin',
        'password' => 'password',
    ]);

    $user->exists = true;

    return $user;
}

beforeEach(function () {
    $this->withoutVite();
});

it('shows the api playground page to authenticated admins', function () {
    $this->actingAs(adminUser())
        ->get('/admin/api-playground')
        ->assertSuccessful()
        ->assertSee('API 요청 테스트')
        ->assertSee('/slangs');
});

it('proxies a selected api request with the configured api key', function () {
    config(['app.api_key' => 'test-api-key']);

    Http::fake([
        'http://localhost/api/v1/slangs*' => Http::response([
            'data' => [
                ['id' => 1, 'korean' => '억까'],
            ],
        ], 200, [
            'Content-Type' => 'application/json',
        ]),
    ]);

    $this->actingAs(adminUser())
        ->postJson('/admin/api-playground/request', [
            'endpoint_key' => 'slangs.index',
            'query_params' => [
                'per_page' => '10',
                'page' => '1',
            ],
        ])
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('request.method', 'GET')
        ->assertJsonPath('response.status', 200)
        ->assertJsonPath('response.body.data.0.korean', '억까');

    Http::assertSent(function ($request) {
        return $request->method() === 'GET'
            && $request->url() === 'http://localhost/api/v1/slangs?per_page=10&page=1'
            && $request->hasHeader('X-API-Key', 'test-api-key')
            && $request->hasHeader('Accept', 'application/json');
    });
});

it('validates required path parameters before proxying the request', function () {
    $this->actingAs(adminUser())
        ->postJson('/admin/api-playground/request', [
            'endpoint_key' => 'slangs.show',
            'path_params' => [
                'slang' => '',
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['path_params.slang']);
});
