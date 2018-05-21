import React, {Component} from 'react';
import Typo from "./Typo/index";

export default class TypoList extends Component {
    state = {
        currentTypo: 0,
        siteId: 0,
    };

    /**
     * Одобряет предложеное исправление опечатки
     * и вносит соответствующее исправление в текст.
     *
     * @param typoId Идентификатор опечатки
     */
    acceptCorrection(typoId) {
        this._setTypoStatus(1, typoId, this.state.siteId, () => {
            this.state.currentTypo++;
            this.forceUpdate();
        });
    }

    /**
     * Отклоняет исправление опечатки.
     * Опечатка не исправляется автоматически.
     *
     * @param typoId Идентификатор опечатки.
     */
    declineCorrection(typoId) {
        this._setTypoStatus(0, typoId, this.state.siteId, () => {
            this.state.currentTypo++;
            this.forceUpdate();
        });
    }

    /**
     * Обновляет статус опечатки, в случае, если
     * status true, то данная опечатка автоматически исправляется,
     * если false, то данная опечатка помечается как решенная, но
     * изменения в текст статьи не вносятся.
     *
     * @param status    Новый статус опечатки
     * @param typoId    Идентификатор опечатки
     * @param siteId    Идентификатор сайта, на котором найдена опечатка
     * @param then      Колбэк функция
     */
    _setTypoStatus(status, typoId, siteId, then) {
        $.ajax({
            url: `${window.baseUrl}users/typos/setTypoStatus/${typoId}/${siteId}/${status}`,
        }).done(() => {
            alert("Status changed");
            then();
        }).fail((error) => {
            alert("Status change error");
            console.error(error.message);
        });
    }

    render() {

        const {typos} = this.props;

        this.state.siteId = this.props.siteId;

        console.log("Render typolist for site " + this.state.siteId);

        const typoCards = typos.map((typo, index) =>
            <Typo key={typo.id} typo={typo}
                  show={this.state.currentTypo === index}
                  acceptCallback={this.acceptCorrection.bind(this, typo.id)}
                  declineCallback={this.declineCorrection.bind(this, typo.id)}/>
        );

        console.log(typoCards);

        return (
            <div>
                {typoCards}
            </div>
        )
    }
}