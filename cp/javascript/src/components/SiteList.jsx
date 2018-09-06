import React from 'react'
import {Nav, NavItem, NavLink, TabContent, TabPane, Alert, Badge} from "reactstrap";
import TypoList from "./TypoList/";
import FaRefresh from 'react-icons/lib/fa/refresh';


export default class SiteList extends React.Component {

    constructor(props) {
        super(props);

        this.sites = this.props.sites;
        this.state = {
            activeTab: 0,
            error: false,
        };

        this.typos = [];

        this.updateTyposForActiveSite();
    }

    updateTyposForActiveSite = () => {
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
            url: `${window.baseUrl}/users/typos/getSiteTypos/${this.sites[siteId].id}`,
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

                    <Badge id={site.id + "-typos-count"} className={"typos-count"}
                           hidden={this.state.activeTab !== index}>
                        {this.typos.length}
                    </Badge>
                </NavLink>
                {this.state.activeTab === index ?
                    <FaRefresh className="refresh-site" title="Обновить" onClick={this.updateTyposForActiveSite} /> :
                    null}
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

            if (this.state.activeTab === index) {
                return (
                    <TabPane key={index} tabId={index}>
                        <TypoList siteId={site.id} typos={this.typos} />
                    </TabPane>
                );
            } else { // Если не активная вкладка - то не рендерим содержимое
                return (
                    <TabPane key={index} tabId={index}>
                    </TabPane>
                );
            }
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