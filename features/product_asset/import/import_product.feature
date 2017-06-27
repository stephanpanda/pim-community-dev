@javascript
Feature: Import products with an assets collection
  In order to import products with a collection of assets
  As a product manager

  Background:
    Given the "clothing" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully import a csv file of products with a collection of assets
    Given the following CSV file to import:
      """
      sku;categories;enabled;family;gallery;groups;main_color;name-de_DE;name-en_US;name-fr_FR;price-EUR;price-USD;size
      pant-1;summer_collection;1;pants;dog,tiger;;white;Hose;Pant;Pantalon;50.00;70.00;S
      pant-2;summer_collection;0;pants;minivan;;white;Hose;Pant;Pantalon;50.00;70.00;S
      pant-3;summer_collection;0;pants;;;white;Hose;Pant;Pantalon;50.00;70.00;S
      """
    And the following job "csv_clothing_product_import" configuration:
      | filePath          | %file to import% |
      | enabledComparison | yes              |
    When I am on the "csv_clothing_product_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_product_import" job to finish
    Then there should be 3 products
    And the product "pant-1" should have the following values:
      | gallery | dog, tiger |
    And the product "pant-2" should have the following values:
      | gallery | minivan |
    And the product "pant-3" should have the following values:
      | gallery |  |

  Scenario: Skip products with unknown collection assets
    Given the following CSV file to import:
      """
      sku;categories;enabled;family;gallery;groups;main_color;name-de_DE;name-en_US;name-fr_FR;price-EUR;price-USD;size
      pant-1;summer_collection;1;pants;foo,tiger;;white;Hose;Pant;Pantalon;50.00;70.00;S
      pant-2;summer_collection;0;pants;minivan;;white;Hose;Pant;Pantalon;50.00;70.00;S
      pant-3;summer_collection;0;pants;bar;;white;Hose;Pant;Pantalon;50.00;70.00;S
      """
    And the following job "csv_clothing_product_import" configuration:
      | filePath          | %file to import% |
      | enabledComparison | yes              |
    When I am on the "csv_clothing_product_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_product_import" job to finish
    Then I should see "created 1"
    And I should see "skipped 2"
    And I should see "Property \"gallery\" expects a valid reference data code. The code of the reference data \"assets\" does not exist, \"foo\" given."
    And I should see "Property \"gallery\" expects a valid reference data code. The code of the reference data \"assets\" does not exist, \"bar\" given."
    Then there should be 1 products
    And the product "pant-2" should have the following values:
      | gallery | minivan |