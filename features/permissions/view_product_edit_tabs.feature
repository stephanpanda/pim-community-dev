@javascript
Feature: Check product edit tabs visibility
  In order to be able to prevent some users from editing some products informations
  As an administrator or a user
  I should not be able to see tabs

  Background:
    Given the "clothing" catalog configuration
    And the following products:
      | sku     | categories |
      | rangers | pants      |
    And the following product category accesses:
      | product category | user group | access |
      | pants            | Redactor   | edit   |

  @jira https://akeneo.atlassian.net/browse/PIM-4483
  Scenario: Not being able to classify a product if I am not owner
    Given I am logged in as "Mary"
    And I edit the "rangers" product
    Then I should not see the "Categories" tab
    When I logout
    And I am logged in as "Julia"
    And I edit the "rangers" product
    Then I should see the "Categories" tab
