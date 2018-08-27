import React, {Component} from 'react';
import {Card, CardHeader, CardBody, CardText} from 'reactstrap'

import Typo from "../Typo/";

import './style.css'

/**
 * Alertify object
 * @type {Alertify}
 */
const alertify = require("alertify.js");

export default class TypoList extends Component {
    state = {
        siteId: 0,
        resolvedTypos: []
    };

    /**
     * Одобряет исправление опечатки
     * и вносит соответствующее исправление в текст.
     *
     * @param typoId Идентификатор опечатки
     * @param corrected Финальный вариант исправления
     */
    acceptCorrection(typoId, corrected) {
        this._setTypoStatus(1, typoId, this.state.siteId, corrected)
            .done((response) => {
                if (response.error === false) {
                    alertify.success(`<p>Опечатка ${typoId} была подтверждена.</p>
                        <p>Исправления применены к тексту, содержащему опечатку.</p>`);

                    this.state.resolvedTypos.push(typoId)
                    this._decrementSiteTyposCount();
                    this.forceUpdate();
                    return true;
                }

                alertify.error(response.message);
                return false;
            })
            .fail(() => {
                alertify.error("Ошибка исправления опечатки, попробуйте позже");
                return false;
            })
    }

    /**
     * Отклоняет исправление опечатки.
     * Опечатка не исправляется автоматически.
     *
     * @param typoId Идентификатор опечатки.
     */
    declineCorrection(typoId) {
        this._setTypoStatus(0, typoId, this.state.siteId)
            .done(() => {
                alertify.success(`Опечатка ${typoId} была отклонена`);
                
                this.state.resolvedTypos.push(typoId);
                this._decrementSiteTyposCount();
                this.forceUpdate();

                return true;
            })
            .fail(() => {
                alertify.error("Ошибка исправления опечатки, попробуйте позже");
                return false;
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
     * @param corrected Исправленный вариант
     */
    _setTypoStatus(status, typoId, siteId, corrected) {
        return $.ajax({
            method: "POST",
            url: `${window.baseUrl}users/typos/setTypoStatus`,
            data: {
                accepted: status,
                typoId: typoId,
                siteId: siteId,
                corrected: corrected,
            }
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

        if (typos.length === 0 || this.state.resolvedTypos.length >= typos.length) {
            return TypoList._displayEmptyMessage();
        }

        const typoCards = typos.map((typo, index) =>
            <Typo key={typo.id} typo={typo}
                  show={!this.state.resolvedTypos.includes(typo.id)}
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

    /**
     * Уменьшает счетчик опечаток сайта
     * @private
     */
    _decrementSiteTyposCount() {
        const value = parseInt($(`#${this.state.siteId}-typos-count`).text());
        $(`#${this.state.siteId}-typos-count`).text(value-1);
    }
}