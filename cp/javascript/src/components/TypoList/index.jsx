import React, {Component} from 'react';
import {Card, CardHeader, CardBody, CardText} from 'reactstrap'
import TopBarProgress from 'react-topbar-progress-indicator';

import Typo from "../Typo/";

import './style.css'

/**
 * Alertify object
 * @type {Alertify}
 */
const alertify = require("alertify.js");

export default class TypoList extends Component {
    constructor(props) {
        super(props);

        this.state = {
            loading: false,
            siteId: this.props.siteId,
        };

        this.removeTypo = this.props.removeTypoCallback.bind(this);

        console.log("Construct(TypoList):", this);
    }

    /**
     * Одобряет исправление опечатки
     * и вносит соответствующее исправление в текст.
     *
     * @param typoId Идентификатор опечатки
     * @param corrected Финальный вариант исправления
     */
    acceptCorrection(typoId, corrected) {
        this.setState({
            loading: true
        });

        this._setTypoStatus(1, typoId, this.state.siteId, corrected)
            .done((response) => {
                if (response.error === false) {
                    alertify.success(`<p>Опечатка ${typoId} была подтверждена.</p>
                        <p>Исправления применены к тексту, содержащему опечатку.</p>`);

                    this.removeTypo(typoId);
                } else {
                    alertify.error(response.message);
                }
            })
            .fail(() => {
                alertify.error("Ошибка исправления опечатки, попробуйте позже");
            }).always(() => {
                this.setState({
                    loading: false
                });
            });
    }

    /**
     * Отклоняет исправление опечатки.
     * Опечатка не исправляется автоматически.
     *
     * @param typoId Идентификатор опечатки.
     */
    declineCorrection(typoId) {
        this.setState({
            loading: true
        });

        this._setTypoStatus(0, typoId, this.state.siteId)
            .done(() => {
                alertify.success(`Опечатка ${typoId} была отклонена`);
                this.removeTypo(typoId);
            })
            .fail(() => {
                alertify.error("Ошибка исправления опечатки, попробуйте позже");
            }).always(() => {
                this.setState({
                    loading: false
                });
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
                        В данный момент нет неисправленных опечаток.<br />
                        Когда новые сообщения об опечатках будут получены, вам будет отправлено
                        уведомление на почту.
                    </CardText>
                </CardBody>
            </Card>
        )
    }

    render() {
        console.log("Render typolist for site " + this.state.siteId);

        this.updateSiteTyposCount();

        const typos = this.props.typos;

        if (typos.length === 0) {
            return TypoList._displayEmptyMessage();
        }

        const typoCards = typos.map((typo, index) =>
            <Typo key={typo.id} typo={typo}
                  acceptCallback={this.acceptCorrection.bind(this, typo.id)}
                  declineCallback={this.declineCorrection.bind(this, typo.id)}/>
        );

        console.log(typoCards);

        return (
            <div>
                {this.state.loading && <TopBarProgress />}
                {typoCards}
            </div>
        )
    }

    /**
     * Уменьшает счетчик опечаток сайта
     * @private
     */
    updateSiteTyposCount() {
        $(`#${this.state.siteId}-typos-count`).text(this.props.typos.length);
    }
}