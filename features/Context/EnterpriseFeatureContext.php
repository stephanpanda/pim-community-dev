<?php

namespace Context;

use Behat\Gherkin\Node\TableNode;

/**
 * A context for creating entities
 *
 * @author    Gildas Quéméner <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseFeatureContext extends FeatureContext
{
    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->useContext('fixtures', new EnterpriseFixturesContext());
        $this->useContext('catalogConfiguration', new EnterpriseCatalogConfigurationContext());
        $this->useContext('webUser', new EnterpriseWebUser($parameters['window_width'], $parameters['window_height']));
        $this->useContext('webApi', new WebApiContext($parameters['base_url']));
        $this->useContext('datagrid', new EnterpriseDataGridContext());
        $this->useContext('command', new CommandContext());
        $this->useContext('navigation', new EnterpriseNavigationContext());
        $this->useContext('transformations', new TransformationContext());
        $this->useContext('assertions', new AssertionContext());
    }

    /**
     * @BeforeScenario
     */
    public function registerConfigurationDirectory()
    {
        $this
            ->getSubcontext('catalogConfiguration')
            ->addConfigurationDirectory(__DIR__.'/catalog');
    }

    /**
     * @param string $field
     *
     * @return bool
     * @throws ExpectationException
     * @Then /^I should see that (.*) is a modified value$/
     */
    public function iShouldSeeThatFieldIsAModifiedValue($field)
    {
        $icons = $this->getSubcontext('navigation')->getCurrentPage()->findFieldIcons($field);
        foreach ($icons as $icon) {
            if ($icon->hasClass('icon-file-text-alt')) {
                return true;
            }
        }

        throw $this->createExpectationException('Modified value icon was not found');
    }

    /**
     * @param string $attribute
     *
     * @return bool
     * @throws ExpectationException
     * @Then /^I should see that (.*) is a smart$/
     */
    public function iShouldSeeThatAttributeIsASmart($attribute)
    {
        $icons = $this->getSubcontext('navigation')->getCurrentPage()->findFieldIcons($attribute);
        foreach ($icons as $icon) {
            if ($icon->hasClass('icon-code-fork')) {
                return true;
            }
        }

        throw $this->createExpectationException('Affected by a rule icon was not found');
    }

    /**
     * @param TableNode $table
     *
     * @Given /^I should see the following rule conditions:$/
     */
    public function iShouldSeeTheFollowingRuleConditions(TableNode $table)
    {
        $expectedConditions = $table->getHash();
        $actualConditions = $this->getSession()->getPage()->findAll('css', '.rule-table .rule-condition');

        $expectedCount = count($expectedConditions);
        $actualCount   = count($actualConditions);
        if ($expectedCount !== $actualCount) {
            throw new \Exception(
                sprintf(
                    'Expecting %d rules conditions, actually saw %d',
                    $expectedCount,
                    $actualCount
                )
            );
        }

        foreach ($expectedConditions as $key => $condition) {
            $condition = array_merge(
                [
                    'locale' => null,
                    'scope' => null
                ],
                $condition
            );

            $actualCondition = $actualConditions[$key];

            $this->checkElementValue(
                $actualCondition->find('css', '.condition-field'),
                $condition['field'],
                true,
                true
            );
            $this->checkElementValue(
                $actualCondition->find('css', '.condition-operator'),
                $condition['operator']
            );
            $this->checkElementValue(
                $actualCondition->find('css', '.condition-value'),
                $condition['value'],
                false
            );
            $this->checkElementValue(
                $actualCondition->find('css', '.rule-item-context .locale'),
                $condition['locale'],
                false
            );
            $this->checkElementValue(
                $actualCondition->find('css', '.rule-item-context .scope'),
                $condition['scope'],
                false
            );
        }
    }

    /**
     * @param TableNode $table
     *
     * @Given /^I should see the following rule actions:$/
     */
    public function iShouldSeeTheFollowingRuleActions(TableNode $table)
    {
        $expectedActions = $table->getHash();
        $actualActions = $this->getSession()->getPage()->findAll('css', '.rule-table .rule-action');

        $expectedCount = count($expectedActions);
        $actualCount   = count($actualActions);
        if ($expectedCount !== $actualCount) {
            throw new \Exception(
                sprintf(
                    'Expecting %d rules actions, actually saw %d',
                    $expectedCount,
                    $actualCount
                )
            );
        }

        foreach ($expectedActions as $key => $action) {
            $action = array_merge(
                [
                    'locale' => null,
                    'scope' => null
                ],
                $action
            );

            $actualAction = $actualActions[$key];

            switch (trim($action['type'])) {
                case 'set_value':
                    $action['type'] = 'is set into';

                    $this->checkElementValue(
                        $actualAction->find('css', '.action-field'),
                        $action['field'],
                        true,
                        true
                    );
                    $this->checkElementValue(
                        $actualAction->find('css', '.action-type'),
                        $action['type']
                    );
                    $this->checkElementValue(
                        $actualAction->find('css', '.action-value'),
                        $action['value'],
                        false
                    );
                    $this->checkElementValue(
                        $actualAction->find('css', '.rule-item-context .locale'),
                        $action['locale'],
                        false
                    );
                    $this->checkElementValue(
                        $actualAction->find('css', '.rule-item-context .scope'),
                        $action['scope'],
                        false
                    );

                    break;
                case 'copy_value':
                    $action['type'] = 'is copied into';


                    break;
                default:
                    throw new \Exception(
                        sprintf('The action type %s is not supported yet', $action['type'])
                    );
            }
        }
    }

    /**
     * @param string $code
     *
     * @Given /^I delete the rule "([^"]*)"$/
     */
    public function iDeleteTheRule($code)
    {
        $rules = $this->getSession()->getPage()->findAll('css', '.rule-table .rule-row');

        foreach ($rules as $rule) {
            if (strtolower($rule->find('css', '.rule-code')->getText()) === $code) {
                $rule->find('css', '.delete-row')->click();

                $this->wait();

                return;
            }
        }

        throw new \Exception(sprintf('No rule found with code %s', $code));

    }

    protected function checkElementValue($element, $expectedValue, $mandatory = true, $firstElement = false)
    {
        $element = is_array($element) ? reset($element) : $element;

        if ($mandatory || null !== $element && $expectedValue !== null) {
            $actualValue = $firstElement ? explode('<', trim($element->getHTML()))[0] : $element->getText();

            if ($actualValue !== $expectedValue) {
                throw new \Exception(
                    sprintf(
                        'Rule element is expected to be "%s", actually is "%s"',
                        $expectedValue,
                        $actualValue
                    )
                );
            }
        }
    }

    /**
     * @param string $field
     * @param string $userGroups
     *
     * @throws ExpectationException
     * @Then /^I should see the permission (.*) with user groups (.*)$/
     */
    public function iShouldSeeThePermissionFieldWithRoles($field, $userGroups)
    {
        try {
            $element = $this->getSubcontext('navigation')->getCurrentPage()->findField($field);
            if (!$element) {
                throw $this->createExpectationException(sprintf('Expecting to see field "%s".', $field));
            }
        } catch (ElementNotFoundException $e) {
            throw $this->createExpectationException(sprintf('Expecting to see field "%s".', $field));
        }

        $selectedOptions = $element->getParent()->getParent()->findAll('css', 'li.select2-search-choice div');
        $selectedRoles = [];
        foreach ($selectedOptions as $option) {
            $selectedRoles[] = $option->getHtml();
        }

        $expectedUserGroups = $this->getMainContext()->listToArray($userGroups);
        $missingUserGroups = array_diff($selectedRoles, $expectedUserGroups);
        $extraUserGroups = array_diff($expectedUserGroups, $selectedRoles);
        if (count($missingUserGroups) > 0 || count($extraUserGroups) > 0) {
            throw $this->createExpectationException(
                sprintf(
                    'For permission %s, user groups %s are expected, user groups granted are %s',
                    $field,
                    implode(', ', $expectedUserGroups),
                    implode(', ', $selectedRoles)
                )
            );
        }
    }

    /**
     * @param string $status
     *
     * @throws \LogicException
     * @Then /^its status should be "([^"]*)"$/
     */
    public function itsStatusShouldBe($status)
    {
        $info = $this->getSession()->getPage()->find('css', '.navbar-content li:contains("Status")');

        if (false === strpos($info->getText(), $status)) {
            throw new \LogicException(
                sprintf(
                    'Expecting product status "%s", actually is "%s"',
                    $status,
                    $info->getText()
                )
            );
        }
    }

    /**
     * @param string $code
     *
     * @throws \LogicException
     * @Given /^the product rule "([^"]*)" is executed$/
     */
    public function iExecuteTheProductRule($code)
    {
        $rule = $this->getSubcontext('fixtures')->getRule($code);
        $runner = $this->getContainer()->get('pimee_rule_engine.runner.chained');
        $updated = $runner->run($rule);
    }
}
