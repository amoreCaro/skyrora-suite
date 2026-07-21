import {
    useBlockProps,
    InspectorControls,
    PanelColorSettings,
    RichText,
    MediaUpload,
    BlockControls,
} from '@wordpress/block-editor';

import {
    PanelBody,
    TextControl,
    SelectControl,
    Button,
    ButtonGroup,
    TabPanel,
    ToolbarGroup,
    ToolbarButton,
} from '@wordpress/components';

import { __ } from '@wordpress/i18n';
import { useState } from 'react';
import './editor.css';
import { closeSmall } from '@wordpress/icons';
import { alignLeft, alignCenter, alignRight } from '@wordpress/icons';

export default function Edit({ attributes, setAttributes }) {
    const {
        imgUrl,
        imgId,
        imgLink,
        text1,
        text2,
        paddingLeft,
        paddingRight,
        paddingTop,
        paddingBottom,
        fontFamily,
        fontSize,
        lineHeight,
        color,
        backgroundColor,
        textTransform,
        fontWeight,
        textAlign,
        tabSelected,
    } = attributes;

    const blockProps = useBlockProps();
    const [showLogoInfo, setShowLogoInfo] = useState(false);
    const [isImageSelected, setIsImageSelected] = useState(false);

    const onSelectImage = (media) => {
        setAttributes({ imgUrl: media.url, imgId: media.id });
        setIsImageSelected(true);
    };

    const onRemoveImage = () => {
        setAttributes({ imgUrl: '', imgId: 0 });
        setIsImageSelected(false);
    };
    const onChangeLink = (value) => setAttributes({ imgLink: value });
    const toggleLogoCollapse = () => setShowLogoInfo(!showLogoInfo);

    const onChangeText1 = (value) => setAttributes({ text1: value });
    const onChangeText2 = (value) => setAttributes({ text2: value });

    return (
        <>
            <BlockControls>
                {imgUrl && isImageSelected && (
                    <ToolbarGroup>
                        <ToolbarButton
                            icon={closeSmall}
                            label={__('Remove Image')}
                            onClick={onRemoveImage}
                            isDestructive
                            style={{
                                padding: '0',
                                fontSize: '20px',
                                backgroundColor: 'transparent',
                                color: 'black',
                                border: 'none !important',
                                outline: 'none !important',
                            }}
                        />
                    </ToolbarGroup>
                )}
            </BlockControls>

            <InspectorControls>
                <div
                    style={{
                        width: '100%',
                    }}
                >
                    <TabPanel
                        className="my-tab-panel"
                        initialTabName={tabSelected || 'settings'}
                        onSelect={(tabName) => setAttributes({ tabSelected: tabName })}
                        tabs={[
                            { name: 'settings', title: <span className="dashicons dashicons-admin-generic" /> },
                            { name: 'styles', title: <span className="dashicons dashicons-admin-appearance" /> },
                        ]}
                    >
                        {({ name }) => (
                            <div>
                                {name === 'settings' && (
                                    <>
                                        <PanelBody title={__('Image Settings', 'your-text-domain')} initialOpen={true}>
                                            <div className="collapse">
                                                <div
                                                    className="collapse__head"
                                                    onClick={toggleLogoCollapse}
                                                    style={{
                                                        cursor: 'pointer',
                                                        display: 'flex',
                                                        justifyContent: 'space-between',
                                                        alignItems: 'center',
                                                        gap: '12px',
                                                        border: '1px solid #E1E1E1',
                                                        padding: '8px'
                                                    }}
                                                >
                                                    <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                                                        {imgUrl ? (
                                                            <img
                                                                src={imgUrl}
                                                                alt="Logo"
                                                                onClick={() => setIsImageSelected(true)}
                                                                style={{
                                                                    height: '40px',
                                                                    width: '40px',
                                                                    objectFit: 'cover',
                                                                    cursor: 'pointer',
                                                                }}
                                                            />
                                                        ) : (
                                                            <MediaUpload
                                                                onSelect={onSelectImage}
                                                                allowedTypes={['image']}
                                                                value={imgId}
                                                                render={({ open }) => (
                                                                    <div
                                                                        onClick={open}
                                                                        style={{
                                                                            height: '40px',
                                                                            width: '40px',
                                                                            display: 'flex',
                                                                            alignItems: 'center',
                                                                            justifyContent: 'center',
                                                                            fontSize: '24px',
                                                                            color: '#aaa',
                                                                            border: '2px dashed #ccc',
                                                                            cursor: 'pointer',
                                                                        }}
                                                                    >
                                                                        +
                                                                    </div>
                                                                )}
                                                            />
                                                        )}
                                                        <span>{imgUrl ? imgUrl.split('/').pop() : __('No Image')}</span>
                                                    </div>
                                                    {imgUrl && (
                                                        <Button
                                                            onClick={onRemoveImage}
                                                            isDestructive
                                                            style={{ padding: '0', fontSize: '20px' }}
                                                        >
                                                            <span className="dashicons dashicons-no-alt" style={{ color: 'black' }} />
                                                        </Button>
                                                    )}
                                                </div>

                                                {showLogoInfo && (
                                                    <div className="collapse__content" style={{ padding: '8px', border: '1px solid #E1E1E1' }}>
                                                        {imgUrl ? (
                                                            <img
                                                                src={imgUrl}
                                                                alt="Full-width Image"
                                                                style={{
                                                                    width: '100%',
                                                                    objectFit: 'cover',
                                                                    backgroundColor: '#F0F0F0',
                                                                }}
                                                            />
                                                        ) : (
                                                            <MediaUpload
                                                                onSelect={onSelectImage}
                                                                allowedTypes={['image']}
                                                                value={imgId}
                                                                render={({ open }) => (
                                                                    <div
                                                                        onClick={open}
                                                                        style={{
                                                                            width: '100%',
                                                                            height: '150px',
                                                                            backgroundColor: '#F0F0F0',
                                                                            display: 'flex',
                                                                            justifyContent: 'center',
                                                                            alignItems: 'center',
                                                                            cursor: 'pointer',
                                                                            fontSize: '48px',
                                                                            color: '#CCCCCC',
                                                                            border: '2px dashed #CCCCCC'
                                                                        }}
                                                                    >
                                                                        +
                                                                    </div>
                                                                )}
                                                            />
                                                        )}
                                                        <TextControl
                                                            label={__('Image Link')}
                                                            value={imgLink || '#'}
                                                            onChange={onChangeLink}
                                                        />
                                                    </div>
                                                )}
                                            </div>
                                        </PanelBody>

                                        <PanelBody title={__('Content Settings', 'your-text-domain')} initialOpen={true}>
                                            <TextControl
                                                label={__('Text 1')}
                                                value={text1}
                                                onChange={onChangeText1}
                                            />
                                            <TextControl
                                                label={__('Text 2')}
                                                value={text2}
                                                onChange={onChangeText2}
                                            />
                                        </PanelBody>
                                    </>
                                )}

                                {name === 'styles' && (
                                    <>
                                        <PanelColorSettings
                                            title={__('Colors', 'custom-paragraph')}
                                            initialOpen={true}
                                            colorSettings={[
                                                {
                                                    value: color,
                                                    onChange: (value) => setAttributes({ color: value }),
                                                    label: __('Text Color', 'custom-paragraph'),
                                                },
                                                {
                                                    value: backgroundColor,
                                                    onChange: (value) => setAttributes({ backgroundColor: value }),
                                                    label: __('Background Color', 'custom-paragraph'),
                                                },
                                            ]}
                                        />

                                        <PanelBody title={__('Font Settings', 'your-text-domain')} initialOpen={true}>
                                            <TextControl
                                                label={__('Font Family')}
                                                value={fontFamily}
                                                onChange={(value) => setAttributes({ fontFamily: value })}
                                            />
                                            <TextControl
                                                label={__('Font Size (px)')}
                                                type="number"
                                                value={fontSize}
                                                onChange={(value) => setAttributes({ fontSize: value })}
                                            />
                                            <TextControl
                                                label={__('Line Height (px)')}
                                                type="number"
                                                value={lineHeight}
                                                onChange={(value) => setAttributes({ lineHeight: value })}
                                            />
                                            <SelectControl
                                                label={__('Font Weight')}
                                                value={fontWeight}
                                                options={[
                                                    { label: 'Normal', value: '400' },
                                                    { label: 'Bold', value: '700' },
                                                    { label: 'Bolder', value: '900' },
                                                ]}
                                                onChange={(value) => setAttributes({ fontWeight: value })}
                                            />
                                            <SelectControl
                                                label={__('Text Transform')}
                                                value={textTransform}
                                                options={[
                                                    { label: 'None', value: 'none' },
                                                    { label: 'Uppercase', value: 'uppercase' },
                                                    { label: 'Lowercase', value: 'lowercase' },
                                                    { label: 'Capitalize', value: 'capitalize' },
                                                ]}
                                                onChange={(value) => setAttributes({ textTransform: value })}
                                            />
                                             <ButtonGroup
                                                style={{
                                                    width: '100%',
                                                    display: 'flex',
                                                    justifyContent: 'space-between',
                                                    marginBottom: '16px'
                                                }}
                                            >
                                                <Button
                                                    icon={alignLeft}
                                                    label={__('Align Left', 'app')}
                                                    isPressed={textAlign === 'left'}
                                                    onClick={() => setAttributes({ textAlign: 'left' })}
                                                    style={{ flex: 1 }}
                                                />
                                                <Button
                                                    icon={alignCenter}
                                                    label={__('Align Center', 'app')}
                                                    isPressed={textAlign === 'center'}
                                                    onClick={() => setAttributes({ textAlign: 'center' })}
                                                    style={{ flex: 1 }}
                                                />
                                                <Button
                                                    icon={alignRight}
                                                    label={__('Align Right', 'app')}
                                                    isPressed={textAlign === 'right'}
                                                    onClick={() => setAttributes({ textAlign: 'right' })}
                                                    style={{ flex: 1 }}
                                                />
                                            </ButtonGroup>
                                        </PanelBody>
                                    </>
                                )}
                            </div>
                        )}
                    </TabPanel>
                </div>

                <PanelBody title={__('Padding Settings')} initialOpen={false}>
                    <TextControl
                        label={__('Padding Left')}
                        type="number"
                        value={paddingLeft}
                        onChange={(value) => setAttributes({ paddingLeft: parseInt(value) || 0 })}
                    />
                    <TextControl
                        label={__('Padding Right')}
                        type="number"
                        value={paddingRight}
                        onChange={(value) => setAttributes({ paddingRight: parseInt(value) || 0 })}
                    />
                    <TextControl
                        label={__('Padding Top')}
                        type="number"
                        value={paddingTop}
                        onChange={(value) => setAttributes({ paddingTop: parseInt(value) || 0 })}
                    />
                    <TextControl
                        label={__('Padding Bottom')}
                        type="number"
                        value={paddingBottom}
                        onChange={(value) => setAttributes({ paddingBottom: parseInt(value) || 0 })}
                    />
                </PanelBody>
                {/* Inline style override for TabPanel tab buttons */}
                <style>{`
                    .my-tab-panel .components-tab-panel__tabs {
                        display: flex !important;
                        width: 100% !important;
                        padding: 0 !important;
                        margin: 0 !important;
                    }
                    .my-tab-panel .components-tab-panel__tabs button {
                        flex: 1 1 50% !important;
                        display: flex !important;
                        justify-content: center !important;
                        align-items: center !important;
                        height: 40px !important;
                        border-radius: 0 !important;
                        padding: 0 !important;
                    }
                `}</style>
            </InspectorControls>

            <div {...blockProps}>
                <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                    <div
                        style={{
                            backgroundColor: backgroundColor || '#181b24',
                            width: '250px',
                            paddingLeft: `${paddingLeft}px`,
                            paddingRight: `${paddingRight}px`,
                            paddingTop: `${paddingTop}px`,
                            paddingBottom: `${paddingBottom}px`,
                        }}
                    >
                        <div>
                            {imgUrl && (
                                <img
                                    src={imgUrl}
                                    alt="Logo"
                                    onClick={() => setIsImageSelected(true)}
                                    style={{
                                        height: '60px',
                                        width: '114px',
                                        objectFit: 'contain',
                                        cursor: 'pointer',
                                    }}
                                />
                            )}
                        </div>
                    </div>
                    <div style={{ display: 'flex', alignItems: 'center' }}>
                        <div style={{ display: 'flex', flexDirection: 'row', alignItems: 'center' }}>
                            <div style={{ display: 'flex', flexDirection: 'column', marginRight: '12px' }}>
                                <RichText
                                    tagName="p"
                                    value={text1}
                                    onChange={onChangeText1}
                                    style={{
                                        margin: 0,
                                        color,
                                        fontFamily,
                                        fontSize: `${fontSize}px`,
                                        lineHeight: `${lineHeight}px`,
                                        fontWeight,
                                        textTransform,
                                        textAlign,
                                    }}
                                />
                                <RichText
                                    tagName="p"
                                    value={text2}
                                    onChange={onChangeText2}
                                    style={{
                                        margin: 0,
                                        color,
                                        fontFamily,
                                        fontSize: `${fontSize}px`,
                                        lineHeight: `${lineHeight}px`,
                                        fontWeight,
                                        textTransform,
                                        textAlign,
                                    }}
                                />
                            </div>

                            {/* Line separator on the right side */}
                            <div style={{
                                width: '2px',
                                height: '32px',
                                backgroundColor: '#164BDC',
                            }}></div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}