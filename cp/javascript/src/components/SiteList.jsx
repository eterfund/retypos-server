import React from 'react'
import {Nav, NavItem, NavLink, TabContent, TabPane, Alert, Badge} from "reactstrap";
import TypoList from "./TypoList/";
import FaRefresh from 'react-icons/lib/fa/refresh';
import TopBarProgress from 'react-topbar-progress-indicator';

const alertify = require("alertify.js");

export default class SiteList extends React.Component {
    constructor(props) {
        super(props);

        this.sites = this.props.sites;
        this.state = {
            activeTab: 0,
            error: false,
            refreshing: true
        };

        this.typos = [];

        // Настройка прогресс бара
        TopBarProgress.config({
            barColors: {
                "0": "#ffc107",
                "1.0": "#ffc107",
            }
        });

        this.updateTab();
    }

    /**
     * Обновляет содержимое данной вкладки или текущей активной вкладки,
     * если параметр не указан. Если указанная вкладка не является активной,
     * то делает её активной.
     *
     * @param tab Если указан, то обновляет содержимое данной вкладки
     */
    updateTab = (tab) => {
        this.setState({
           refreshing: true
        });

        tab = tab === undefined ? this.state.activeTab : tab;

        this.loadSiteTypos(tab, () => {
                alertify.success("Опечатки обновлены");

                this.setState({
                    refreshing: false,
                    activeTab: tab
                });
            }
        );
    }

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
                         onClick={() => { this.updateTab(index) }}>
                    {site.name}

                    <Badge id={site.id + "-typos-count"} className={"typos-count"}
                           hidden={this.state.activeTab !== index}>
                        {this.typos.length}
                    </Badge>
                </NavLink>
                {this.state.activeTab === index &&
                    <FaRefresh className="refresh-site" title="Обновить"
                               onClick={ () => { this.updateTab() } } />}
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
                {this.state.refreshing && <TopBarProgress />}
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