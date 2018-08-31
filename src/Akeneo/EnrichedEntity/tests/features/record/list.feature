Feature: Lists all records of an enriched entity
  In order to see the records of an enriched entity
  As a user
  I want to list all of its records

  @acceptance-front
  Scenario: List all records of an enriched entity
    Given the following enriched entity:
      | identifier | labels                                       | image |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} | null  |
    And the following records for the enriched entity "designer":
      | identifier        | code   | labels                        |
      | designer_starck_1 | starck | {"en_US": "Philippe Starck" } |
      | designer_coco_2   | coco   | {"en_US": "Coco"}             |
    When the user asks for the enriched entity "designer"
    Then the list of records should be:
      | identifier        |
      | designer_starck_1 |
      | designer_coco_2   |

  @acceptance-front
  Scenario: Shows an empty page when there are no records for the enriched entity
    Given the following enriched entity:
      | identifier | labels                                | image |
      | brand      | {"en_US": "Brand", "fr_FR": "Marque"} | null  |
    When the user asks for the enriched entity "brand"
    Then the list of records should be empty
