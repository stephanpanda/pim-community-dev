import {EventsHash} from 'backbone';
import * as $ from 'jquery';
import BaseForm = require('pimenrich/js/view/base');
import BootstrapModal = require('pimui/lib/backbone.bootstrap-modal');
import * as _ from 'underscore';
import {Filterable} from '../../common/filterable';
import AttributeOptionsMapping = require('../attribute-options-mapping/edit');
import SimpleSelectAttribute = require('../common/simple-select-attribute');

const __ = require('oro/translator');
const FetcherRegistry = require('pim/fetcher-registry');
const FormBuilder = require('pim/form-builder');
const Router = require('pim/router');
const template = require('pimee/template/attributes-mapping/attributes-mapping');
const i18n = require('pim/i18n');
const UserContext = require('pim/user-context');

interface NormalizedAttributeMapping {
  mapping: {
    [key: string]: {
      pimAiAttribute: {
        label: string,
        type: string,
      },
      attribute: string,
      status: number,
    },
  };
}

interface Config {
  labels: {
    pending: string,
    mapped: string,
    unmapped: string,
    pimAiAttribute: string,
    catalogAttribute: string,
    attributeMappingStatus: string,
  };
}

/**
 * This module will allow user to map the attributes from PIM.ai to the catalog attributes.
 * It displays a grid with all the attributes to map.
 *
 * The attribute types authorized for the mapping are defined in
 * Akeneo\Pim\Automation\SuggestData\Infrastructure\Controller\AttributeMappingController
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class AttributeMapping extends BaseForm {
  private static readonly ATTRIBUTE_PENDING: number = 0;
  private static readonly ATTRIBUTE_MAPPED: number = 1;
  private static readonly ATTRIBUTE_UNMAPPED: number = 2;
  private static readonly VALID_MAPPING: { [key: string]: string[] } = {
    metric: [ 'pim_catalog_metric' ],
    select: [ 'pim_catalog_simpleselect' ],
    multiselect: [ 'pim_catalog_multiselect' ],
    number: [ 'pim_catalog_number' ],
    text: [ 'pim_catalog_text' ],
  };

  private readonly template = _.template(template);
  private readonly config: Config = {
    labels: {
      pending: '',
      mapped: '',
      unmapped: '',
      pimAiAttribute: '',
      catalogAttribute: '',
      attributeMappingStatus: '',
    },
  };
  private attributeOptionsMappingModal: any = null;
  private attributeOptionsMappingForm: BaseForm | null = null;

  /**
   * {@inheritdoc}
   */
  constructor(options: {config: Config}) {
    super(options);

    this.config = {...this.config, ...options.config};
  }

  /**
   * {@inheritdoc}
   */
  public configure(): JQueryPromise<any> {
    Filterable.set(this);

    return BaseForm.prototype.configure.apply(this, arguments);
  }

  /**
   * {@inheritdoc}
   */
  public render(): BaseForm {
    this.$el.html('');
    const familyMapping: NormalizedAttributeMapping = this.getFormData();
    const mapping = familyMapping.hasOwnProperty('mapping') ? familyMapping.mapping : {};
    this.$el.html(this.template({
      mapping,
      statuses: this.getMappingStatuses(),
      pimAiAttribute: __(this.config.labels.pimAiAttribute),
      catalogAttribute: __(this.config.labels.catalogAttribute),
      attributeMappingStatus: __(this.config.labels.attributeMappingStatus),
      edit: __('pim_common.edit'),
    }));

    Object.keys(mapping).forEach((pimAiAttributeCode: string) => {
      this.appendAttributeSelector(mapping, pimAiAttributeCode);
    });

    this.toggleAttributeOptionButtons(Object.keys(mapping).reduce((acc, pimAiAttributeCode: string) => {
      acc[pimAiAttributeCode] = mapping[pimAiAttributeCode].attribute;

      return acc;
    }, {} as { [key: string]: string }));

    Filterable.afterRender(this, __(this.config.labels.pimAiAttribute));

    this.renderExtensions();
    this.delegateEvents();

    return this;
  }

  /**
   * {@inheritdoc}
   */
  public events(): EventsHash {
    return {
      'click .option-mapping': this.openAttributeOptionsMappingModal,
    };
  }

  /**
   * @param mapping
   * @param {string} pimAiAttributeCode
   */
  private appendAttributeSelector(mapping: any, pimAiAttributeCode: string) {
    const $dom = this.$el.find(
      '.attribute-selector[data-franklin-attribute-code="' + pimAiAttributeCode + '"]',
    );
    const attributeSelector = new SimpleSelectAttribute({
      config: {
        fieldName: 'mapping.' + pimAiAttributeCode + '.attribute',
        label: '',
        choiceRoute: 'pim_enrich_attribute_rest_index',
        types: AttributeMapping.VALID_MAPPING[mapping[pimAiAttributeCode].pimAiAttribute.type],
      },
      className: 'AknFieldContainer AknFieldContainer--withoutMargin AknFieldContainer--inline',
    });
    attributeSelector.configure().then(() => {
      attributeSelector.setParent(this);
      $dom.html(attributeSelector.render().$el);
    });
  }

  /**
   * @returns { [ key: number ]: string }
   */
  private getMappingStatuses() {
    const statuses: { [key: number]: string } = {};
    statuses[AttributeMapping.ATTRIBUTE_PENDING] = __(this.config.labels.pending);
    statuses[AttributeMapping.ATTRIBUTE_MAPPED] = __(this.config.labels.mapped);
    statuses[AttributeMapping.ATTRIBUTE_UNMAPPED] = __(this.config.labels.unmapped);

    return statuses;
  }

  /**
   * This method will show or hide the Attribute Option buttons.
   * The first parameter is the current mapping, from pimAiAttributeCode to pimAttributeCode.
   *
   * @param { [pimAiAttributeCode: string]: string | null } mapping
   */
  private toggleAttributeOptionButtons(mapping: { [pimAiAttributeCode: string]: string | null }) {
    const pimAttributes = Object.values(mapping).filter((pimAttribute) => {
      return '' !== pimAttribute && null !== pimAttribute;
    });

    FetcherRegistry
      .getFetcher('attribute')
      .fetchByIdentifiers(pimAttributes)
      .then((attributes: Array<{ code: string, type: string }>) => {
      Object.keys(mapping).forEach((pimAiAttribute) => {
        const $attributeOptionButton = this.$el.find(
          '.option-mapping[data-franklin-attribute-code=' + pimAiAttribute + ']',
        );
        const attribute: { code: string, type: string } | undefined = attributes
          .find((attr: { code: string, type: string }) => {
            return attr.code === mapping[pimAiAttribute];
          },
        );
        const type = undefined === attribute ? '' : attribute.type;

        ['pim_catalog_simpleselect', 'pim_catalog_multiselect'].indexOf(type) >= 0 ?
          $attributeOptionButton.show() :
          $attributeOptionButton.hide();
      });
    });
  }

  /**
   * Open the modal for the attribute options mapping
   *
   * @param { { currentTarget: any } } event
   */
  private openAttributeOptionsMappingModal(event: { currentTarget: any }) {
    const $line = $(event.currentTarget).closest('.line');
    const franklinAttributeLabel = $line.data('pim-ai-attribute') as string;
    const franklinAttributeCode = $line.find('.attribute-selector').data('franklin-attribute-code');
    const catalogAttributeCode =
        $line.find('input[name="mapping.' + franklinAttributeCode + '.attribute"]').val() as string;
    const familyCode = Router.match(window.location.hash).params.familyCode;

    $.when(
      FormBuilder.build('pimee-suggest-data-settings-attribute-options-mapping-edit'),
      FetcherRegistry.getFetcher('family').fetch(familyCode),
    ).then((
      form: BaseForm,
      normalizedFamily: any,
    ) => {
      this.attributeOptionsMappingModal = new BootstrapModal({
        className: 'modal modal--fullPage modal--topButton',
        modalOptions: {
          backdrop: 'static',
          keyboard: false,
        },
        allowCancel: true,
        okCloses: false,
        title: '',
        content: '',
        cancelText: ' ',
      });
      this.attributeOptionsMappingModal.open();
      this.attributeOptionsMappingForm = form;

      const formContent = form.getExtension('content') as AttributeOptionsMapping;
      formContent
        .setFamilyLabel(i18n.getLabel(normalizedFamily.labels, UserContext.get('catalogLocale'), normalizedFamily.code))
        .setFamilyCode(familyCode)
        .setFranklinAttributeLabel(franklinAttributeLabel)
        .setCatalogAttributeCode(catalogAttributeCode);

      this.listenTo(form, 'pim_enrich:form:entity:post_save', this.closeAttributeOptionsMappingModal.bind(this));

      $('.modal .ok').remove();
      form.setElement(this.attributeOptionsMappingModal.$('.modal-body')).render();

      this.attributeOptionsMappingModal.on('cancel', this.closeAttributeOptionsMappingModal.bind(this));
    });
  }

  /**
   * Closes the modal then destroy all its data inside.
   */
  private closeAttributeOptionsMappingModal(): void {
    if (null !== this.attributeOptionsMappingModal) {
      this.attributeOptionsMappingModal.close();
      this.attributeOptionsMappingModal = null;
    }

    if (null !== this.attributeOptionsMappingForm) {
      this.attributeOptionsMappingForm.getFormModel().clear();
      this.attributeOptionsMappingForm = null;
    }
  }
}

export = AttributeMapping;
