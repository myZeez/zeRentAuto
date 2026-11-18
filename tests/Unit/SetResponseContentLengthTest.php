<?php

namespace Tests\Unit;

use App\Http\Middleware\SetResponseContentLength;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SetResponseContentLengthTest extends TestCase
{
    public function test_length_uses_final_response_bytes_including_multibyte_text(): void
    {
        $response = new Response('Sambutan');
        $response->headers->set('Content-Length', '8');

        $result = (new SetResponseContentLength)->handle(Request::create('/'), function () use ($response): Response {
            $response->setContent('Sambutan zeAUTOrent → perjalanan keluarga');

            return $response;
        });

        $this->assertSame($response, $result);
        $this->assertSame((string) strlen($response->getContent()), $result->headers->get('Content-Length'));
    }

    public function test_head_preserves_the_length_of_the_get_response(): void
    {
        $response = new Response('', headers: ['Content-Length' => '125']);

        $result = (new SetResponseContentLength)->handle(Request::create('/', 'HEAD'), fn (): Response => $response);

        $this->assertSame('125', $result->headers->get('Content-Length'));
    }

    public function test_streams_downloads_empty_statuses_and_encoded_responses_are_not_reframed(): void
    {
        $responses = [
            new StreamedResponse(fn () => print 'stream'),
            new BinaryFileResponse(__FILE__),
            new Response('', 204),
            new Response('', 304),
            new Response('compressed', headers: ['Content-Encoding' => 'gzip']),
            new Response('chunked', headers: ['Transfer-Encoding' => 'chunked']),
        ];

        foreach ($responses as $response) {
            $result = (new SetResponseContentLength)->handle(Request::create('/'), fn (): Response => $response);

            $this->assertSame($response, $result);
            $this->assertFalse($result->headers->has('Content-Length'));
        }
    }
}
