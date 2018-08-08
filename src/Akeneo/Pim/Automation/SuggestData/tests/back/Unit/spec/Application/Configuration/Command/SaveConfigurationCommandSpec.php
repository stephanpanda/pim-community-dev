<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\Pim\Automation\SuggestData\Application\Configuration\Command;

use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Command\SaveConfigurationCommand;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class SaveConfigurationCommandSpec extends ObjectBehavior
{
    public function it_is_a_save_configuration_command()
    {
        $this->beConstructedWith('foobar', ['foo' => 'bar']);

        $this->shouldHaveType(SaveConfigurationCommand::class);
    }

    public function it_returns_a_configuration_code()
    {
        $this->beConstructedWith('foobar', ['foo' => 'bar']);

        $this->getCode()->shouldReturn('foobar');
    }

    public function it_returns_a_values()
    {
        $this->beConstructedWith('foobar', ['foo' => 'bar']);

        $this->getValues()->shouldReturn(['foo' => 'bar']);
    }

    public function it_throws_an_exception_during_instantiation_if_code_is_emtpy()
    {
        $this->beConstructedWith('', ['value' => 'value']);

        $this
            ->shouldThrow(new \InvalidArgumentException('Configuration code cannot be empty.'))
            ->duringInstantiation();
    }

    public function it_throws_an_exception_during_instantiation_if_values_are_emtpy()
    {
        $this->beConstructedWith('foobar', []);

        $this
            ->shouldThrow(new \InvalidArgumentException('Configuration values cannot be empty.'))
            ->duringInstantiation();
    }

    public function it_throws_an_exception_during_instantiation_if_configuration_value_key_is_not_a_string()
    {
        $this->beConstructedWith('foobar', [42 => 'value']);

        $this
            ->shouldThrow(new \InvalidArgumentException(
                'The key of a configuration value must be a string, "integer" given.'
            ))
            ->duringInstantiation();
    }

    public function it_throws_an_exception_during_instantiation_if_configuration_value_value_is_not_a_string()
    {
        $this->beConstructedWith('foobar', ['value' => 42]);

        $this
            ->shouldThrow(new \InvalidArgumentException(
                'The value of a configuration value must be a string, "integer" given.'
            ))
            ->duringInstantiation();
    }

    public function it_throws_an_exception_during_instantiation_if_configuration_value_value_is_empty()
    {
        $this->beConstructedWith('foobar', ['value' => '']);

        $this
            ->shouldThrow(new \InvalidArgumentException('The value of a configuration value cannot be empty.'))
            ->duringInstantiation();
    }
}