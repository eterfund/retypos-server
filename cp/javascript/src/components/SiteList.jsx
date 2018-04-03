import React from 'react'
import {Nav, NavItem, NavLink, TabContent, TabPane} from "reactstrap";
import Site from "./Site";

export default class SiteList extends React.Component {

    sites = this.props.sites;

    state = {
        activeTab: this.sites[0].id
    };

    toggle = (tab) => {
        console.log("Toggle to ", tab);
        if (this.state.activeTab !== tab) {
            this.setState({
                activeTab: tab
            })
        }
    };

    render() {
        const tabItems = this.sites.map(site =>
            <NavItem key={site.id}>
                <NavLink className={this.state.activeTab === site.id ? "active" : ""}
                         onClick={() => { this.toggle(site.id) }}>
                    {site.name}
                </NavLink>
            </NavItem>
        );

        const tabContents = this.sites.map(site =>
            <TabPane tabId={site.id}>
                <Site site={site} />
            </TabPane>
        );

        return (
            <div>
                <Nav tabs>
                    {tabItems}
                </Nav>
                <TabContent activeTab={this.state.activeTab}>
                    {tabContents}
                </TabContent>
            </div>
        )
    }
}