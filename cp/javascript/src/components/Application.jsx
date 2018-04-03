import React from 'react';
import Typo from './Typo'

export default class Application extends Component {
    render() {
        return (
            <div>
                <h1>Управление опечатками</h1>
                <Typo/>
                <div className="controlPanel">
                    <button className="accept">Принять исправление</button>
                    <button className="decline">Отклонить исправление</button>
                </div>
            </div>
        )
    }
}