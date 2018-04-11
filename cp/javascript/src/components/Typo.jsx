import React, {Component} from 'react';
import {Card, CardBody, CardText} from 'reactstrap'

export default class Typo extends Component {

    state = {
      show: this.props.show
    };

    render() {
        const {typo} = this.props;

        const display = this.state.show ? "d-block" : "d-none";

        return (
            <Card className={display}>
                <CardHeader>
                    Опечатка #{typo.id}
                </CardHeader>

                <CardBody>
                    <CardTitle><del>{typo.original}</del> -> {typo.corrected}</CardTitle>
                    <CardText>{typo.context}</CardText>

                    <a href="#" className="btn btn-primary">Исправить</a>
                    <a href="#" className="btn btn-danger">Отклонить</a>

                    <CardFooter>{typo.comment}</CardFooter>
                </CardBody>
            </Card>
        );
    }

}