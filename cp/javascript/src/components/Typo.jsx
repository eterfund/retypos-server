import React, {Component} from 'react';

export default class Typo extends Component {

    render() {
        const {article} = this.props;

        return (
            <div>
                <h1>Опечатка #{article.id}</h1>
                <div className="typo-body">
                    <div className="context">
                        {article.context}
                    </div>
                    <div className="original-text">{article.original}</div>
                    <div className="corrected-text">{article.corrected}</div>
                    <div className="comment">{article.comment}</div>
                </div>
            </div>
        );
    }

}