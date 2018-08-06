import React, {Component} from 'react';
import {Card, CardHeader, CardTitle, CardFooter, CardBody, CardText, Tooltip} from 'reactstrap'
import EditableText from "../EditableText";

import './style.css'


export default class Typo extends Component {

    constructor(props) {
        super(props);

        this.toggleDeclineTooltip = this.toggleDeclineTooltip.bind(this);
        this.toggleAcceptTooltip = this.toggleAcceptTooltip.bind(this);

        this.typo = props.typo;
        this.acceptCallback = props.acceptCallback.bind(this);
        this.declineCallback = props.declineCallback.bind(this);

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

    onCorrectedTextUpdated = (corrected) => {
        this.typo.correctedText = corrected;
    };

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
            return null;
        }

        if (!this.state.textHighlighted) {
            this._highlightTypoInContext();
            this.state.textHighlighted = true;
        }   

        return (
            <Card id={`typo-${typo.id}`} className={className}>
                <CardHeader>
                    Опечатка #{typo.id}
                    <span id="typo-id">
                        <a href={typo.link} target="_blank">Ссылка на текст</a>
                    </span>
                </CardHeader>

                <CardBody>
                    <CardTitle>
                        <del>{typo.originalText}</del> ->
                        <EditableText text={typo.correctedText} onTextSaved={this.onCorrectedTextUpdated}/>
                    </CardTitle>

                    <CardText dangerouslySetInnerHTML={{__html: typo.context}} />

                    <div className="card-buttons">
                        <div className="buttons-wrapper">
                            <button id="acceptTypo" className="accept-button btn btn-warning" onClick={this.applyCorrection}>Исправить</button>
                            <Tooltip placement="left" isOpen={this.state.acceptTooltipOpen}
                                     target="acceptTypo" toggle={this.toggleAcceptTooltip}>
                                Опечатка будет автоматически исправлена
                            </Tooltip>
                            <button id="declineTypo" className="decline-button btn btn-danger" onClick={this.declineCorrection}>Отклонить</button>
                            <Tooltip placement="right" isOpen={this.state.declineTooltipOpen}
                                     target="declineTypo" toggle={this.toggleDeclineTooltip}>
                                Опечатка не будет исправлена автоматически
                            </Tooltip>
                        </div>
                    </div>
                </CardBody>
                <CardFooter>
                    <p>Комментарий: "{typo.comment}"</p>
                    Добавлена <small>{typo.date}</small>
                </CardFooter>
            </Card>
        );
    }

    /**
     * Hides typo card 
     */
    hideTypoCard(completeFunc) {
        $(`#typo-${this.typo.id}`).animate({
            marginLeft: "3000px",
            opacity: 0
        }, 500, completeFunc);
    }

    applyCorrection = () => {
        this.hideTypoCard(() => {
            this.acceptCallback(this.typo.correctedText);
        });
    };

    declineCorrection = () => {
        this.hideTypoCard(() => {
            this.declineCallback();
        });
    };
}