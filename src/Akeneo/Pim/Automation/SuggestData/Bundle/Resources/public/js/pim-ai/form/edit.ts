import * as _ from 'underscore';
import BaseView = require('pimenrich/js/view/base');
import {EventsHash} from 'backbone';
import {getConfiguration, isConnectionActivated} from 'akeneosuggestdata/js/pim-ai/fetcher/connection-fetcher';

const __ = require('oro/translator');
const ConnectionSaver = require('pimee/saver/pim-ai-connection');
const Messenger = require('oro/messenger');
const template = require('pimee/template/pim-ai-connection/edit');

interface EditConfig {
  token_label_title: string;
  token_label_content: string;
  token_field_title: string;
  token_field_placeholder: string;
  token_save_pre_activation_title: string;
  token_save_post_activation_title: string;
  code: string;
}

/**
 * Setups the connection to PIM.ai.
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditView extends BaseView {
  readonly template = _.template(template);

  readonly config: EditConfig = {
    token_label_title: '',
    token_label_content: '',
    token_field_title: '',
    token_field_placeholder: '',
    token_save_pre_activation_title: '',
    token_save_post_activation_title: '',
    code: '',
  };

  private storedToken: string = '';
  private isConnectionActivated: boolean = false;

  /**
   * {@inheritdoc}
   */
  constructor(options: { config: EditConfig }) {
    super(options);

    this.config = {...this.config, ...options.config};
  };

  /**
   * {@inheritdoc}
   */
  public events(): EventsHash {
    return {
      'click .activate-connection': 'activate',
      'keyup input': 'updateModel',
    };
  }

  /**
   * {@inheritdoc}
   */
  public configure(): JQueryPromise<any> {
    return $.when(
      getConfiguration(this.config.code).then((configuration: any) => {
        const data = {token: ''};
        if (configuration.hasOwnProperty('values')) {
          data.token = configuration.values.token;
        }

        this.setData(data);
        this.storedToken = data.token;
      }),
      BaseView.prototype.configure.apply(this, arguments)
    );
  }

  /**
   * {@inheritdoc}
   */
  public render(): BaseView {
    isConnectionActivated(this.config.code).then((isConnectionActivated: any) => {
      const formData = this.getFormData();

      this.isConnectionActivated = isConnectionActivated;
      true === isConnectionActivated
        ? this.renderActivatedConnection(formData.token)
        : this.renderUnactivatedConnection(formData.token);
    });

    return this;
  };

  /**
   * Activates the connection to PIM.ai
   */
  public activate(): void {
    const data = this.getFormData();

    ConnectionSaver
      .save(this.config.code, data)
      .fail((xhr: any) => {
        Messenger.notify(xhr.responseJSON.status, xhr.responseJSON.message);
        this.renderUnactivatedConnection(data.token);
      })
      .done((response: any) => {
        Messenger.notify(response.status, response.message);
        this.storedToken = data.token;
        this.isConnectionActivated = true;
        this.renderActivatedConnection(data.token);
      });
  }

  /**
   * Updates the model.
   * The state of the activation button will be changed too.
   */
  public updateModel(): void {
    const fieldValue = $('.token-field').val();

    const token: string = undefined === fieldValue ? '' : fieldValue.toString();

    this.setData({token: token});

    if (true === this.isConnectionActivated) {
      this.storedToken !== token ? this.buttonAllowedToActivateConnection() : this.buttonDisallowedToActivateConnection();
    }
  }

  /**
   * Renders the view for an invalid or empty token.
   *
   * @param {string} token
   */
  private renderUnactivatedConnection(token: string): void {
    this.$el.html(
      this.template({
        tokenLabelTitle: __(this.config.token_label_title),
        tokenLabelContent: __(this.config.token_label_content),
        tokenFieldTitle: __(this.config.token_field_title),
        tokenFieldPlaceholder: __(this.config.token_field_placeholder),
        token: token,
        activationLabel: __(this.config.token_save_pre_activation_title),
        buttonStyle: 'AknButton--slateGrey',
        connectionStatus: 'activate-connection',
      })
    );
  }

  /**
   * Renders the view for a filled and valid token.
   *
   * @param {string} token
   */
  private renderActivatedConnection(token: string): void {
    this.$el.html(
      this.template({
        tokenLabelTitle: __(this.config.token_label_title),
        tokenLabelContent: __(this.config.token_label_content),
        tokenFieldTitle: __(this.config.token_field_title),
        tokenFieldPlaceholder: __(this.config.token_field_placeholder),
        token: token,
        activationLabel: __(this.config.token_save_post_activation_title),
        buttonStyle: 'AknButton--apply AknButton--disabled',
        connectionStatus: 'connection-activated',
      })
    );
  }

  /**
   * Makes the button grey with text "Activate" so the user knows that connection
   * to PIM.ai is not active and new token can be submitted.
   */
  private buttonAllowedToActivateConnection() {
    $('.suggest-data-connection')
      .removeClass('AknButton--apply ')
      .removeClass('AknButton--disabled')
      .removeClass('connection-activated')
      .addClass('AknButton--slateGrey')
      .addClass('activate-connection')
      .html(__(this.config.token_save_pre_activation_title));
  }

  /**
   * Makes the button green with text "Activated" so the user knows that
   * connection to PIM.ai is already active.
   */
  private buttonDisallowedToActivateConnection() {
    $('.suggest-data-connection')
      .removeClass('AknButton--slateGrey')
      .removeClass('activate-connection')
      .addClass('AknButton--apply ')
      .addClass('AknButton--disabled')
      .addClass('connection-activated')
      .html(__(this.config.token_save_post_activation_title));
  }
}

export = EditView;