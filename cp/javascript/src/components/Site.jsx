import React, {Component} from 'react';

export default class Site extends Component {

    render() {
        const {site} = this.props;

        return (
            <div className="site">
                <h2>{site.name}</h2>
                <p className="date-container">Добавлено: <span className="date">{site.date}</span></p>
                <button className="siteTyposButton">Перейти к опечаткам данного сайта</button>
            </div>
        )
    }

}