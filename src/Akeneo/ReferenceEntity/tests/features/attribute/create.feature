Feature: Create an attribute linked to an reference entity
  In order to create an attribute linked to an reference entity
  As a user
  I want create an attribute linked to an reference entity

  Background:
    Given the following reference entity:
      | identifier | labels                                       | image |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} | null  |

  @acceptance-back
  Scenario: Create an image attribute linked to an reference entity
    When the user creates an image attribute "image" linked to the reference entity "designer" with:
      | code  | labels                                    | is_required | order | value_per_channel | value_per_locale | max_file_size | allowed_extensions |
      | image | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 0     | true              | false            | 250.0         | ["png", "jpg"]     |
    Then there is an image attribute "image" in the reference entity "designer" with:
      | code  | labels                                    | is_required | order | value_per_channel | value_per_locale | max_file_size | allowed_extensions | type  |
      | image | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 0     | true              | false            | 250.0         | ["png", "jpg"]     | image |

  @acceptance-back
  Scenario: Create a text attribute linked to an reference entity
    When the user creates a text attribute "name" linked to the reference entity "designer" with:
      | code | labels                                    | is_required | order | value_per_channel | value_per_locale | max_length |
      | name | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 0     | true              | false            | 44         |
    Then there is a text attribute "name" in the reference entity "designer" with:
      | code | labels                                    | is_required | order | value_per_channel | value_per_locale | max_length | type | is_textarea | is_rich_text_editor | validation_rule | regular_expression |
      | name | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 0     | true              | false            | 44         | text | 0           | 0                   | none            |                    |

  @acceptance-back
  Scenario: Cannot create an attribute for an reference entity if it already exists
    Given the user creates a text attribute "name" linked to the reference entity "designer" with:
      | code | labels                                    | is_required | order | value_per_channel | value_per_locale | max_length |
      | name | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 0     | true              | false            | 44         |
    When the user creates a text attribute "name" linked to the reference entity "designer" with:
      | code | labels                                    | is_required | order | value_per_channel | value_per_locale | max_length |
      | name | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 0     | true              | false            | 44         |
    Then an exception is thrown

  @acceptance-back
  Scenario: Cannot create an attribute with the same order for an reference entity
    When the user creates a text attribute "name" linked to the reference entity "designer" with:
      | code | labels                                    | is_required | order | value_per_channel | value_per_locale | max_length |
      | name | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 0     | true              | false            | 44         |
    And the user creates a text attribute "bio" linked to the reference entity "designer" with:
      | code | labels                                  | is_required | order | value_per_channel | value_per_locale | max_length |
      | bio  | {"en_US": "Bio", "fr_FR": "Biographie"} | true        | 0     | true              | false            | 44         |
    Then an exception is thrown

  @acceptance-back
  Scenario Outline: Cannot create an attribute with a reserverd word as code
    When the user creates a text attribute "code" linked to the reference entity "designer" with:
      | labels                                    | is_required | order | value_per_channel | value_per_locale | max_length |
      | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 0     | true              | false            | 44         |
    Then there should be a validation error on the property 'code' with message '<message>'
    And there is no exception thrown

    Examples:
      | invalid_attribute_code | message                                                  |
      | labels                 | The code cannot be any of those values: "%code, labels%" |
      | code                   | The code cannot be any of those values: "%code, labels%" |

  @acceptance-front
  Scenario: Create a simple valid text attribute
    When the user creates a valid attribute
    And the user saves the valid attribute
    Then the user should not see any validation error

  @acceptance-front
  Scenario: Create an invalid text attribute
    When the user creates an attribute with an invalid code
    And the user saves the attribute with an invalid code
    Then the user should see the validation error "This field may only contain letters, numbers and underscores."