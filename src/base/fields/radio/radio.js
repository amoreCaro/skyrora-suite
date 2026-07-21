import { RadioControl } from '@wordpress/components';
import { useState, useEffect } from 'react';

export const Radio = ({ attributes, label, values, setAttributes }) => {

    const defaultValue = values[0].value;
    const [ option, setOption ] = useState( defaultValue );

    const onSelectedRadio = (newValue) => {
        setAttributes({ [attributes]: newValue });
    };

    useEffect(() => {
        setOption(option);
        onSelectedRadio(defaultValue);
    }, []);

    return (
        <div className="form-item">
            <RadioControl
                label={label}
                selected={ option }
                options={values}
                onChange={(value) => {
                    setOption(value);
                    onSelectedRadio(value);
                }}
            />
        </div>
    );
};