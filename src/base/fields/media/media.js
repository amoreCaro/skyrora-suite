import { useState, useEffect } from 'react';
import {  
    MediaUpload,
    MediaUploadCheck 
} from '@wordpress/block-editor';
import { 
    Button,
    TabPanel,
} from '@wordpress/components';

export const Media = ({ attributes, setAttributes }) => {

    const imageUrl = attributes.imageUrl;
    const videoUrl = attributes.videoUrl;
    const defaultMediaTab = attributes.mediaTab;
    
    const [activeMediaTab, setActiveMediaTab] = useState(defaultMediaTab);

    // ActiveTab
    useEffect(() => {
        if (defaultMediaTab) {
            setActiveMediaTab(defaultMediaTab);
        } else {
            setActiveMediaTab('media-image');
        }
    }, []);

    const onSelectMediaTab = (tabName) => {
        setActiveMediaTab(tabName);
        setAttributes({ mediaTab: activeMediaTab });
    };

  
    // Обробка вибору зображення
    const onSelectImage = (media) => {
        setAttributes({ imageUrl: media.url });
    };

    // Обробка видалення зображення
    const onRemoveImage = () => {
        setAttributes({
            imageUrl: ''
        });
    };

    // Обробка вибору відео
    const onSelectVideo = (media) => {
        setAttributes({ videoUrl: media.url }); 
    };

    // Обробка видалення відео
    const onRemoveVideo = () => {
        setAttributes({ videoUrl: '' });
    };

    return (
        <div className="background background--media">
            <div className="background__group-tabs">
                <TabPanel
                    activeClass="tab__active"
                    onSelect={onSelectMediaTab}
                    initialTabName={activeMediaTab}
                    tabs={[
                        {
                            name: 'media-image',
                            title: 'Image',
                            className: 'tab-item tab-background-image'
                        },
                        {
                            name: 'media-video',
                            title: 'Video',
                            className: 'tab-item tab-background-video'
                        }
                    ]}
                >
                    {(tab) => (
                        <div className="tab__body">

                            {/* background-image */}
                            {tab.name === 'media-image' && (
                                <div className="tab__content tab__content--image">
                                    <MediaUploadCheck>
                                        <MediaUpload
                                            onSelect={onSelectImage}
                                            allowedTypes={['image']}
                                            render={({ open }) => (
                                                <div className="wp-image--upload">
                                                    {imageUrl ? (
                                                        <div className="wp-group__image" onClick={open}>
                                                            <img height="350px" width="350px" src={imageUrl} alt="Selected image" />
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

                            {/* background-video */}
                            {tab.name === 'media-video' && (
                                <div className="tab__content tab__content--video">
                                    <MediaUpload
                                        onSelect={onSelectVideo}
                                        allowedTypes={['video']}
                                        render={({ open }) => (
                                            <div className="wp-image--upload">
                                                {videoUrl ? (
                                                    <div className="wp-group__image">
                                                        <video src={videoUrl} className="h-video" preload="metadata" onClick={open} controls />
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
