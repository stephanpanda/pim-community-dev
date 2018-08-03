@acceptance-back
Feature: Subscribe a product to PIM.ai
  In order to automatically enrich my products
  As Julia
  I want to subscribe a product to PIM.ai

  Scenario: Successfully subscribe a product to PIM.ai
    Given the following attribute:
      | code  | type                   |
      | ean   | pim_catalog_text       |
      | sku   | pim_catalog_identifier |
    And the following family:
      | code | attributes |
      | tshirt | sku,ean  |
    And the following product:
      | identifier | family | ean |
      | ts_0013    | tshirt | 156 |
    And a predefined mapping as follows:
      | pim_ai_code | attribute_code |
      | upc         | ean            |
    When I subscribe the product "ts_0013" to PIM.ai
    Then the product "ts_0013" should be subscribed

  #Scenario: Successfully subscribe a product to PIM.ai that does not exist on PIM.ai

  #Scenario: Fail to subscribe a product that is already subscribed to PIM.ai

  #Scenario: Fail to subscribe a product that does not exist

  #Scenario: Fail to subscribe a product if the identifier mapping is empty

  #Scenario: Fail to subscribe a product that does not have any value on mapped identifiers
