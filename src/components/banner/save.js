import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
    const blockProps = useBlockProps.save();
    const {
        bannerUrl,
        paddingLeft,
        paddingRight,
        paddingTop,
        paddingBottom
    } = attributes;

    const bannerWrapper = {
		paddingTop: `${paddingTop}px`,
		paddingBottom: `${paddingBottom}px`,
		paddingLeft: `${paddingLeft}px`,
		paddingRight: `${paddingRight}px`,
		width: "100%",
		height: "244px",
		margin: "0",
		background: '#fff'
	};

	const banner = {
		display: "block",
		height: "244px",
		width: "100%",
		objectFit: "cover",
	};

    return (
        <>
          
            
            <div {...blockProps} className="wp-banner-wrapper" style={bannerWrapper}>
                <img
                    src={bannerUrl}
                    alt=""
                    style={banner}
                />
            </div>
        </>
    );
}
