import React, {Component} from 'react';
import {Card, CardHeader, CardTitle, CardFooter, CardBody, CardText} from 'reactstrap'

import './style.css'

export default class Typo extends Component {

    state = {
      show: this.props.show
    };

    render() {
        const {typo, acceptCallback, declineCallback} = this.props;

        const display = this.state.show ? "d-block" : "d-none";
        const textColor = "text-white";
        const backgroundColor = "bg-primary";

        const className = `TypoCard text-center ${display} ${backgroundColor} ${textColor}`;

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
                    <CardText>{typo.context}</CardText>

                    <div className="card-buttons">
                        <div className="buttons-wrapper">
                            <button className="accept-button btn btn-warning" onClick={acceptCallback}>Исправить</button>
                            <button className="decline-button btn btn-danger" onClick={declineCallback}>Отклонить</button>
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