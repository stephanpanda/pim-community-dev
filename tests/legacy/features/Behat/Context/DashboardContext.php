<?php

namespace PimEnterprise\Behat\Context;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\PimContext;

class DashboardContext extends PimContext
{
    use SpinCapableTrait;

    /**
     * @param string $user
     * @param string $product
     *
     * @When /^I click on the proposal to review created by "([^"]+)" on the product "([^"]+)"$/
     */
    public function iClickOnTheProposalToReview($user, $product)
    {
        try {
            $proposalWidget = $this->getElementOnCurrentPage('Proposal widget');

            $proposalWidget->followProposalLink($user, $product);
        } catch (\Exception $e) {
            throw $this->getMainContext()->createExpectationException(
                sprintf('No proposal(s) found for %s user and product %s', $user, $product)
            );
        }
    }

    /**
     * Get the channel completeness ratio inside the completeness widget
     *
     * @param string $channel
     *
     * @return string
     */
    public function getChannelCompleteness($channel)
    {
        $completenessWidget = $this->getElementOnCurrentPage('Completeness Widget');

        return $completenessWidget->getChannelCompleteness($channel);
    }

    /**
     * Get the localized channel completeness ratio inside the completeness widget
     *
     * @param string $channel
     * @param string $locale
     *
     * @return string
     */
    public function getLocalizedChannelCompleteness($channel, $locale)
    {
        $completenessWidget = $this->getElementOnCurrentPage('Completeness Widget');

        return $completenessWidget->getLocalizedChannelCompleteness($channel, $locale);
    }
}