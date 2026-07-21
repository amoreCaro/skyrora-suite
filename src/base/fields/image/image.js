import {  
    MediaUpload,
    MediaUploadCheck 
} from '@wordpress/block-editor';

import { Button } from "@wordpress/components";

export const Image = ({ imageUrl, index, setAttributes }) => {

    const onSelectImage = (media) => {
        setAttributes({ 
            [index]: media.url
        });
    };

    const onRemoveImage = () => {
        setAttributes({
            [index]: ''
        });
    };


    return (
        <>
            <MediaUploadCheck>
                <MediaUpload
                    onSelect={onSelectImage}
                    allowedTypes={['image']}
                    render={({ open }) => (
                        <div className="wp-image--upload">
                            {imageUrl ? (
                                <div className="wp-group__image">
                                    <img height="720px" width="320px" src={imageUrl} alt="Selected image" />
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
        </>
    );
};