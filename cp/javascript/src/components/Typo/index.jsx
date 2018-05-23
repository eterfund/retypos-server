import React, {Component} from 'react';
import {Card, CardHeader, CardTitle, CardFooter, CardBody, CardText, Tooltip} from 'reactstrap'

import './style.css'

export default class Typo extends Component {

    constructor(props) {
        super(props);

        this.toggleDeclineTooltip = this.toggleDeclineTooltip.bind(this);
        this.toggleAcceptTooltip = this.toggleAcceptTooltip.bind(this);

        this.typo = props.typo;

        this.state = {
            acceptTooltipOpen: false,
            declineTooltipOpen: false,
            textHighlighted: false,
        };
    }

    /**
     * Управляет отображением всплывающей подсказки для
     * кнопки принятия исправления.
     */
    toggleAcceptTooltip() {
        this.setState({
            acceptTooltipOpen: !this.state.acceptTooltipOpen,
            declineTooltipOpen: false
        })
    }

    /**
     * Управляет отображением всплывающей подсказки для
     * кнопки отклонения исправления.
     */
    toggleDeclineTooltip() {
        this.setState({
            acceptTooltipOpen: false,
            declineTooltipOpen: !this.state.declineTooltipOpen
        })
    }

    _escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };

        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    /**
     * Выделяет опечатку в контексте
     */
    _highlightTypoInContext() {
        const original = this._escapeHtml(this.typo.originalText);
        const corrected = this._escapeHtml(this.typo.correctedText);
        const context = this._escapeHtml(this.typo.context);

        // Экранируем символы, которые мешают использовать регулярные выражения
        const escapedTypoString = original.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");

        let regex = new RegExp(escapedTypoString, "g");

        this.typo.context = context.replace(regex,
            `<span class="typo-correction">
                <del>${original}</del> -> 
                <span class="text-danger">${corrected}</span>
             </span>`);
    }

    render() {
        const typo = this.typo;
        const {acceptCallback, declineCallback, show} = this.props;

        const display = show ? "d-block" : "d-none";
        const textColor = "text-white";
        const backgroundColor = "bg-primary";

        const className = `TypoCard text-center ${display} ${backgroundColor} ${textColor}`;

        if (show) {
            console.log("Render typo #" + typo.id);
        } else {
            console.log("Render hidden typo #" + typo.id);
        }

        if (!this.state.textHighlighted) {
            this._highlightTypoInContext();
            this.state.textHighlighted = true;
        }

        return (
            <Card className={className}>
                <CardHeader>
                    Опечатка #{typo.id}
                    <span id="typo-id">
                        <a href={typo.link} target="_blank">Ссылка на текст</a>
                    </span>
                </CardHeader>

                <CardBody>
                    <CardTitle><del>{typo.originalText}</del> -> {typo.correctedText}</CardTitle>

                    <CardText dangerouslySetInnerHTML={{__html: this.typo.context}} />

                    <div className="card-buttons">
                        <div className="buttons-wrapper">
                            <button id="acceptTypo" className="accept-button btn btn-warning" onClick={acceptCallback}>Исправить</button>
                            <Tooltip placement="left" isOpen={this.state.acceptTooltipOpen}
                                     target="acceptTypo" toggle={this.toggleAcceptTooltip}>
                                Опечатка будет автоматически исправлена
                            </Tooltip>
                            <button id="declineTypo" className="decline-button btn btn-danger" onClick={declineCallback}>Отклонить</button>
                            <Tooltip placement="right" isOpen={this.state.declineTooltipOpen}
                                     target="declineTypo" toggle={this.toggleDeclineTooltip}>
                                Опечатка не будет исправлена автоматически
                            </Tooltip>
                        </div>
                    </div>
                </CardBody>
                <CardFooter>
                    <p>Тут должен отображаться комментарий</p>
                    Добавлена <small>{typo.date}</small>
                </CardFooter>
            </Card>
        );
    }

}