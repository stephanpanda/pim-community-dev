<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$builder = new RuleBuilder();

$rules = [
    $builder->only([
        'Akeneo\Tool\Component',
        'Webmozart\Assert\Assert',
        'Symfony\Component\EventDispatcher\Event'
    ])->in('Akeneo\ReferenceEntity\Domain'),
    $builder->only([
        'Akeneo\ReferenceEntity\Domain',
        'Akeneo\Tool\Component',
        'Doctrine\Common',
        'Symfony\Component\EventDispatcher\EventSubscriberInterface',
    ])->in('Akeneo\ReferenceEntity\Application'),
    $builder->only([
        'Akeneo\ReferenceEntity\Application',
        'Akeneo\ReferenceEntity\Domain',
        'Akeneo\Tool\Component',
        'Akeneo\Tool\Bundle\ElasticsearchBundle',
        'Doctrine\DBAL',
        'Oro\Bundle\SecurityBundle\SecurityFacade',
        'Akeneo\Platform\Bundle\InstallerBundle',
        'Ramsey\Uuid\Uuid',
        'Symfony',
        'Webmozart\Assert\Assert',
        'JsonSchema\Validator',
        'PDO',

        // TODO: reference entities should not depend on PIM
        'Akeneo\Pim\Enrichment\ReferenceEntity\Component',
    ])->in('Akeneo\ReferenceEntity\Infrastructure'),
];

$config = new Configuration($rules, $finder);

return $config;
