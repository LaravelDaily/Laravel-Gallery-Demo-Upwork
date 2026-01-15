<?php

use App\Http\Middleware\AddCacheHeaders;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

test('adds cache headers to storage requests', function () {
    $middleware = new AddCacheHeaders;

    $request = Request::create('/storage/test.jpg', 'GET');
    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->headers->get('Cache-Control'))
        ->toContain('public')
        ->toContain('max-age=31536000')
        ->toContain('immutable');
});

test('adds cache headers to build requests', function () {
    $middleware = new AddCacheHeaders;

    $request = Request::create('/build/app.js', 'GET');
    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->headers->get('Cache-Control'))
        ->toContain('public')
        ->toContain('max-age=31536000')
        ->toContain('immutable');
});

test('adds cache headers to CSS files', function () {
    $middleware = new AddCacheHeaders;

    $request = Request::create('/styles.css', 'GET');
    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->headers->get('Cache-Control'))
        ->toContain('public')
        ->toContain('max-age=31536000')
        ->toContain('immutable');
});

test('adds cache headers to JS files', function () {
    $middleware = new AddCacheHeaders;

    $request = Request::create('/app.js', 'GET');
    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->headers->get('Cache-Control'))
        ->toContain('public')
        ->toContain('max-age=31536000')
        ->toContain('immutable');
});

test('adds cache headers to WebP images', function () {
    $middleware = new AddCacheHeaders;

    $request = Request::create('/image.webp', 'GET');
    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->headers->get('Cache-Control'))
        ->toContain('public')
        ->toContain('max-age=31536000')
        ->toContain('immutable');
});

test('adds cache headers to JPEG images', function () {
    $middleware = new AddCacheHeaders;

    $request = Request::create('/image.jpg', 'GET');
    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->headers->get('Cache-Control'))
        ->toContain('public')
        ->toContain('max-age=31536000')
        ->toContain('immutable');
});

test('adds cache headers to jpeg extension images', function () {
    $middleware = new AddCacheHeaders;

    $request = Request::create('/photo.jpeg', 'GET');
    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->headers->get('Cache-Control'))
        ->toContain('public')
        ->toContain('max-age=31536000')
        ->toContain('immutable');
});

test('adds cache headers to PNG images', function () {
    $middleware = new AddCacheHeaders;

    $request = Request::create('/image.png', 'GET');
    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->headers->get('Cache-Control'))
        ->toContain('public')
        ->toContain('max-age=31536000')
        ->toContain('immutable');
});

test('adds cache headers to SVG images', function () {
    $middleware = new AddCacheHeaders;

    $request = Request::create('/icon.svg', 'GET');
    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->headers->get('Cache-Control'))
        ->toContain('public')
        ->toContain('max-age=31536000')
        ->toContain('immutable');
});

test('cache header has correct max-age value', function () {
    $middleware = new AddCacheHeaders;

    $request = Request::create('/storage/image.jpg', 'GET');
    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->headers->get('Cache-Control'))->toContain('max-age=31536000');
});

test('cache header is immutable', function () {
    $middleware = new AddCacheHeaders;

    $request = Request::create('/build/styles.css', 'GET');
    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->headers->get('Cache-Control'))->toContain('immutable');
});

test('does not add cache headers to HTML pages', function () {
    $middleware = new AddCacheHeaders;

    $request = Request::create('/', 'GET');
    $response = $middleware->handle($request, fn () => new Response('OK'));

    $cacheControl = $response->headers->get('Cache-Control');

    // Should not have immutable or long max-age
    expect($cacheControl)->not->toContain('immutable');
});

test('does not add cache headers to regular routes', function () {
    $middleware = new AddCacheHeaders;

    $request = Request::create('/artworks/test-slug', 'GET');
    $response = $middleware->handle($request, fn () => new Response('OK'));

    $cacheControl = $response->headers->get('Cache-Control');

    expect($cacheControl)->not->toContain('immutable');
});

test('does not add cache headers to admin pages', function () {
    $middleware = new AddCacheHeaders;

    $request = Request::create('/admin/artworks', 'GET');
    $response = $middleware->handle($request, fn () => new Response('OK'));

    $cacheControl = $response->headers->get('Cache-Control');

    expect($cacheControl)->not->toContain('immutable');
});

test('adds cache headers to nested storage paths', function () {
    $middleware = new AddCacheHeaders;

    $request = Request::create('/storage/artworks/123/image.webp', 'GET');
    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->headers->get('Cache-Control'))
        ->toContain('public')
        ->toContain('max-age=31536000')
        ->toContain('immutable');
});

test('adds cache headers to nested build paths', function () {
    $middleware = new AddCacheHeaders;

    $request = Request::create('/build/assets/app-abc123.js', 'GET');
    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->headers->get('Cache-Control'))
        ->toContain('public')
        ->toContain('max-age=31536000')
        ->toContain('immutable');
});

test('does not add cache headers to JSON API endpoints', function () {
    $middleware = new AddCacheHeaders;

    $request = Request::create('/api/artworks', 'GET');
    $response = $middleware->handle($request, fn () => new Response('OK'));

    $cacheControl = $response->headers->get('Cache-Control');

    expect($cacheControl)->not->toContain('immutable');
});
