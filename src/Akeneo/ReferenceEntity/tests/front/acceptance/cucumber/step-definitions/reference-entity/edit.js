const Edit = require('../../decorators/reference-entity/edit.decorator');
const Header = require('../../decorators/reference-entity/app/header.decorator');
const path = require('path');

const {
  decorators: {createElementDecorator},
  tools: {convertDataTable, convertItemTable, answerJson},
} = require(path.resolve(process.cwd(), './tests/front/acceptance/cucumber/test-helpers.js'));

module.exports = async function(cucumber) {
  const {When, Then} = cucumber;
  const assert = require('assert');

  const config = {
    Header: {
      selector: '.AknTitleContainer',
      decorator: Header,
    },
    Edit: {
      selector: '.AknDefault-contentWithColumn',
      decorator: Edit,
    },
  };

  const getElement = createElementDecorator(config);

  const askForReferenceEntity = async function(identifier) {
    await this.page.evaluate(async identifier => {
      const Controller = require('pim/controller/reference-entity/edit');
      const controller = new Controller();
      controller.renderRoute({params: {identifier, tab: 'attribute'}});

      await document.getElementById('app').appendChild(controller.el);
    }, identifier);

    await this.page.waitFor('.AknDefault-mainContent[data-tab="attribute"] .AknSubsection-container');
    const editPage = await await getElement(this.page, 'Edit');
    const properties = await editPage.getProperties();
    const isLoaded = await properties.isLoaded();

    assert.strictEqual(isLoaded, true);
  };

  const changeReferenceEntity = async function(editPage, identifier, updates) {
    const properties = await editPage.getProperties();

    const labels = convertDataTable(updates).labels;

    Object.keys(labels).forEach(async locale => {
      const label = labels[locale];
      await (await editPage.getLocaleSwitcher()).switchLocale(locale);
      await properties.setLabel(label);
    });
  };

  const savedReferenceEntityWillBe = function(page, identifier, updates) {
    page.on('request', request => {
      if (`http://pim.com/rest/reference_entity/${identifier}` === request.url() && 'POST' === request.method()) {
        answerJson(request, {}, 204);
      }

      if (`http://pim.com/rest/reference_entity/${identifier}` === request.url() && 'GET' === request.method()) {
        answerJson(request, convertItemTable(updates)[0], 200);
      }
    });
  };

  const answerLocaleList = function() {
    this.page.on('request', request => {
      if ('http://pim.com/configuration/locale/rest?activated=true' === request.url() && 'GET' === request.method()) {
        answerJson(
          request,
          [
            {code: 'de_DE', label: 'German (Germany)', region: 'Germany', language: 'German'},
            {code: 'en_US', label: 'English (United States)', region: 'United States', language: 'English'},
            {code: 'fr_FR', label: 'French (France)', region: 'France', language: 'French'},
          ],
          200
        );
      }
    });
  };

  When('the user asks for the reference entity {string}', askForReferenceEntity);

  When('the user gets the reference entity {string} with label {string}', async function(
    expectedIdentifier,
    expectedLabel
  ) {
    const editPage = await await getElement(this.page, 'Edit');
    const properties = await editPage.getProperties();
    const identifierValue = await properties.getIdentifier();
    assert.strictEqual(identifierValue, expectedIdentifier);

    const labelValue = await properties.getLabel();
    assert.strictEqual(labelValue, expectedLabel);
  });

  When('the user updates the reference entity {string} with:', async function(identifier, updates) {
    await answerLocaleList.apply(this);
    await askForReferenceEntity.apply(this, [identifier]);

    const editPage = await await getElement(this.page, 'Edit');
    await changeReferenceEntity(editPage, identifier, updates);
    await savedReferenceEntityWillBe(this.page, identifier, updates);
    await editPage.save();
  });

  When('the user changes the reference entity {string} with:', async function(identifier, updates) {
    await answerLocaleList.apply(this);
    await askForReferenceEntity.apply(this, [identifier]);

    const editPage = await await getElement(this.page, 'Edit');

    await changeReferenceEntity.apply(this, [editPage, identifier, updates]);
  });

  Then('the reference entity {string} should be:', async function(identifier, updates) {
    const referenceEntity = convertItemTable(updates)[0];

    const editPage = await await getElement(this.page, 'Edit');
    const properties = await editPage.getProperties();
    const identifierValue = await properties.getIdentifier();
    assert.strictEqual(identifierValue, referenceEntity.identifier);

    await Object.keys(referenceEntity.labels).forEach(async locale => {
      const label = referenceEntity.labels[locale];
      await (await editPage.getLocaleSwitcher()).switchLocale(locale);
      const labelValue = await properties.getLabel();
      assert.strictEqual(labelValue, label);
    });
  });

  Then('the saved reference entity {string} will be:', async function(identifier, updates) {
    await savedReferenceEntityWillBe(this.page, identifier, updates);
  });

  Then('the user saves the changes', async function() {
    const editPage = await await getElement(this.page, 'Edit');
    await editPage.save();
  });

  Then('the user should see the saved notification', async function() {
    const editPage = await await getElement(this.page, 'Edit');
    const hasSuccessNotification = await editPage.hasSuccessNotification();

    assert.strictEqual(hasSuccessNotification, true);
  });

  Then('the reference entity {string} save will fail', function(identifier) {
    this.page.on('request', request => {
      if (`http://pim.com/rest/reference_entity/${identifier}` === request.url() && 'POST' === request.method()) {
        request.respond({
          status: 500,
          contentType: 'text/plain',
          body: 'Internal Error',
        });
      }
    });
  });

  Then('the user should see the saved notification error', async function() {
    const editPage = await await getElement(this.page, 'Edit');
    const hasErrorNotification = await editPage.hasErrorNotification();

    assert.strictEqual(hasErrorNotification, true);
  });

  When('the user deletes the reference entity {string}', async function(identifier) {
    const header = await await getElement(this.page, 'Header');

    this.page.once('request', request => {
      if (`http://pim.com/rest/reference_entity/${identifier}` === request.url() && 'DELETE' === request.method()) {
        request.respond({
          status: 204,
          contentType: 'application/json',
          body: null,
        });
      }
    });

    this.page.once('dialog', async dialog => {
      await dialog.accept();
    });

    header.clickOnDeleteButton();
  });

  When('the user fails to delete the reference entity {string}', async function(identifier) {
    const header = await await getElement(this.page, 'Header');
    const response = JSON.stringify([
      {
        messageTemplate: 'pim_reference_entity.reference_entity.validation.records.should_have_no_record',
        parameters: {'%reference_entity_identifier%': []},
        plural: null,
        message: 'You cannot delete this entity because records exist for this entity',
        root: {identifier: `${identifier}`},
        propertyPath: '',
        invalidValue: {identifier: `${identifier}`},
        constraint: {targets: 'class', defaultOption: null, requiredOptions: [], payload: null},
        cause: null,
        code: null,
      },
    ]);

    this.page.once('request', request => {
      if (`http://pim.com/rest/reference_entity/${identifier}` === request.url() && 'DELETE' === request.method()) {
        request.respond({
          status: 400,
          contentType: 'application/json',
          body: response,
        });
      }
    });

    this.page.once('dialog', async dialog => {
      await dialog.accept();
    });

    header.clickOnDeleteButton();
  });

  When('the user refuses to delete the current reference entity', async function() {
    const header = await await getElement(this.page, 'Header');

    this.page.once('dialog', async dialog => {
      await dialog.dismiss();
    });

    header.clickOnDeleteButton();
  });

  Then('the user should see the deleted notification', async function() {
    const editPage = await await getElement(this.page, 'Edit');
    const hasSuccessNotification = await editPage.hasSuccessNotification();

    assert.strictEqual(hasSuccessNotification, true);
  });

  Then('the user should see the delete notification error', async function() {
    const editPage = await await getElement(this.page, 'Edit');
    const hasErrorNotification = await editPage.hasErrorNotification();

    assert.strictEqual(hasErrorNotification, true);
  });

  Then('the user should not be notified that deletion has been made', async function() {
    const editPage = await await getElement(this.page, 'Edit');
    const hasNoNotification = await editPage.hasNoNotification();

    assert.strictEqual(hasNoNotification, true);
  });

  Then('the user should not see the deletion button', async function() {
    const header = await await getElement(this.page, 'Header');
    const isDeleteButtonVisible = await header.isDeleteButtonVisible();

    assert.strictEqual(isDeleteButtonVisible, false);
  });
};