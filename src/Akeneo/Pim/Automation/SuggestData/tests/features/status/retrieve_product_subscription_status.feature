@acceptance-back
Feature: Fetch product subscription status
  In order to know if product is already subscribed and if I can subscribe
  As the System
  I want to fetch product subscription statuses

  Scenario: I retrieved the product subscription status for a product without family
    Given the product without family "product_without_family"
    When I retrieve the product subscription status of the product "product_without_family"
    Then the product subscription status has no family

  Scenario: I retrieved the product subscription status for a product with family
    Given the product "606449099812" of the family "router"
    When I retrieve the product subscription status of the product "606449099812"
    Then the product subscription status has family

  Scenario: I retrieved the product subscription status for a product already subscribed
    Given the product "B00EYZY6AC" of the family "router"
    And the product "B00EYZY6AC" is subscribed to Franklin
    When I retrieve the product subscription status of the product "B00EYZY6AC"
    Then the product subscription status has subscribed product