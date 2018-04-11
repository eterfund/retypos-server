import React, {Component} from 'react';
import Typo from "./Typo/index";

export default class TypoList extends Component {
    state = {
        currentTypo: 0
    };

    render() {

        const {typos} = this.props;

        const typoCards = typos.map((typo, index) =>
            <Typo key={typo.id} typo={typos[this.state.currentTypo]}
                  show={this.state.currentTypo === index}/>
        );

        return (
            <div>
                {typoCards}
            </div>
        )
    }
}