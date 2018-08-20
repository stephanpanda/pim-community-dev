<?php

namespace spec\Akeneo\Asset\Component\Factory;

use PhpSpec\ObjectBehavior;

class TagFactorySpec extends ObjectBehavior
{
    const TAG_CLASS = 'Akeneo\Asset\Component\Model\Tag';

    function let()
    {
        $this->beConstructedWith(self::TAG_CLASS);
    }

    function it_can_be_initialized()
    {
        $this->shouldHaveType('Akeneo\Asset\Component\Factory\TagFactory');
    }

    function it_creates_a_tag()
    {
        $this->create()->shouldReturnAnInstanceOf(self::TAG_CLASS);
    }
}