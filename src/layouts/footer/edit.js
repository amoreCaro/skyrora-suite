import { __ } from '@wordpress/i18n';
import {
    useBlockProps,
    InspectorControls,
    RichText,
    PanelColorSettings,
    MediaUpload
} from '@wordpress/block-editor';

import {
    PanelBody,
    TextControl,
    Button,
    TabPanel,
    SelectControl,
    Icon
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { plus } from '@wordpress/icons';
import { DragDropContext, Droppable, Draggable } from 'react-beautiful-dnd';

export default function Edit({ attributes, setAttributes }) {
    const {
        imageItems = [],
        imgUrl,
        imgId,
        imgLink,
        footerLinks: attrFooterLinks,
        copyright,
        copyrightUrl,
        paddingLeft,
        paddingRight,
        tabSelected,
        fontFamily,
        fontSize,
        lineHeight,
        fontWeight,
        textTransform,
        textAlign,
        textColor,
        backgroundColor,
    } = attributes;
    const blockProps = useBlockProps();

    // ─── SOCIALS ──────────────────────────────────────────────
    const toggleSocialImageCollapse = (index) => {
        const updatedItems = imageItems.map((item, i) => ({
            ...item,
            isOpen: i === index ? !item.isOpen : false,
        }));
        setAttributes({ imageItems: updatedItems });
    };

    const addFooterSocial = () => {
        const newItem = { imgUrl: '', imgId: null, imgLink: '', isOpen: true };
        setAttributes({ imageItems: [...imageItems, newItem] });
    };

    const updateItem = (index, updatedData) => {
        const updatedItems = [...imageItems];
        updatedItems[index] = { ...updatedItems[index], ...updatedData };
        setAttributes({ imageItems: updatedItems });
    };

    const deleteImage = (index) => {
        const updatedItems = imageItems.filter((item, i) => i !== index);
        setAttributes({ imageItems: updatedItems });
    };

    // ─── LINKS ────────────────────────────────────────────────
    const [footerLinks, setFooterLinks] = useState(attrFooterLinks || []);

    useEffect(() => {
        setAttributes({ footerLinks });
    }, [footerLinks]);

    const toggleCollapse = (index) => {
        const updatedLinks = [...footerLinks];
        updatedLinks[index].isCollapsed = !updatedLinks[index].isCollapsed;
        setFooterLinks(updatedLinks);
    };

    const addFooterLink = () => {
        const newLink = { text: '', url: '', isCollapsed: false };
        setFooterLinks([...footerLinks, newLink]);
    };

    const updateFooterLink = (value, index, field) => {
        const updatedLinks = [...footerLinks];
        updatedLinks[index][field] = value;
        setFooterLinks(updatedLinks);
    };
    const deleteFooterLink = (indexToDelete) => {
        setFooterLinks(footerLinks.filter((_, i) => i !== indexToDelete));
    };

    // ─── COPYRIGHT ─────────────────────────────────────────────
    useEffect(() => {
        if (!copyright) {
            setAttributes({ copyright: '© 2025 SKYRORA LIMITED' });
        }
        if (!copyrightUrl) {
            setAttributes({ copyrightUrl: '#' });
        }
    }, []);

    // ─── DRAG AND DROP ──────────────────────────────────────────
    const onDragEnd = (result) => {
        if (!result.destination) return;
        const updatedItems = Array.from(imageItems);
        const [movedItem] = updatedItems.splice(result.source.index, 1);
        updatedItems.splice(result.destination.index, 0, movedItem);
        setAttributes({ imageItems: updatedItems });
    };

    return (
        <>
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
                            { name: 'content', title: <span className="dashicons dashicons-admin-generic" /> },
                            { name: 'styles', title: <span className="dashicons dashicons-admin-appearance" /> },
                        ]}
                    >
                        {(tab) => {
                            if (tab.name === 'content') {
                                return (
                                    <>
                                        <PanelBody title="Footer Socials" initialOpen={true}>
                                            <DragDropContext onDragEnd={onDragEnd}>
                                                <Droppable droppableId="footerLinks">
                                                    {(provided) => (
                                                        <div
                                                            {...provided.droppableProps}
                                                            ref={provided.innerRef}
                                                            style={{ marginBottom: '12px' }}
                                                        >
                                                            {imageItems.map((item, index) => (
                                                                <Draggable key={index} draggableId={`item-${index}`} index={index}>
                                                                    {(provided) => (
                                                                        <div
                                                                            ref={provided.innerRef}
                                                                            {...provided.draggableProps}
                                                                            {...provided.dragHandleProps}
                                                                            style={{
                                                                                ...provided.draggableProps.style,
                                                                                marginBottom: '12px',
                                                                                border: '1px solid #E1E1E1',
                                                                                background: '#F9F9F9',
                                                                                cursor: 'move',
                                                                            }}
                                                                        >
                                                                            <div
                                                                                className="collapse__head"
                                                                                onClick={() => toggleSocialImageCollapse(index)}
                                                                                style={{
                                                                                    cursor: 'pointer',
                                                                                    display: 'flex',
                                                                                    justifyContent: 'space-between',
                                                                                    alignItems: 'center',
                                                                                    gap: '12px',
                                                                                    padding: '8px',
                                                                                    background: '#F9F9F9',
                                                                                }}
                                                                            >
                                                                                <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                                                                                    {item.imgUrl ? (
                                                                                        <img
                                                                                            src={item.imgUrl}
                                                                                            alt="Footer Social"
                                                                                            style={{
                                                                                                height: '40px',
                                                                                                width: '40px',
                                                                                                objectFit: 'cover',
                                                                                            }}
                                                                                        />
                                                                                    ) : (
                                                                                        <MediaUpload
                                                                                            onSelect={(media) =>
                                                                                                updateItem(index, { imgUrl: media.url, imgId: media.id })
                                                                                            }
                                                                                            allowedTypes={['image']}
                                                                                            value={item.imgId}
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
                                                                                    <span>{item.imgUrl ? item.imgUrl.split('/').pop() : __('No Image')}</span>
                                                                                </div>
                                                                                <Button
                                                                                    onClick={(e) => {
                                                                                        e.stopPropagation();
                                                                                        deleteImage(index);
                                                                                    }}
                                                                                    isDestructive
                                                                                    style={{ padding: '0', fontSize: '20px' }}
                                                                                >
                                                                                    <span className="dashicons dashicons-no-alt" style={{ color: 'black' }} />
                                                                                </Button>
                                                                            </div>

                                                                            {item.isOpen && (
                                                                                <div
                                                                                    className="collapse__content"
                                                                                    style={{
                                                                                        padding: '8px',
                                                                                        border: '1px solid #E1E1E1',
                                                                                        borderTop: 'none',
                                                                                        background: '#fff',
                                                                                    }}
                                                                                >
                                                                                    {item.imgUrl ? (
                                                                                        <img
                                                                                            src={item.imgUrl}
                                                                                            alt="Full"
                                                                                            style={{
                                                                                                width: '100%',
                                                                                                objectFit: 'cover',
                                                                                                backgroundColor: '#F0F0F0',
                                                                                            }}
                                                                                        />
                                                                                    ) : (
                                                                                        <MediaUpload
                                                                                            onSelect={(media) =>
                                                                                                updateItem(index, { imgUrl: media.url, imgId: media.id })
                                                                                            }
                                                                                            allowedTypes={['image']}
                                                                                            value={item.imgId}
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
                                                                                                        border: '2px dashed #CCCCCC',
                                                                                                    }}
                                                                                                >
                                                                                                    +
                                                                                                </div>
                                                                                            )}
                                                                                        />
                                                                                    )}
                                                                                    <TextControl
                                                                                        label={__('Image Link')}
                                                                                        value={item.imgLink || '#'}
                                                                                        onChange={(value) => updateItem(index, { imgLink: value })}
                                                                                    />
                                                                                </div>
                                                                            )}
                                                                        </div>
                                                                    )}

                                                                </Draggable>
                                                            ))}
                                                            {provided.placeholder}
                                                        </div>
                                                    )}
                                                </Droppable>
                                            </DragDropContext>

                                            <Button
                                                onClick={addFooterSocial}
                                                icon={plus}
                                                isPrimary
                                                style={{
                                                    width: '100%',
                                                    display: 'flex',
                                                    justifyContent: 'center',
                                                    alignItems: 'center',
                                                    gap: '8px',
                                                    marginTop: '12px',
                                                }}
                                            >
                                                {__('Add Social Icon')}
                                            </Button>
                                        </PanelBody>
                                        <PanelBody title="Footer Links" initialOpen={true}>
                                            <DragDropContext onDragEnd={onDragEnd}>
                                                <Droppable droppableId="footerLinks">
                                                    {(provided) => (
                                                        <div {...provided.droppableProps} ref={provided.innerRef}>
                                                            {footerLinks.map((link, index) => (
                                                                <Draggable key={index} draggableId={`link-${index}`} index={index}>
                                                                    {(provided) => (
                                                                        <div
                                                                            ref={provided.innerRef}
                                                                            {...provided.draggableProps}
                                                                            {...provided.dragHandleProps}
                                                                            style={{
                                                                                marginBottom: '8px',
                                                                                ...provided.draggableProps.style,
                                                                            }}
                                                                        >
                                                                            <div
                                                                                className="collapse__head"
                                                                                onClick={() => toggleCollapse(index)}
                                                                                style={{
                                                                                    cursor: 'pointer',
                                                                                    display: 'flex',
                                                                                    justifyContent: 'space-between',
                                                                                    alignItems: 'center',
                                                                                    gap: '12px',
                                                                                    border: '1px solid #E1E1E1',
                                                                                    padding: '8px',
                                                                                    background: '#F9F9F9',
                                                                                }}
                                                                            >
                                                                                <span>{link.text || __('Footer Link')}</span>
                                                                                <span
                                                                                    onClick={(e) => {
                                                                                        e.stopPropagation();
                                                                                        deleteFooterLink(index);
                                                                                    }}
                                                                                    style={{
                                                                                        cursor: 'pointer',
                                                                                        color: '#888',
                                                                                        fontWeight: 'bold',
                                                                                        marginLeft: 'auto',
                                                                                    }}
                                                                                >
                                                                                    ×
                                                                                </span>
                                                                            </div>

                                                                            {link.isCollapsed && (
                                                                                <div
                                                                                    className="collapse__content"
                                                                                    style={{
                                                                                        padding: '8px',
                                                                                        border: '1px solid #E1E1E1',
                                                                                        background: '#fff',
                                                                                    }}
                                                                                >
                                                                                    <TextControl
                                                                                        label={__('Footer Link Text')}
                                                                                        value={link.text}
                                                                                        onChange={(value) =>
                                                                                            updateFooterLink(value, index, 'text')
                                                                                        }
                                                                                    />
                                                                                    <TextControl
                                                                                        label={__('Footer Link URL')}
                                                                                        value={link.url}
                                                                                        onChange={(value) =>
                                                                                            updateFooterLink(value, index, 'url')
                                                                                        }
                                                                                        placeholder="#"
                                                                                    />
                                                                                </div>
                                                                            )}
                                                                        </div>
                                                                    )}
                                                                </Draggable>
                                                            ))}
                                                            {provided.placeholder}
                                                        </div>
                                                    )}
                                                </Droppable>
                                            </DragDropContext>

                                            <Button
                                                onClick={addFooterLink}
                                                icon={plus}
                                                isPrimary
                                                style={{
                                                    width: '100%',
                                                    display: 'flex',
                                                    justifyContent: 'center',
                                                    alignItems: 'center',
                                                    gap: '8px',
                                                    marginTop: '12px',
                                                }}
                                            >
                                                {__('Add Footer Link')}
                                            </Button>
                                        </PanelBody>
                                        <PanelBody title={__('Footer Copyright')}>
                                            <TextControl
                                                label={__('Copyright')}
                                                value={copyright}
                                                onChange={(val) => setAttributes({ copyright: val })}
                                                placeholder="© 2025 SKYRORA LIMITED"
                                            />
                                            <TextControl
                                                label={__('Copyright URL')}
                                                value={copyrightUrl}
                                                onChange={(val) => setAttributes({ copyrightUrl: val })}
                                                placeholder="#"
                                            />
                                        </PanelBody>
                                    </>
                                );
                            }

                            if (tab.name === 'styles') {
                                return (
                                    <>
                                        <PanelColorSettings
                                            title={__('Color Settings')}
                                            initialOpen={true}
                                            colorSettings={[
                                                {
                                                    value: textColor || '#FFFFFF',
                                                    onChange: (value) => setAttributes({ textColor: value }),
                                                    label: __('Text Color'),
                                                },
                                                {
                                                    value: backgroundColor || '#181B24',
                                                    onChange: (value) => setAttributes({ backgroundColor: value }),
                                                    label: __('Background Color'),
                                                },
                                            ]}
                                        />
                                        <PanelBody title={__('Typography')} initialOpen={true}>
                                            <TextControl
                                                label={__('Font Size (px)')}
                                                type="number"
                                                value={fontSize}
                                                onChange={(value) => setAttributes({ fontSize: value })}
                                            />
                                            <TextControl
                                                label={__('Line Height (%)')}
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
                                            <TextControl
                                                label={__('Padding Left (px)')}
                                                type="number"
                                                value={paddingLeft}
                                                onChange={(value) => setAttributes({ paddingLeft: value })}
                                            />
                                            <TextControl
                                                label={__('Padding Right (px)')}
                                                type="number"
                                                value={paddingRight}
                                                onChange={(value) => setAttributes({ paddingRight: value })}
                                            />
                                        </PanelBody>
                                    </>
                                );
                            }
                        }}
                    </TabPanel>
                </div>
            </InspectorControls>
            <style>
                {`
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
        `}
            </style>

            <div
                {...blockProps}
                style={{
                    width: '100%',
                    boxSizing: 'border-box',
                    display: 'flex',
                    flexDirection: 'column',
                    justifyContent: 'center',
                    alignItems: 'center',
                    background: attributes.backgroundColor || '#181B24',
                    paddingTop: '30px',
                    paddingBottom: '48px',
                    paddingLeft: `${paddingLeft || 16}px`,
                    paddingRight: `${paddingRight || 16}px`,
                    color: attributes.textColor || '#FFFFFF',
                    fontFamily: fontFamily || 'Bai Jamjuree, sans-serif',
                    fontSize: `${fontSize || 12}px`,
                    lineHeight: `${lineHeight || 100}%`,
                    fontWeight: fontWeight || '400',
                    textTransform: textTransform || 'none',
                    textAlign: textAlign || 'center',
                }}
            >
                {/* Footer Socials  */}
                <div
                    style={{
                        display: 'flex',
                        flexWrap: 'wrap',
                        justifyContent: 'center',
                        gap: '20px',
                        width: '100%',
                        marginBottom: '16px',
                    }}
                >
                    {imageItems.map((item, index) => (
                        <a
                            key={index}
                            style={{
                                maxWidth: '48px',
                                width: '100%',
                                height: '48px',
                            }}
                            href={item.imgLink || '#'}
                        >
                            {item.imgUrl && (
                                <img
                                    src={item.imgUrl}
                                    alt={`Column Image ${index + 1}`}
                                    style={{
                                        width: '100%',
                                        height: '100%',
                                        objectFit: 'cover',
                                    }}
                                />
                            )}
                        </a>
                    ))}
                </div>

                {/* Footer Links  */}
                <div
                    style={{
                        marginBottom: '16px',
                        display: 'flex',
                        gap: '16px',
                        justifyContent: 'center',
                        flexWrap: 'wrap',
                    }}
                >
                    {footerLinks.map((link, index) => (
                        <div key={index}>
                            <a
                                href={link.url}
                                style={{
                                    color: '#FFFFFF',
                                    textDecoration: 'none',
                                }}
                            >
                                {link.text || 'New Link'}
                            </a>
                        </div>
                    ))}
                </div>
                {/* Footer Copyright */}
                <div style={{ textAlign: 'center' }}>
                    <a href={copyrightUrl || '#'} style={{ textDecoration: 'none' }}>
                        <span
                            style={{
                                fontWeight: 400,
                                fontSize: '12px',
                                lineHeight: '100%',
                                letterSpacing: '0px',
                                textTransform: 'uppercase',
                                color: '#B8BDCC',
                            }}
                        >
                            {copyright}
                        </span>
                    </a>
                </div>
            </div>
        </>
    );
}
