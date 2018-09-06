import * as React from 'react';
import {connect} from 'react-redux';
import {EditState as State} from 'akeneoenrichedentity/application/reducer/record/edit';
import Sidebar from 'akeneoenrichedentity/application/component/app/sidebar';
import {Tab} from 'akeneoenrichedentity/application/reducer/sidebar';
import sidebarProvider from 'akeneoenrichedentity/application/configuration/sidebar';
import Breadcrumb from 'akeneoenrichedentity/application/component/app/breadcrumb';
import Image from 'akeneoenrichedentity/application/component/app/image';
import __ from 'akeneoenrichedentity/tools/translator';
import PimView from 'akeneoenrichedentity/infrastructure/component/pim-view';
import Record, {denormalizeRecord} from 'akeneoenrichedentity/domain/model/record/record';
import {saveRecord, deleteRecord, recordImageUpdated} from 'akeneoenrichedentity/application/action/record/edit';
import EditState from 'akeneoenrichedentity/application/component/app/edit-state';
const securityContext = require('pim/security-context');
import ImageModel from 'akeneoenrichedentity/domain/model/image';
import Locale from 'akeneoenrichedentity/domain/model/locale';
import {catalogLocaleChanged} from 'akeneoenrichedentity/domain/event/user';
import LocaleSwitcher from 'akeneoenrichedentity/application/component/app/locale-switcher';

interface StateProps {
  sidebar: {
    tabs: Tab[];
    currentTab: string;
  };
  form: {
    isDirty: boolean;
  };
  context: {
    locale: string;
  };
  acls: {
    create: boolean;
    delete: boolean;
  };
  record: Record;
  structure: {
    locales: Locale[];
  };
}

interface DispatchProps {
  events: {
    onSaveEditForm: () => void;
    onLocaleChanged: (locale: Locale) => void;
    onImageUpdated: (image: ImageModel | null) => void;
    onDelete: (record: Record) => void;
  };
}

interface EditProps extends StateProps, DispatchProps {}

class RecordEditView extends React.Component<EditProps> {
  private tabView: JSX.Element;

  public props: EditProps;

  constructor(props: EditProps) {
    super(props);

    this.updateTabView(props.sidebar.currentTab);
  }

  componentDidUpdate(nextProps: EditProps) {
    if (JSON.stringify(this.props.sidebar.currentTab) !== JSON.stringify(nextProps.sidebar.currentTab)) {
      this.updateTabView(this.props.sidebar.currentTab);
    }
  }

  private updateTabView = async (currentTab: string): Promise<void> => {
    const TabView = await sidebarProvider.getView('akeneo_enriched_entities_record_edit', currentTab);

    this.tabView = <TabView code={currentTab} />;
    this.forceUpdate();
  };

  private onClickDelete = () => {
    if (confirm(__('pim_enriched_entity.record.module.delete.confirm'))) {
      this.props.events.onDelete(this.props.record);
    }
  };

  private getSecondaryActions = (canDelete: boolean): JSX.Element | JSX.Element[] | null => {
    if (canDelete) {
      return (
        <div className="AknSecondaryActions AknDropdown AknButtonList-item">
          <div className="AknSecondaryActions-button dropdown-button" data-toggle="dropdown" />
          <div className="AknDropdown-menu AknDropdown-menu--right">
            <div className="AknDropdown-menuTitle">{__('pim_datagrid.actions.other')}</div>
            <div>
              <button className="AknDropdown-menuLink" onClick={this.onClickDelete}>
                {__('pim_enriched_entity.record.module.delete.button')}
              </button>
            </div>
          </div>
        </div>
      );
    }

    return null;
  };

  render(): JSX.Element | JSX.Element[] {
    const editState = this.props.form.isDirty ? <EditState /> : '';
    const label = this.props.record.getLabel(this.props.context.locale);

    return (
      <div className="AknDefault-contentWithColumn">
        <div className="AknDefault-thirdColumnContainer">
          <div className="AknDefault-thirdColumn" />
        </div>
        <div className="AknDefault-contentWithBottom">
          <div className="AknDefault-mainContent" data-tab={this.props.sidebar.currentTab}>
            <header className="AknTitleContainer">
              <div className="AknTitleContainer-line">
                <Image
                  alt={__('pim_enriched_entity.record.img', {'{{ label }}': label})}
                  image={this.props.record.getImage()}
                  onImageChange={this.props.events.onImageUpdated}
                />
                <div className="AknTitleContainer-mainContainer">
                  <div>
                    <div className="AknTitleContainer-line">
                      <div className="AknTitleContainer-breadcrumbs">
                        <Breadcrumb
                          items={[
                            {
                              action: {
                                type: 'redirect',
                                route: 'akeneo_enriched_entities_enriched_entity_index',
                              },
                              label: __('pim_enriched_entity.enriched_entity.title'),
                            },
                            {
                              action: {
                                type: 'redirect',
                                route: 'akeneo_enriched_entities_enriched_entity_edit',
                                parameters: {
                                  identifier: this.props.record.getEnrichedEntityIdentifier().stringValue(),
                                  tab: 'record',
                                },
                              },
                              label: __('pim_enriched_entity.record.title'),
                            },
                          ]}
                        />
                      </div>
                      <div className="AknTitleContainer-buttonsContainer">
                        <div className="user-menu">
                          <PimView
                            className="AknTitleContainer-userMenu"
                            viewName="pim-enriched-entity-index-user-navigation"
                          />
                        </div>
                        <div className="AknButtonList">
                          {this.getSecondaryActions(this.props.acls.delete)}
                          <div className="AknTitleContainer-rightButton">
                            <button className="AknButton AknButton--apply" onClick={this.props.events.onSaveEditForm}>
                              {__('pim_enriched_entity.record.button.save')}
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div className="AknTitleContainer-line">
                      <div className="AknTitleContainer-title">{label}</div>
                      {editState}
                    </div>
                  </div>
                  <div>
                    <div className="AknTitleContainer-line">
                      <div className="AknTitleContainer-context AknButtonList">
                        <LocaleSwitcher
                          localeCode={this.props.context.locale}
                          locales={this.props.structure.locales}
                          onLocaleChange={this.props.events.onLocaleChanged}
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </header>
            <div className="content">{this.tabView}</div>
          </div>
        </div>
        <Sidebar />
      </div>
    );
  }
}

export default connect(
  (state: State): StateProps => {
    const record = denormalizeRecord(state.form.data);
    const tabs = undefined === state.sidebar.tabs ? [] : state.sidebar.tabs;
    const currentTab = undefined === state.sidebar.currentTab ? '' : state.sidebar.currentTab;
    const locale = undefined === state.user || undefined === state.user.catalogLocale ? '' : state.user.catalogLocale;

    return {
      sidebar: {
        tabs,
        currentTab,
      },
      form: {
        isDirty: state.form.state.isDirty,
      },
      context: {
        locale,
      },
      record,
      structure: {
        locales: state.structure.locales,
      },
      acls: {
        create: securityContext.isGranted('akeneo_enrichedentity_record_create'),
        delete: securityContext.isGranted('akeneo_enrichedentity_record_delete'),
      },
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onSaveEditForm: () => {
          dispatch(saveRecord());
        },
        onLocaleChanged: (locale: Locale) => {
          dispatch(catalogLocaleChanged(locale.code));
        },
        onImageUpdated: (image: ImageModel | null) => {
          dispatch(recordImageUpdated(image));
        },
        onDelete: (record: Record) => {
          dispatch(deleteRecord(record));
        },
      },
    };
  }
)(RecordEditView);
