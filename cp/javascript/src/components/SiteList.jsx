import React from 'react'
import {Nav, NavItem, NavLink, TabContent, TabPane, Alert} from "reactstrap";
import TypoList from "./TypoList";

export default class SiteList extends React.Component {

    constructor(props) {
        super(props);

        this.sites = this.props.sites;

        this.state = {
            activeTab: 0,
            error: false,
        };

        this.typos = [];

        this.loadSiteTypos(this.state.activeTab, () =>
            this.forceUpdate()
        );
    }

    toggle = (tab) => {
        if (this.state.activeTab !== tab) {
            /* Обновляем стейт только после загрузки опечаток */
            this.loadSiteTypos(tab, () => {
                this.state.activeTab = tab;
                this.forceUpdate();
            })
        }
    };

    loadSiteTypos(siteId, done) {
        $.ajax({
            url: window.baseUrl + "/users/typos/getSiteTypos?siteId=" + this.sites[siteId],
        }).done((typos) => {
            this.typos = typos;

            if (done) {
                done();
            }
        }).fail((error) => {
            console.log(error);
            this.state.error = true;

            if (done) {
                done();
            }
        });
    }

    render() {
        const tabItems = this.sites.map((site, index) =>
            <NavItem key={index}>
                <NavLink className={this.state.activeTab === index ? "active" : ""}
                         onClick={() => { this.toggle(index) }}>
                    {site.name}
                </NavLink>
            </NavItem>
        );

        const tabContents = this.sites.map((site, index) => {
            // Если была ошибка загрузки, то error = true,
            // тогда вместо контента покажем ошибку загрузки
            if (index === this.state.activeTab && this.state.error) {
                return (
                    <Alert key={index} color="danger">
                        <h4 className="alert-heading">
                            Произошла ошибка загрузки, попробуйте позже
                        </h4>
                        <p>
                            При загрузке опечаток для сайта <strong>{site.name}</strong> произошла
                            ошибка. Попробуйте позже или напишите в службу поддержки
                            support@etersoft.ru.
                        </p>
                    </Alert>
                );
            }

            return(
                <TabPane key={index} tabId={index}>
                    <TypoList typos={this.typos}/>
                </TabPane>
            );
        });

        return (
            <div>
                <Nav pills fill>
                    {tabItems}
                </Nav>
                <TabContent activeTab={this.state.activeTab}>
                    {tabContents}
                </TabContent>
            </div>
        )
    }
}