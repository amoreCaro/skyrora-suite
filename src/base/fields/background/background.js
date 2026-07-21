import { useState, useEffect } from 'react';
import {  
    MediaUpload,
    MediaUploadCheck 
} from '@wordpress/block-editor';
import { 
    Button,
    TabPanel,
    ColorPicker,
    GradientPicker
} from '@wordpress/components';

export const Background = ({ attributes, setAttributes }) => {

    const backgroundImageUrl = attributes.backgroundImage;
    const backgroundVideoUrl = attributes.backgroundVideo;
    const defaultColor = attributes.backgroundColor;
    const defaultTab = attributes.backgroundTab;
    const defaultGradient = attributes.backgroundGradient;
    
    const [colorCurrent, setColor] = useState(defaultColor);
    const [activeTab, setActiveTab] = useState(defaultTab);

    // ActiveTab
    useEffect(() => {
        if (defaultTab) {
            setActiveTab(defaultTab);
        } else {
            setActiveTab('background-image');
        }
    }, []);

    const onSelectTab = (tabName) => {
        setActiveTab(tabName);
        setAttributes({ backgroundTab: activeTab });
    };

    // Обробка зміни кольору
    const onSelectColor = (newColor) => {
        setColor(newColor);
        setAttributes({ backgroundColor: newColor });
    };

    const onSelectGradient = (newGradient) => {
        setAttributes({ backgroundGradient: newGradient });
    };

    // Обробка вибору зображення
    const onSelectImage = (media) => {
        setAttributes({ backgroundImage: media.url });
    };

    // Обробка видалення зображення
    const onRemoveImage = () => {
        setAttributes({
            backgroundImage: ''
        });
    };

    // Обробка вибору відео
    const onSelectVideo = (media) => {
        setAttributes({ backgroundVideo: media.url }); 
    };

    // Обробка видалення відео
    const onRemoveVideo = () => {
        setAttributes({ backgroundVideo: '' });
    };

    return (
        <div className="background">
            <div className="background__group-heading">
                <h2>Background Type</h2>
            </div>
            <div className="background__group-tabs">
                <TabPanel
                    activeClass="tab__active"
                    onSelect={onSelectTab}
                    initialTabName={activeTab}
                    tabs={[
                        {
                            name: 'background-image',
                            title: 'Image',
                            className: 'tab-item tab-background-image'
                        },
                        {
                            name: 'background-color',
                            title: 'Color',
                            className: 'tab-item tab-background-color'
                        },
                        {
                            name: 'background-gradient',
                            title: 'Gradient',
                            className: 'tab-item tab-background-gradient'
                        },
                        {
                            name: 'background-video',
                            title: 'Video',
                            className: 'tab-item tab-background-video'
                        }
                    ]}
                >
                    {(tab) => (
                        <div className="tab__body">

                            {/* background-image */}
                            {tab.name === 'background-image' && (
                                <div className="tab__content tab__content--image">
                                    <MediaUploadCheck>
                                        <MediaUpload
                                            onSelect={onSelectImage}
                                            allowedTypes={['image']}
                                            render={({ open }) => (
                                                <div className="wp-image--upload">
                                                    {backgroundImageUrl ? (
                                                        <div className="wp-group__image" onClick={open}>
                                                            <img height="350px" width="350px" src={backgroundImageUrl} alt="Selected image" />
                                                            <Button onClick={onRemoveImage} className="btn btn--remove-image">
                                                                Remove Image
                                                            </Button>
                                                        </div>
                                                    ) : (
                                                        <Button className="btn btn--select-image" onClick={open} isPrimary>
                                                            Choose Image
                                                        </Button>
                                                    )}
                                                </div>
                                            )}
                                        />
                                    </MediaUploadCheck>
                                </div>
                            )}

                            {/* background-color */}
                            {tab.name === 'background-color' && (
                                <div className="tab__content tab__content--color">
                                    <ColorPicker
                                        color={colorCurrent}
                                        onChange={onSelectColor}
                                        enableAlpha={true}
                                    />
                                    <div className="current-color" style={{ background: colorCurrent || 'red' }}></div>
                                </div>
                            )}

                            {/* background-gradient */}
                            {tab.name === 'background-gradient' && (
                                <div className="tab__content tab__content--gradient">
                                    <GradientPicker
                                        value={ defaultGradient }
                                        gradients={[]}
                                        onChange={ onSelectGradient }            
                                    />
                                </div>
                            )}

                            {/* background-video */}
                            {tab.name === 'background-video' && (
                                <div className="tab__content tab__content--video">
                                    <MediaUpload
                                        onSelect={onSelectVideo}
                                        allowedTypes={['video']}
                                        render={({ open }) => (
                                            <div className="wp-image--upload">
                                                {backgroundVideoUrl ? (
                                                    <div className="wp-group__image">
                                                        <video src={backgroundVideoUrl} className="h-video" preload="metadata" onClick={open} controls />
                                                        <Button onClick={onRemoveVideo} className="btn btn--remove-image">
                                                            Remove Image
                                                        </Button>
                                                    </div>
                                                ) : (
                                                    <Button className="btn btn--select-image" onClick={open} isPrimary>
                                                        Choose Video
                                                    </Button>
                                                )}
                                            </div>
                                        )}
                                    />
                                </div>
                            )}

                        </div>
                    )}
                </TabPanel>
            </div>
        </div>
    );
};
