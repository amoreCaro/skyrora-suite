import { TextControl } from '@wordpress/components';

export const Text = ({ value, index, setAttributes }) => {
    return (
        <TextControl
            label={index}
            value={value}
            className="form-control form-text"
            onChange={(newValue) => setAttributes({ [index]: newValue })}
        />
    );
};