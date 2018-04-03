import React, {Component} from 'react'
import Site from "./Site";

export default class SiteList extends Component {


    render() {
        const {sites} = this.props;

        const siteElements = sites.map(site =>
          <li key={site.id}><Site site={site}/></li>
        );

        return (
            <div className="site-list">
                <h1>Список сайтов, за которые вы отвечаете:</h1>
                <ul>
                    {siteElements}
                </ul>
            </div>
        )
    }
}