import React, {Component} from 'react';
import Typo from "./Typo/index";

export default class TypoList extends Component {
    state = {
        currentTypo: 0
    };

    /**
     * Одобряет предложеное исправление опечатки
     * и вносит соответствующее исправление в текст.
     *
     * @param typoId Идентификатор опечатки
     */
    acceptCorrection(typoId) {
        alert("Accept");
        this.state.currentTypo++;
        this.forceUpdate();
    }

    /**
     * Отклоняет исправление опечатки.
     * Опечатка не исправляется автоматически.
     *
     * @param typoId Идентификатор опечатки.
     */
    declineCorrection(typoId) {
        alert("Decline");
        this.state.currentTypo++;
        this.forceUpdate();
    }

    render() {

        const {typos} = this.props;

        const typoCards = typos.map((typo, index) =>
            <Typo key={typo.id} typo={typos[this.state.currentTypo]}
                  show={this.state.currentTypo === index}
                  acceptCallback={this.acceptCorrection.bind(this, typo.id)}
                  declineCallback={this.declineCorrection.bind(this, typo.id)}/>
        );

        return (
            <div>
                {typoCards}
            </div>
        )
    }
}