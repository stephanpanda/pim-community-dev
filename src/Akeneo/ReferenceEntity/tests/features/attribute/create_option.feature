Feature: Append an option into an attribute
  In order to enrich my record with one or more options for a given value
  As a user
  I want to append an option into the available options of an option attribute or an option collection attribute

  Background:
    Given a valid reference entity

  @acceptance-back
  Scenario: Append an option into an option attribute
    Given an option attribute
    When the user appends a new option for this attribute
    Then the option is added into the option collection of the attribute

#  @acceptance-back
#  Scenario: Append an option into an option collection attribute
#    Given an option attribute
#    When the user add a new option for this attribute
#    Then the option is added into the option colelction of the attribute
#
#  @acceptance-back
#  Scenario: Append an option into an option collection attribute
#    Given an option attribute
#    When the user add a new option for this attribute
#    Then the option is added into the option colelction of the attribute
