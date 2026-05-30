<?php

use App\Services\EmailService;
use CodeIgniter\Test\CIUnitTestCase;
use Resend\Exceptions\ErrorException as ResendErrorException;

/**
 * Regression test that no Resend transport error (API key invalid, domain
 * unverified, internal HTTP body, etc.) is propagated to callers as an
 * exception message. Callers in this codebase tend to bubble exception
 * messages into HTTP responses, which is how the original SMTP debugger
 * leak reached the frontend.
 *
 * @internal
 */
final class EmailServiceResendLeakTest extends CIUnitTestCase
{
    public function testResendApiErrorReturnsFalseAndDoesNotEscapeAsException(): void
    {
        $service = new class () extends EmailService {
            public function __construct()
            {
                // Skip parent constructor — no real Resend client needed.
            }

            protected function dispatch(array $payload): void
            {
                throw new ResendErrorException([
                    'message'   => 'API key is invalid: re_X4U94qTR_4ccJve4TpvvBDYPQPhQAWea5',
                    'name'      => 'validation_error',
                    'statusCode' => 401,
                ]);
            }
        };

        $thrown = null;
        try {
            $result = $service->sendRawEmail(
                'user@example.com',
                'Subject',
                '<p>body</p>',
                'no-reply@example.com'
            );
        } catch (\Throwable $e) {
            $thrown = $e;
            $result = null;
        }

        $this->assertNull($thrown, 'sendRawEmail must swallow Resend errors, not rethrow them');
        $this->assertFalse($result, 'sendRawEmail must return false on transport error');
    }

    public function testGenericTransportFailureReturnsFalseAndDoesNotEscapeAsException(): void
    {
        $service = new class () extends EmailService {
            public function __construct()
            {
                // Skip parent constructor — no real Resend client needed.
            }

            protected function dispatch(array $payload): void
            {
                throw new \RuntimeException(
                    'Curl error 28: connect timeout to api.resend.com — internal-server-1.example.local'
                );
            }
        };

        $thrown = null;
        try {
            $result = $service->sendRawEmail(
                'user@example.com',
                'Subject',
                '<p>body</p>',
                'no-reply@example.com'
            );
        } catch (\Throwable $e) {
            $thrown = $e;
            $result = null;
        }

        $this->assertNull($thrown, 'sendRawEmail must swallow transport errors, not rethrow them');
        $this->assertFalse($result, 'sendRawEmail must return false on transport error');
    }
}
