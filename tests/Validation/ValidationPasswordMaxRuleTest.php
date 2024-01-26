<?php

namespace Illuminate\Tests\Validation;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationServiceProvider;
use Illuminate\Validation\Validator;
use PHPUnit\Framework\TestCase;

class ValidationPasswordMaxRuleTest extends TestCase
{
    public function testPasswordExceedsMaxLength()
    {
        $rules = [
            'password' => Password::default(),
        ];

        $translator = resolve('translator');
        $translator->addLines(['validation.max.string' => 'The :attribute field must be at least :min characters.'], 'en');

        $validator = new Validator(
            $translator,
            ['password' => str_repeat('a', 6)],
            $rules,
            ['max.string' => 'The :attribute field must not be greater than :max characters.']
        );

        self::assertTrue($validator->fails());
        $message = $validator->messages()->first();
        self::assertNotEquals('validation.max.string', $message);
        self::assertEquals('The password field must not be greater than 6 characters.', $message);
    }

    protected function setUp(): void
    {
        $container = Container::getInstance();

        $container->bind('translator', function () {
            return new Translator(
                new ArrayLoader, 'en'
            );
        });

        Facade::setFacadeApplication($container);

        (new ValidationServiceProvider($container))->register();

        Password::defaults(
            Password::min(1)
                ->max(5)
        );
    }

    protected function tearDown(): void
    {
        Container::setInstance(null);

        Facade::clearResolvedInstances();

        Facade::setFacadeApplication(null);
    }
}
