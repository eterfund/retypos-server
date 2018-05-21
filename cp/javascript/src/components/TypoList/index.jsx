import React, {Component} from 'react';
import {Card, CardHeader, CardBody, CardText} from 'reactstrap'

import Typo from "../Typo/";

import './style.css'

const alertify = require("alertify.js");

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
            alertify.success(`<p>Опечатка ${typoId} была подтверждена.</p>
                <p>Исправления применены к тексту, содержащему опечатку.</p>`);
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
            alertify.success(`Опечатка ${typoId} была отклонена`);
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
            then();
        }).fail((error) => {
            alertify.fail("Ошибка исправления опечатки, попробуйте позже");
            console.error(error.message);
        });
    }

    static _displayEmptyMessage() {
        return (
            <Card className="text-center" inverse color="danger">
                <CardHeader>
                    Список опечаток для сайта пуст
                </CardHeader>
                <CardBody>
                    <CardText>
                        В данный момент нет неисправленых опечаток.<br />
                        Когда новые опечатки будут отправлены, вы получите
                        уведомление на почту.
                    </CardText>
                </CardBody>
            </Card>
        )
    }

    render() {

        const {typos} = this.props;

        this.state.siteId = this.props.siteId;

        console.log("Render typolist for site " + this.state.siteId);

        if (typos.length === 0 || this.state.currentTypo >= typos.length) {
            return TypoList._displayEmptyMessage();
        }

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