import React, {Component} from 'react';
import Typo from "./Typo";

export default class TypoList extends Component {
    typos = this.props.typos;

    state = {
        currentTypo: 0
    };

    render() {

        const typoCards = this.typos.map((typo, index) =>
            <Typo typo={this.typos[this.state.currentTypo]}
                  show={this.state.currentTypo === index}/>
        );

        return <div>
            {typoCards}
        </div>
    }
}