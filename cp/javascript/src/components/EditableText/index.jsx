import React, {Component} from 'react';
import FaCheckCircle from 'react-icons/lib/fa/check-circle';
import FaTimesCircle from 'react-icons/lib/fa/times-circle';

import './style.css'

/**
 * Текст, который возможно редактировать
 */
export default class EditableText extends Component {

    constructor(props) {
        super(props);

        // Колбэки
        this.onTextChanged = props.onTextChanged;
        this.onTextSaved = props.onTextSaved;

        this.text = props.text;

        this.state = {
            isEditable: false
        }
    }

    render() {
        if (this.state.isEditable) {
            return (
                <div className="text-editable-wrapper">
                    <input className="text-editable"
                           type="text" defaultValue={this.text} />

                    <div className="button-wrapper">
                        <FaCheckCircle className="text-editable-button text-editable-save"
                                       onClick={this.finishEditing.bind(this, true)} />
                        <FaTimesCircle className="text-editable-button text-editable-cancel"
                                       onClick={this.finishEditing.bind(this, false)} />
                    </div>
                </div>
            )
        }

        return (
          <span onClick={this.enableEditing.bind(this)}>{this.text}</span>
        );
    }

    enableEditing() {
        this.setState({
            isEditable: true
        });
    }

    /**
     * Завершает редактирование элемента.
     * Если значение параметра success - true,
     * то вызывает onTextSaved. Выключает режим редактирования.
     *
     */
    finishEditing(success, element) {
        if (success) {
            this.text = $("input.text-editable").val();

            if (this.onTextSaved) {
                this.onTextSaved(this.text);
            }
        }

        this.setState({
           isEditable: false
        });
    }
}