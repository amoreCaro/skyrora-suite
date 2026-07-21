import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
    PanelBody,
    ColorPalette,
    TextControl
} from '@wordpress/components';
import './editor.css';

const Edit = ({ attributes, setAttributes }) => {
    const { backgroundColor, height } = attributes;

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Background and Height Settings', 'custom-heading')}>
                    <ColorPalette
                        label={__('Background Color', 'custom-heading')}
                        value={backgroundColor}
                        onChange={(color) => setAttributes({ backgroundColor: color })}
                    />
                    <TextControl
                        label={__('Height (px)', 'custom-heading')}
                        type="number"
                        value={height.replace('px', '')}
                        onChange={(height) => setAttributes({ height: `${height}px` })}
                    />
                </PanelBody>
            </InspectorControls>

            <div {...useBlockProps()} className="wp-block wp-spacer" style={{
                backgroundColor: backgroundColor,
                height: height,
            }}>
             
            </div>
        </>
    );
};

export default Edit;
