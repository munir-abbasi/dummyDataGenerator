<?php
/**
 * @file tests/Stubs/OJSStubs.php
 *
 * Minimal stubs for OJS framework classes when running standalone unit tests.
 */

declare(strict_types=1);

namespace PKP\core {
    if (!class_exists(Core::class, false)) {
        class Core
        {
            public static function getCurrentDate(?int $ts = null): string
            {
                return date('Y-m-d H:i:s', $ts ?? time());
            }
        }
    }
}

namespace PKP\plugins {
    if (!class_exists(Plugin::class, false)) {
        class Plugin
        {
            public function getName(): string
            {
                return 'dummyDataGenerator';
            }

            public function getSetting($contextId, $name): mixed
            {
                return null;
            }

            public function updateSetting($contextId, $name, $value, $type = null): void
            {
            }
        }
    }

    if (!class_exists(GenericPlugin::class, false)) {
        class GenericPlugin extends Plugin
        {
        }
    }
}

namespace PKP\context {
    if (!class_exists(Context::class, false)) {
        class Context
        {
            protected int $id = 1;

            public function getId(): int
            {
                return $this->id;
            }
        }
    }
}

namespace APP\core {
    if (!class_exists(Application::class, false)) {
        class Application
        {
            private static ?Application $instance = null;
            private mixed $request = null;

            public static function get(): self
            {
                if (self::$instance === null) {
                    self::$instance = new self();
                }
                return self::$instance;
            }

            public function getRequest(): mixed
            {
                return $this->request;
            }

            public function setRequest(mixed $request): void
            {
                $this->request = $request;
            }
        }
    }
}

namespace {
    if (!function_exists('__')) {
        function __(string $key, array $params = []): string
        {
            return $key;
        }
    }
}

namespace Illuminate\Http {
    if (!class_exists(JsonResponse::class, false)) {
        class JsonResponse
        {
            protected mixed $data;
            protected int $status;
            protected array $headers;

            public function __construct(mixed $data = null, int $status = 200, array $headers = [])
            {
                $this->data = $data;
                $this->status = $status;
                $this->headers = $headers;
            }

            public function getStatusCode(): int
            {
                return $this->status;
            }

            public function getData(bool $assoc = false): mixed
            {
                if (is_array($this->data)) {
                    return $this->data;
                }
                return json_decode(json_encode($this->data), $assoc);
            }
        }
    }

    if (!class_exists(Request::class, false)) {
        class Request
        {
            protected array $input = [];

            public function input(?string $key = null, mixed $default = null): mixed
            {
                if ($key === null) {
                    return $this->input;
                }
                return $this->input[$key] ?? $default;
            }

            public function setInput(string $key, mixed $value): void
            {
                $this->input[$key] = $value;
            }
        }
    }
}
