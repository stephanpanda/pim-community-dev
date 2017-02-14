@javascript
Feature: Products datagrid projects
  In order to easily see on which project I have to work on
  As a contributor
  I need to be able to apply and tweak datagrid project filters

  Background:
    Given the "activity_manager" catalog configuration
    And the following attribute groups:
      | code      | label-en_US | group | type             |
      | marketing | Marketing   | other | pim_catalog_text |
      | technical | Technical   | other | pim_catalog_text |
      | other     | Other       | other | pim_catalog_text |
      | media     | Media       | other | pim_catalog_text |
    And the following attributes:
      | code         | label-en_US  | type                   | localizable | scopable | decimals_allowed | metric_family | default metric unit | useable_as_grid_filter | group     | allowed extensions |
      | sku          | SKU          | pim_catalog_identifier | 0           | 0        |                  |               |                     | 1                      | other     |                    |
      | name         | Name         | pim_catalog_text       | 1           | 0        |                  |               |                     | 1                      | marketing |                    |
      | description  | Description  | pim_catalog_text       | 1           | 1        |                  |               |                     | 0                      | marketing |                    |
      | size         | Size         | pim_catalog_text       | 1           | 0        |                  |               |                     | 1                      | marketing |                    |
      | weight       | Weight       | pim_catalog_metric     | 1           | 0        | 0                | Weight        | GRAM                | 1                      | technical |                    |
      | release_date | Release date | pim_catalog_date       | 1           | 0        |                  |               |                     | 1                      | other     |                    |
      | capacity     | Capacity     | pim_catalog_metric     | 0           | 0        | 0                | Binary        | GIGABYTE            | 1                      | technical |                    |
      | material     | Material     | pim_catalog_text       | 1           | 0        |                  |               |                     | 1                      | technical |                    |
      | picture      | Picture      | pim_catalog_image      | 0           | 1        |                  |               |                     | 0                      | media     | jpg                |
    And the following categories:
      | code       | label-en_US | parent  |
      | clothing   | Clothing    | default |
      | high_tech  | High-Tech   | default |
      | decoration | Decoration  | default |
    And the following product category accesses:
      | product category | user group          | access |
      | clothing         | Marketing           | edit   |
      | clothing         | Technical Clothing  | edit   |
      | clothing         | Technical High-Tech | none   |
      | clothing         | Read Only           | view   |
      | clothing         | Media manager       | edit   |
      | high_tech        | Marketing           | edit   |
      | high_tech        | Technical Clothing  | view   |
      | high_tech        | Technical High-Tech | edit   |
      | high_tech        | Read Only           | view   |
      | high_tech        | Media manager       | edit   |
      | decoration       | Marketing           | edit   |
      | decoration       | Technical Clothing  | none   |
      | decoration       | Technical High-Tech | none   |
      | decoration       | Read Only           | view   |
      | decoration       | Media manager       | edit   |
    And the following attribute group accesses:
      | attribute group | user group          | access | group | type             |
      | marketing       | Marketing           | edit   | other | pim_catalog_text |
      | marketing       | Technical Clothing  | view   | other | pim_catalog_text |
      | marketing       | Technical High-Tech | view   | other | pim_catalog_text |
      | marketing       | Read Only           | view   | other | pim_catalog_text |
      | marketing       | Media manager       | view   | other | pim_catalog_text |
      | technical       | Marketing           | view   | other | pim_catalog_text |
      | technical       | Technical Clothing  | edit   | other | pim_catalog_text |
      | technical       | Technical High-Tech | edit   | other | pim_catalog_text |
      | technical       | Read Only           | view   | other | pim_catalog_text |
      | technical       | Media manager       | none   | other | pim_catalog_text |
      | other           | Marketing           | edit   | other | pim_catalog_text |
      | other           | Technical Clothing  | edit   | other | pim_catalog_text |
      | other           | Technical High-Tech | edit   | other | pim_catalog_text |
      | other           | Read Only           | view   | other | pim_catalog_text |
      | other           | Media manager       | view   | other | pim_catalog_text |
      | media           | Marketing           | view   | other | pim_catalog_text |
      | media           | Technical Clothing  | view   | other | pim_catalog_text |
      | media           | Technical High-Tech | view   | other | pim_catalog_text |
      | media           | Read Only           | view   | other | pim_catalog_text |
      | media           | Media manager       | edit   | other | pim_catalog_text |
    And the following families:
      | code     | label-en_US | attributes                                             | requirements-ecommerce             | requirements-mobile                |
      | tshirt   | TShirts     | sku,name,description,size,weight,release_date,material | sku,name,size,description,material | sku,name,size,description,material |
      | usb_keys | USB Keys    | sku,name,description,size,weight,release_date,capacity | sku,name,size,description,capacity | sku,name,size,description,capacity |
      | posters  | Posters     | sku,name,description,size,release_date,picture         | sku,name,size,description,picture  | sku,name,size,description,picture  |
    And the following products:
      | sku                  | family   | categories         | name-en_US                | size-en_US | weight-en_US | weight-en_US-unit | release_date-en_US | release_date-fr_FR | material-en_US | capacity | capacity-unit |
      | tshirt-the-witcher-3 | tshirt   | clothing           | T-Shirt "The Witcher III" | L          | 5            | OUNCE             | 2015-06-20         | 2015-06-20         | cotton         |          |               |
      | tshirt-skyrim        | tshirt   | clothing           | T-Shirt "Skyrim"          | M          | 5            | OUNCE             |                    |                    |                |          |               |
      | tshirt-lcd           | tshirt   | clothing,high_tech | T-shirt LCD screen        | M          | 6            | OUNCE             | 2016-08-13         |                    |                |          |               |
      | usb-key-big          | usb_keys | high_tech          | USB Key Big 64Go          |            | 1            | OUNCE             | 2016-08-13         | 2016-10-13         |                |          |               |
      | usb-key-small        | usb_keys | high_tech          |                           |            | 1            | OUNCE             |                    |                    |                | 8        | GIGABYTE      |
      | poster-movie-contact | posters  | decoration         | Movie poster "Contact"    | A1         |              |                   |                    |                    |                |          |               |

  Scenario: A contributor can modify filters of a projects on the grid
    Given I am logged in as "Julia"
    And I am on the products page
    And I filter by "family" with operator "in list" and value "TShirts"
    And I show the filter "size"
    And I filter by "size" with operator "contains" and value "M"
    And I click on the create project button
    When I fill in the following information in the popin:
      | project-label    | My TShirts Project |
      | project-due-date | 01/25/2077         |
    And I press the "Save" button
    Then I should be on the products page
    And I go on the last executed job resume of "project_calculation"
    And I wait for the "project_calculation" job to finish
    When I am on the products page
    And I switch view selector type to "Projects"
    Then I should see the text "My TShirts Project"
    And the grid should contain 2 elements
    When I filter by "size" with operator "contains" and value "L"
    Then the grid should contain 1 element
    When I filter by "size" with operator "contains" and value ""
    And I filter by "family" with operator "in list" and value ""
    Then the grid should contain 6 elements

  Scenario: A project is not seen as modified if only columns change
    Given I am logged in as "Julia"
    And I am on the products page
    And I filter by "family" with operator "in list" and value "TShirts"
    And I show the filter "size"
    And I filter by "size" with operator "contains" and value "M"
    And I click on the create project button
    When I fill in the following information in the popin:
      | project-label    | My TShirts Project |
      | project-due-date | 01/25/2077         |
    And I press the "Save" button
    Then I should be on the products page
    And I go on the last executed job resume of "project_calculation"
    And I wait for the "project_calculation" job to finish
    When I am on the products page
    And I switch view selector type to "Projects"
    Then I should see the text "My TShirts Project"
    When I display in the products grid the columns sku, name, groups
    Then I should be on the products page
    And the grid should contain 2 elements
    And I should see the text "My TShirts Project"
    But I should not see the text "My TShirts Project *"

  Scenario: I fallback on my custom default view if the project I was working on has been deleted
    Given I am logged in as "Julia"
    And I am on the products page
    And I filter by "family" with operator "in list" and value "Posters"
    And I create the view:
      | new-view-label | My posters |
    Then I should be on the products page
    And I should see the flash message "Datagrid view successfully created"
    When I am on the User profile show page
    And I press the "Edit" button
    Then I should see the text "Edit user - Julia Stark"
    When I visit the "Additional" tab
    Then I should see the text "Default product grid view"
    When I fill in the following information:
      | Default product grid view | My posters |
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    When I am on the products page
    And I filter by "family" with operator "in list" and value "TShirts"
    And I show the filter "size"
    And I filter by "size" with operator "contains" and value "M"
    And I click on the create project button
    When I fill in the following information in the popin:
      | project-label    | My TShirts Project |
      | project-due-date | 01/25/2077         |
    And I press the "Save" button
    Then I should be on the products page
    And I go on the last executed job resume of "project_calculation"
    And I wait for the "project_calculation" job to finish
    When I am on the products page
    And I switch view selector type to "Projects"
    Then I should see the text "My TShirts Project"
    When  I am on the "ecommerce" channel page
    And I change the "Locales" to "French (France)"
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    When I am on the products page
    Then I should see the text "My posters"
    But I should not see the text "My TShirts Project"
    And the grid should contain 1 element
