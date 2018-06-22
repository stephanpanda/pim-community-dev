import * as React from 'react';
import {connect} from 'react-redux';
import {State} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import Sidebar from 'akeneoenrichedentity/application/component/app/sidebar';
import {Tab} from 'akeneoenrichedentity/application/reducer/sidebar';
import editTabsProvider from 'akeneoenrichedentity/application/configuration/edit-tabs';
import Breadcrumb from 'akeneoenrichedentity/application/component/app/breadcrumb';
import { getImageShowUrl } from 'akeneoenrichedentity/tools/media-url-generator';
import __ from 'akeneoenrichedentity/tools/translator';
import PimView from 'akeneoenrichedentity/infrastructure/component/pim-view';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import { saveEnrichedEntity } from 'akeneoenrichedentity/application/action/enriched-entity/edit';

interface StateProps {
  sidebar: {
    tabs: Tab[];
    currentTab: string;
  };
  context: {
    locale: string;
  }
  enrichedEntity: EnrichedEntity|null;
}

interface DispatchProps {
  events: {
    onSaveEditForm: (enrichedEntity: EnrichedEntity) => void
  }
}

interface EditProps extends StateProps, DispatchProps {}

class EnrichedEntityEditView extends React.Component<EditProps> {
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

  private async updateTabView(currentTab: string): Promise<void> {
    const TabView = await editTabsProvider.getView(currentTab);

    this.tabView = (<TabView code={currentTab} />);
    this.forceUpdate();
  }

  private saveEditForm = () => {
    if (null !== this.props.enrichedEntity) {
      this.props.events.onSaveEditForm(this.props.enrichedEntity);
    }
  };

  render(): JSX.Element | JSX.Element[] {
    return (
      <div className="AknDefault-contentWithColumn">
        <div className="AknDefault-thirdColumnContainer">
          <div className="AknDefault-thirdColumn"></div>
        </div>
        <div className="AknDefault-contentWithBottom">
          <div className="AknDefault-mainContent" data-tab={this.props.sidebar.currentTab}>
            <header className="AknTitleContainer navigation">
              <div className="AknTitleContainer-line">
                <div className="AknTitleContainer-imageContainer">
                  <img className="AknTitleContainer-image" src={getImageShowUrl(null, 'thumbnail')} />
                </div>
                <div className="AknTitleContainer-mainContainer">
                  <div>
                    <div className="AknTitleContainer-line">
                      <div className="AknTitleContainer-breadcrumbs">
                        <Breadcrumb items={[
                          {
                            action: {
                              type: 'redirect',
                              route: 'akeneo_enriched_entities_enriched_entity_edit'
                            },
                            label: __('pim_enriched_entity.enriched_entity.title')
                          }
                        ]}/>
                      </div>
                      <div className="AknTitleContainer-buttonsContainer">
                        <div className="user-menu">
                          <PimView className="AknTitleContainer-userMenu" viewName="pim-enriched-entity-index-user-navigation"/>
                        </div>
                        <div className="AknButtonList" >
                          <div className="AknTitleContainer-rightButton">
                            <button className="AknButton AknButton--apply save" onClick={this.saveEditForm}>
                              {__('pim_enriched_entity.button.save')}
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div className="AknTitleContainer-line">
                      <div className="AknTitleContainer-title">
                        {null !== this.props.enrichedEntity ? this.props.enrichedEntity.getLabel(this.props.context.locale) : ''}
                      </div>
                    </div>
                  </div>
                  <div>
                    <div className="AknTitleContainer-line">
                      <div className="AknTitleContainer-context AknButtonList"></div>
                    </div>
                    <div className="AknTitleContainer-line">
                      <div className="AknTitleContainer-meta AknButtonList"></div>
                    </div>
                  </div>
                </div>
              </div>
              <div className="AknTitleContainer-line">
                <div className="AknTitleContainer-navigation"></div>
              </div>
            </header>
            <div className="content">
              {this.tabView}
            </div>
          </div>
        </div>
        <Sidebar />
      </div>
    );
  }
}

export default connect((state: State): StateProps => {
  const enrichedEntity = undefined === state.enrichedEntity ? null : state.enrichedEntity;
  const tabs = undefined === state.sidebar.tabs ? [] : state.sidebar.tabs;
  const currentTab = undefined === state.sidebar.currentTab ? '' : state.sidebar.currentTab;
  const locale = undefined === state.user || undefined === state.user.uiLocale ? '' : state.user.uiLocale;

  return {
    sidebar: {
      tabs,
      currentTab,
    },
    context: {
      locale
    },
    enrichedEntity
  }
}, (dispatch: any): DispatchProps => {
  return {
    events: {
      onSaveEditForm: (enrichedEntity: EnrichedEntity) => {
        dispatch(saveEnrichedEntity(enrichedEntity));
      }
    }
  }
})(EnrichedEntityEditView);
