import { TextareaControl } from '@wordpress/components';

export const TextArea = ({ attributes, index, setAttributes }) => {
    return (
        <TextareaControl
            __nextHasNoMarginBottom
            label={index}
            value={attributes}
            rows="8"
            className="form-control form-textarea"
            onChange={(newValue) => setAttributes({ [index]: newValue })}
        />
    );
};