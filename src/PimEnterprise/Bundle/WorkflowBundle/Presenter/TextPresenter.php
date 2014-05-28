<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PimEnterprise\Bundle\WorkflowBundle\Diff\Factory\DiffFactory;

/**
 * Present text data
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class TextPresenter extends AbstractPresenter
{
    /**
     * {@inheritdoc}
     */
    public function supportsChange(array $change)
    {
        return array_key_exists('text', $change);
    }

    /**
     * {@inheritdoc}
     */
    public function present($data, array $change)
    {
        return $this
            ->factory
            ->create(
                $this->explodeText($data),
                $this->explodeText($change['text'])
            )
            ->render($this->renderer);
    }

    protected function explodeText($text)
    {
        preg_match_all('/<p>(.*?)<\/p>/', $text, $matches);

        return !empty($matches[0]) ? $matches[0] : [$text];
    }
}
