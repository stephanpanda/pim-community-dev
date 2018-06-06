import * as React from 'react';
import { connect } from "react-redux";
import __ from 'akeneoenrichedentity/tools/translator';
import { State } from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import { toggleSidebar, updateCurrentTab } from 'akeneoenrichedentity/application/event/sidebar';
import { Tab } from "akeneoenrichedentity/application/reducer/sidebar";

interface SidebarState {
  tabs: Tab[];
  currentTab: string;
  isCollapsed: boolean;
}

interface SidebarDispatch {
  events: {
    toggleSidebar: (isCollapsed: boolean) => void,
    updateCurrentTab: (tabCode: string) => void
  }
}

interface SidebarProps extends SidebarState, SidebarDispatch {}

class Sidebar extends React.Component<SidebarProps> {
  props: SidebarProps;

  toggleSidebar = () => {
    this.props.events.toggleSidebar(!this.props.isCollapsed);
  };

  updateCurrentTab = (event: any) => {
    this.props.events.updateCurrentTab(event.target.attributes.getNamedItem('data-tab').value);
  };

  render(): JSX.Element | JSX.Element[] {
    const colapsedClass = (this.props.isCollapsed) ? 'AknColumn--collapsed' : '';

    return (
      <div className={"AknColumn " + colapsedClass}>
        <div className="AknColumn-inner column-inner">
          <div className="AknColumn-innerTop">
            <div className="AknColumn-block">
              <div className="AknColumn-title">{ __('pim_enriched_entity.enriched_entity.title') }</div>
              {this.props.tabs.map((tab: any) => {
                const activeClass = (this.props.currentTab === tab.code) ? 'AknColumn-navigationLink--active' : '';

                return (
                  <div
                    key={ tab.code }
                    className={"AknColumn-navigationLink column-navigation-link " + activeClass }
                    data-tab={ tab.code }
                    onClick={this.updateCurrentTab}
                  >
                    { tab.label }
                  </div>
                );
              })}
            </div>
          </div>
          <div className="AknColumn-innerBottom"></div>
        </div>
        <div className="AknColumn-collapseButton" onClick={this.toggleSidebar}></div>
      </div>
    );
  }
}

export default connect((state: State): SidebarState => {
  return {
    tabs: state.sidebar.tabs,
    currentTab: state.sidebar.currentTab,
    isCollapsed: state.sidebar.isCollapsed
  }
}, (dispatch: any): SidebarDispatch => {
  return {
    events: {
      toggleSidebar: (isCollapsed: boolean) => {
        dispatch(toggleSidebar(isCollapsed))
      },
      updateCurrentTab: (tabCode: string) => {
        dispatch(updateCurrentTab(tabCode))
      }
    }
  }
})(Sidebar);
