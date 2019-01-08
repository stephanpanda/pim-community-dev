@acceptance-back
Feature: Map the PIM attributes with Franklin attributes
  In order to automatically enrich my products
  As a system administrator
  I want to map Franklin attributes to Akeneo PIM attributes

  @critical
  Scenario: Successfully save the attributes mapping for the first time
    Given the family "router"
    And Franklin is configured with a valid token
    When the attributes are mapped for the family "router" as follows:
      | target_attribute_code | pim_attribute_code |
      | product_weight        |                    |
      | color                 | color              |
    Then Franklin's attribute product_weight should not be mapped
    And Franklin's attribute color should be mapped to color

  Scenario: Successfully udpdate the attributes mapping
    Given the family "router"
    And Franklin is configured with a valid token
    And a predefined attributes mapping for the family "router" as follows:
      | target_attribute_code | pim_attribute_code |
      | product_weight        |                    |
      | color                 | color              |
    When the attributes are mapped for the family "router" as follows:
      | target_attribute_code | pim_attribute_code |
      | product_weight        | weight             |
      | color                 | color              |
    Then Franklin's attribute product_weight should be mapped to weight
    And Franklin's attribute color should be mapped to color

  Scenario: Fails to save an empty attribute mapping
    Given the family "router"
    And Franklin is configured with a valid token
    When the attributes mapping for the family "router" is updated with an empty mapping
    Then the attributes mapping should not be saved
    And an empty attributes mapping message should be sent

  Scenario: Fails to save the attributes mapping if the family does not exist
    Given Franklin is configured with a valid token
    When the attributes are mapped for the family "unknown" as follows:
      | target_attribute_code | pim_attribute_code |
      | product_weight        | weight             |
    Then the attributes mapping should not be saved
    And an unknown family message should be sent

  Scenario: Fails to save the attributes mapping if an attribute does not exist
    Given the family "router"
    And Franklin is configured with a valid token
    When the attributes are mapped for the family "router" as follows:
      | target_attribute_code | pim_attribute_code |
      | color                 | unknown_attribute  |
    Then the attributes mapping should not be saved
    And an unknown attribute message should be sent

  Scenario Outline: Fails to save the attributes mapping if an attribute type is invalid
    Given the family "router"
    And the following attribute:
      | code              | type             |
      | invalid_type_attr | <attribute_type> |
    And Franklin is configured with a valid token
    When the attributes are mapped for the family "router" as follows:
      | target_attribute_code | pim_attribute_code |
      | color                 | invalid_type_attr  |
    Then the attributes mapping should not be saved
    And an invalid <attribute_type> attribute type mapping message should be sent

  Examples:
    | attribute_type                  |
    | pim_catalog_identifier          |
    | pim_catalog_date                |
    | pim_catalog_file                |
    | pim_catalog_image               |
    | pim_catalog_price_collection    |
    | pim_reference_data_multiselect  |
    | pim_reference_data_simpleselect |
    | akeneo_reference_entity         |

  Scenario: Fails to save for the first time the attributes mapping when Franklin is down
    Given the family "router"
    And Franklin is configured with a valid token
    And a predefined attributes mapping for the family "router" as follows:
      | target_attribute_code | pim_attribute_code |
      | product_weight        |                    |
      | color                 | color              |
    And Franklin server is down
    When the attributes are mapped for the family "router" as follows:
      | target_attribute_code | pim_attribute_code |
      | product_weight        | weight             |
      | color                 | color              |
    Then the attributes mapping should not be saved
    And a data provider error message should be sent

  Scenario: Fails to udate the attributes mapping when Franklin is down
    Given the family "router"
    And Franklin is configured with a valid token
    And Franklin server is down
    When the attributes are mapped for the family "router" as follows:
      | target_attribute_code | pim_attribute_code |
      | product_weight        | weight             |
      | color                 | color              |
    Then the attributes mapping should not be saved
    And a data provider error message should be sent

  Scenario: Fails to udate the attributes mapping when the token is invalid
    Given the family "router"
    And Franklin is configured with an expired token
    When the attributes are mapped for the family "router" as follows:
      | target_attribute_code | pim_attribute_code |
      | product_weight        | weight             |
      | color                 | color              |
    Then the attributes mapping should not be saved
    And an authentication error message should be sent

  Scenario: Fails to map Franklin attributes with localizable PIM attributes
    Given the family "router"
    And Franklin is configured with a valid token
    And the following attribute:
      | code              | type             | localizable |
      | localizable_attr  | pim_catalog_text | true        |
    When the attributes are mapped for the family "router" as follows:
      | target_attribute_code | pim_attribute_code |
      | product_weight        | localizable_attr   |
    Then the attributes mapping should not be saved
    And an invalid localizable attribute message should be sent

  Scenario: Fails to map Franklin attributes with scopable PIM attributes
    Given the family "router"
    And Franklin is configured with a valid token
    And the following attribute:
      | code              | type             | scopable |
      | scopable_attr     | pim_catalog_text | true     |
    When the attributes are mapped for the family "router" as follows:
      | target_attribute_code | pim_attribute_code |
      | product_weight        | scopable_attr      |
    Then the attributes mapping should not be saved
    And an invalid scopable attribute message should be sent

  Scenario: Fails to map Franklin attributes with locale specific PIM attributes
    Given the family "router"
    And Franklin is configured with a valid token
    And the following locales "en_US"
    And the following text attribute "pim_weight" specific to locale en_US
    When the attributes are mapped for the family "router" as follows:
      | target_attribute_code | pim_attribute_code |
      | product_weight        | pim_weight         |
    Then the attributes mapping should not be saved
    And an invalid locale specific attribute message should be sent

  Scenario: Fails to map the same attribute twice with a franklin attribute
    Given the family "router"
    And Franklin is configured with a valid token
    When the attributes are mapped for the family "router" as follows:
      | target_attribute_code | pim_attribute_code |
      | product_weight        | color              |
      | color                 | color              |
    Then the attributes mapping should not be saved
    And an invalid duplicated pim attribute message should be sent