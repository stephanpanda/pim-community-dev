@javascript
Feature: Save a product draft
  In order to update the product even if I'm not owner
  As a redactor
  I need to be able to save a product draft

  Background:
    Given a "clothing" catalog configuration
    And the asset temporary file storage has been cleared
    And the following product:
      | sku    | family | categories        | description-en_US-mobile |
      | tshirt | tees   | summer_collection | Can't touch this         |
    And the following product:
      | sku    | family  | categories | gallery |
      | jacket | jackets | jackets    | paint   |
    And I am logged in as "Mary"

  @jira https://akeneo.atlassian.net/browse/PIM-4604
  Scenario: Successfully save an empty product draft value
    Given I edit the "tshirt" product
    And I visit the "Attributes" column tab
    And I change the "Name" to "Dark Tshirt"
    When I save the product
    Then the product Name should be "Dark Tshirt"

  @jira https://akeneo.atlassian.net/browse/PIM-4604
  Scenario: Successfully save an existing product draft value
    Given I edit the "tshirt" product
    And I visit the "Attributes" column tab
    And I change the "Description" to "Yes I can"
    When I save the product
    Then the product Description should be "Yes I can"

  @jira https://akeneo.atlassian.net/browse/PIM-4597
  Scenario: Successfully show the product draft status
    Given I edit the "tshirt" product
    Then I should see the text "Draft status Working copy"
    When I change the "Description" to "Hammer time"
    And I save the product
    Then I should see the text "Draft status In progress"
    When I press the Send for approval button
    Then I should see the text "Draft status Waiting for approval"

  Scenario: Mass upload assets from product edit form
    Given I edit the "jacket" product
    And I open the mass uploader of the asset collection
    And I select the assets to upload:
      | name        |
      | akeneo.jpg  |
      | akeneo2.jpg |
    And I start assets mass upload
    And I import assets mass upload
    And I wait for the "apply_assets_mass_upload_into_asset_collection" job to finish
    When I am on the "jacket" product page
    Then I should see the text "Draft status In progress"
    When I press the Send for approval button
    Then I should see the text "Draft status Waiting for approval"
    And I should get the following proposals:
      | product | username | result                                                                                                                                                                      |
      | jacket  | Mary     | {"values":{"gallery":[{"locale":null,"scope":null,"data":["paint","akeneo","akeneo2"]}]},"review_statuses":{"gallery":[{"locale":null,"scope":null,"status":"to_review"}]}} |